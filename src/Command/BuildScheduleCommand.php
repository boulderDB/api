<?php

namespace App\Command;

use App\Entity\Room;
use App\Entity\TimeSlot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildScheduleCommand extends Command
{
    protected static $defaultName = "blocbeta:schedule:build";

    protected static array $schedule = [
        "monday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "tuesday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "wednesday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "thursday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "friday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "saturday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
        "sunday" => [
            [
                "start" => "15:00",
                "end" => "18:00",
                "capacity" => 10,
            ],
            [
                "start" => "15:00",
                "end" => "17:00",
                "capacity" => 10
            ],
            [
                "start" => "17:00",
                "end" => "19:00",
                "capacity" => 10
            ],
            [
                "start" => "18:00",
                "end" => "20:00",
                "capacity" => 10
            ],
            [
                "start" => "19:00",
                "end" => "21:00",
                "capacity" => 10
            ],
            [
                "start" => "20:00",
                "end" => "22:00",
                "capacity" => 10
            ],
            [
                "start" => "21:00",
                "end" => "24:00",
                "capacity" => 10
            ],
            [
                "start" => "22:00",
                "end" => "24:00",
                "capacity" => 10
            ],
        ],
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription("Build default schedule.")
            ->addArgument("locationId", InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roomRepository = $this->entityManager->getRepository(Room::class);
        $room = $roomRepository->findOneBy(["name" => "default", "location" => $input->getArgument("locationId")]);

        foreach (self::$schedule as $day => $defaultSlots) {

            foreach ($defaultSlots as $defaultSlot) {

                $timeSlot = new TimeSlot();

                $timeSlot->setDayName($day);
                $timeSlot->setStartTime($defaultSlot["start"]);
                $timeSlot->setEndTime($defaultSlot["end"]);
                $timeSlot->setCapacity($defaultSlot["capacity"]);
                $timeSlot->setRoom($room);

                $this->entityManager->persist($timeSlot);
            }
        }

        $this->entityManager->flush();

        $io->success("Built default schedule successfully");

        return 0;
    }
}