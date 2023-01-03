<?php

namespace App\Controller;

use App\Entity\Slot;
use App\Repository\SlotRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SlotController extends AbstractController
{
    #[Route('/api/slots', name: 'getSlot', methods:['GET'])]
    public function getSlot(SlotRepository $slotRepository, SerializerInterface $serializer): JsonResponse
    {
        $slotList = $slotRepository->findAll();
        
        $jsonSlotList = $serializer->serialize($slotList, 'json', ['groups' => 'get:slots']);
        
        
        return new jsonResponse([
            json_decode($jsonSlotList), Response::HTTP_OK, [
                'Content-Type: application/json'
            ], true
        ]);
    }

    #[Route('/api/slots/{id}', name: 'getDetailSlot', methods:['GET'])]
    public function getDetailSlot(Slot $slot, SerializerInterface $serializer): JsonResponse
    {
        
        $jsonSlot = $serializer->serialize($slot, 'json', ['groups' => 'get:detailSlot']);


        return new JsonResponse([
            json_decode($jsonSlot), Response::HTTP_OK, [
                "Content-Type" => "application/json"
            ], true
        ]);
    }

    #[Route('/api/slots/{id}', name: 'deleteSlot', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un créneau')]
    public function deleteCourse(Slot $slot, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($slot);
        $manager->flush();

        return new JsonResponse(
            null, Response::HTTP_NO_CONTENT
            
        );
    }

    #[Route('/api/slots', name: 'createSlot', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter un créneau')]
    public function createSlot(EntityManagerInterface $manager, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, CourseRepository $courseRepository, ValidatorInterface $validator): JsonResponse
    {
        /** @var Slot $slot */
        $slot = $serializer->deserialize($request->getContent(), Slot::class, 'json', [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);
        
        /**
         * Add course to the slot by choosing it
         */
        $content = $request->toArray();
        $idCourse = $content['courseId'];
        $slot->setCourse($courseRepository->find($idCourse));

        $errors = $validator->validate($slot);

        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $manager->persist($slot);
        $manager->flush();

        $jsonSlot = $serializer->serialize($slot, 'json', [
            'groups' => 'detailSlot'
        ]);

        $location = $urlGenerator->generate('getDetailSlot', ['id' => $slot->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            $jsonSlot, Response::HTTP_CREATED, ["location" => $location], true
            
        );
    }

    #[Route('/api/slots/{id}', name:"updateSlot", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un créneau')]
    public function updateslot(Request $request, SerializerInterface $serializer, Slot $currentSlot, EntityManagerInterface $manager,ValidatorInterface $validator): JsonResponse
    {
        $updatedSlot = $serializer->deserialize($request->getContent(), Slot::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentSlot]);

        $errors = $validator->validate($updatedSlot);

        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($updatedSlot);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
