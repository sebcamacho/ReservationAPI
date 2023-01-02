<?php

namespace App\Service;

use Doctrine\ORM\Mapping as ORM;
use DateInterval;
use DateTimeImmutable;

class DateTimeManagement
{
   
    /**
     * Specify $intVal number of interval hour value and $currentDate to attribute change
     */
    public function hourInterval(string $intVal, DateTimeImmutable $currentDate): \DateTimeImmutable
    {
        $hourIntVal = new DateInterval('PT'. $intVal . 'H');

        return $currentDate->add($hourIntVal);
    }

    /**
     * Specify $intVal number of interval hour value and $currentDate to attribute change
     */
    public function negativeHourInterval(string $intVal, DateTimeImmutable $currentDate): \DateTimeImmutable
    {
        $hourIntVal = new DateInterval('PT'. $intVal . 'H');
        return $currentDate->sub($hourIntVal);
    }

    /**
     * Specify $intVal number of interval day value and $currentDate to attribute change
     */
    public function dayInterval(string $intVal, DateTimeImmutable $currentDate): \DateTimeImmutable
    {
        $dayIntVal = new DateInterval('P'.$intVal.'D');
        return $currentDate->add($dayIntVal);
    }

    /**
     * Specify number of negative interval day value and date to attribute change
     */
    public function negativeDayInterval(string $intVal, DateTimeImmutable $currentDate): \DateTimeImmutable
    {
        $dayIntVal = new DateInterval('P'.$intVal.'D');
        return $currentDate->sub($dayIntVal);
    }

   
}