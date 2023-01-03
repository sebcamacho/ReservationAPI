<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Slot;
use App\Repository\ReservationRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use App\Service\DateTimeManagement;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{

    #[Route('/api/reservation/{id}', name: 'detailReservation', methods:['GET'])]
    public function getDetailReservation(Reservation $reservation, SerializerInterface $serializer): JsonResponse
    {
        
        $jsonSlot = $serializer->serialize($reservation, 'json', ['groups' => 'get:reservation']);


        return new JsonResponse([
            json_decode($jsonSlot), Response::HTTP_OK, [
                "Content-Type" => "application/json"
            ], true
        ]);
    }

    // #[Route('/api/reservation', name: 'getAllReservations', methods:['GET'])]
    // public function getAllReservations(ReservationRepository $reservationRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $reservation = $reservationRepository->findAll();
    //     $jsonSlot = $serializer->serialize($reservation, 'json', ['groups' => 'get:reservation']);


    //     return new JsonResponse([
    //         json_decode($jsonSlot), Response::HTTP_OK, [
    //             "Content-Type" => "application/json"
    //         ], true
    //     ]);
    // }

    #[Route('/api/reservation', name: 'getUserReservation', methods:['GET'])]
    public function getUserReservation(ReservationRepository $reservationRepository, SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {   
        /** @var User $user */
        $userId = $this->getUser();
        $user = $userRepository->find($userId);
       
        $reservations = $user->getReservations();

        $jsonSlot = $serializer->serialize($reservations, 'json', ['groups' => 'get:reservation']);

        return new JsonResponse([
            json_decode($jsonSlot), Response::HTTP_OK, [
                "Content-Type" => "application/json"
            ], true
        ]);
    }


    #[Route('/api/reservation', name: 'app_reservation', methods:'POST')]
    public function setReservation(SerializerInterface $serializer, Request $request, ValidatorInterface $validator, EntityManagerInterface $manager, UrlGeneratorInterface $urlGenerator, SlotRepository $slotRepository, ReservationRepository $reservationRepository): JsonResponse
    {
        $user = $this->getUser();
        $content = $request->toArray();
        $reservations = [];
        
        foreach ($content['slotId'] as $idSlot) {
            
            $slot = $slotRepository->find($idSlot);
            
            /** @var Reservation $reservation */
            $reservation = $serializer->deserialize($request->getContent(), Reservation::class, 'json');
            $countSlotReservation = $slot->getCountReservation();
            $limitCourseReservation = $slot->getCourse()->getUserMax();
            
            /**
             * Check if user_max limit is reached
             * else continue treatment
             */
            if($countSlotReservation >= $limitCourseReservation){
                return new JsonResponse($serializer->serialize(['message' => 'La limite maximale de participants pour ce cours est atteinte. Choisissez un autre créneau.'], 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }else{
                /**
                 * Check if this reservation already exist in database
                 * if not => send an error message
                 */
                $CheckDb = $reservationRepository->findOneBy(['user' => $user, 'slot' => $slot]);
                if(is_null($CheckDb)){
                    //Increment slot counter
                    $slot->setCountReservation(1);
                    //set reservation in database
                    $reservation->setUser($user)->setSlot($slot);

                    $errors = $validator->validate($reservation);
                    if($errors->count() > 0){
                        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
                    }

                    $reservations [] = $reservation;
                    $manager->persist($reservation);

                }else{
                    return new JsonResponse($serializer->serialize(['message' => 'Vous avez déjà réservé ce créneau.'], 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
                }
            }
        }
         
        $manager->flush();

        $jsonReservation = $serializer->serialize($reservations, 'json', ['groups' => 'get:reservation']);
            
        $location = $urlGenerator->generate('getUserReservation', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            $jsonReservation, Response::HTTP_CREATED, ["location" => $location], true);
    }

    


}
