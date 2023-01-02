<?php

namespace App\Controller;

use App\Entity\Course;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class CourseController extends AbstractController
{
    #[Route('/api/courses', name: 'getAllCourse', methods: ['GET'])]
    public function getAllCourse(CourseRepository $courseRepository, SerializerInterface $serializer): JsonResponse
    {
        $courseList = $courseRepository->findAll();
        $jsonCourseList = $serializer->serialize($courseList, 'json', ['groups' => 'get:course']);

        return new JsonResponse(
            $jsonCourseList, Response::HTTP_OK, [], true
            
        );
    }


    //**Avec paramConverter */

    #[Route('/api/courses/{id}', name: 'detailCourse', methods: ['GET'])]
    public function getDetailCourse(Course $course, SerializerInterface $serializer): JsonResponse
    {
        if(is_null($course)){
            return new JsonResponse($course, Response::HTTP_NOT_FOUND, ['message'=> "Cette page n'existe pas"]);
        }
        $jsonCourse = $serializer->serialize($course, 'json', ['groups' => 'get:detailCourse']);

        return new JsonResponse(
            $jsonCourse, Response::HTTP_OK, [], true
            
        );
    
    }

    #[Route('/api/courses/{id}', name: 'deleteCourse', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un cours')]
    public function deleteCourse(Course $course, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($course);
        $manager->flush();

        return new JsonResponse(
            null, Response::HTTP_NO_CONTENT
            
        );
    }

    #[Route('/api/courses', name: 'createCourse', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un cours')]
    public function createCourse(EntityManagerInterface $manager, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        try{
        /** @var Course $course */
        $course = $serializer->deserialize($request->getContent(), Course::class, 'json');

        $errors = $validator->validate($course);

        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($course);
        $manager->flush();

        $jsonCourse = $serializer->serialize($course, 'json', ['groups' => 'get:course']);

        $location = $urlGenerator->generate('detailCourse', ['id' => $course->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            $jsonCourse, Response::HTTP_CREATED, ["location" => $location], true
            
        );
    }catch(NotEncodableValueException $e){
        return new JsonResponse($serializer->serialize($e->getMessage(), 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    }
    }

    #[Route('/api/courses/{id}', name:"updateCourse", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un cours')]
    public function updateCourse(Request $request, SerializerInterface $serializer, Course $currentCourse, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $updatedCourse = $serializer->deserialize($request->getContent(), Course::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCourse]);

        $errors = $validator->validate($updatedCourse);

        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($updatedCourse);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    //*************** Sans paramConverter  */

    // #[Route('/api/courses/{id}', name: 'app_detail_course', methods: ['GET'])]
    // public function getDetailCourse(int $id, CourseRepository $courseRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $course = $courseRepository->find($id);

    //     if($course){
    //     $jsonCourse = $serializer->serialize($course, 'json');

    //     return new JsonResponse(
    //         $jsonCourse, Response::HTTP_OK, [], true
            
    //     );
    // }
    //     return new JsonResponse(
    //         null, Response::HTTP_NOT_FOUND
            
    //     );
    // }

}
