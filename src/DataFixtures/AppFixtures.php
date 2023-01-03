<?php

namespace App\DataFixtures;

use DateInterval;
use Faker\Factory;
use App\Entity\Slot;
use App\Entity\User;
use App\Entity\Course;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// use Faker\Provider\fr_FR\Address;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $userAdmin = new User();
        $userAdmin->setEmail('admin@admin.ad');
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, 'adminadmin'));
        $manager->persist($userAdmin);
        
        $user = new User();
        $user->setEmail('user@user.us');
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'useruser'));
        $manager->persist($user);

        for ($i=1; $i < 10; $i++) { 
        $CustomUser = new User();
        $CustomUser->setEmail("user".$i."@user.us");
        $CustomUser->setRoles(["ROLE_USER"]);
        $CustomUser->setPassword($this->userPasswordHasher->hashPassword($user, 'useruser'));
        $manager->persist($CustomUser);
        }

        $date = new DateTimeImmutable('2023-02-02 08:00:00');
        
        for ($i = 0; $i < 5; $i++){
            $dayInterval = new DateInterval('P'.$i.'D');
            
            $course = new Course;
            $course->setName('Course '. $i + 1);
            $course->setPrice(random_int(10, 25));
            $course->setDescription($faker->text(maxNbChars:300));
            $course->setLocation($faker->address());
            $course->setUserMax(random_int(5, 10));
            $dateCourse = $date->add($dayInterval);

            $manager->persist($course);
            for($j = 0; $j < 10; $j++){
                $dayInterval7 = new DateInterval('P'. $j * 7 .'D');
                $slotInterval = new DateInterval('PT1H');
                $slotTime = $dateCourse->add($dayInterval7);

                $slot = new Slot;
                $slot->setCourse($course);
                $slot->setStartedAt($slotTime);
                $slot->setFinishedAt($slotTime->add($slotInterval));
                $slot->setCourse($course);
               
                $manager->persist($slot);
            }

            
            
        }
        $manager->flush();
    }
}
