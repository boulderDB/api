<?php

namespace App\Command;

use App\Entity\Reservation;
use App\Helper\TimeHelper;
use App\Repository\ReservationRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AutoCheckoutReservationsCommand extends Command
{
    protected static $defaultName = 'boulderdb:reservation:auto-checkout';

    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->reservationRepository = $reservationRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Index current rankings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $current = Carbon::now()->modify($_ENV["SERVER_TIME_OFFSET"]);

        /**
         * @var Reservation[] $reservations
         */
        $reservations = $this->reservationRepository->createQueryBuilder("reservation")
            ->where("reservation.date = :date")
            ->setParameters([
                "date" => Carbon::now()->startOfDay()->format(TimeHelper::DATE_FORMAT_DATETIME),
            ])
            ->getQuery()
            ->getResult();

        $checkouts = 0;

        foreach ($reservations as $reservation) {
            $reservation->buildStartDate($current->format(TimeHelper::DATE_FORMAT_DATE));
            $reservation->buildEndDate($current->format(TimeHelper::DATE_FORMAT_DATE));

            // skip current
            if ($reservation->getEndDate() >= $current) {
                continue;
            }

            // skip future
            if ($reservation->getStartDate() >= $current) {
                continue;
            }

            // skip already checked out
            if ($reservation->getCheckedIn() !== true) {
                continue;
            }

            $io->writeln("Checking out reservation from {$reservation->getStartDate()} to {$reservation->getEndDate()}");
            $reservation->setCheckedIn(false);
            $checkouts++;

            $this->entityManager->persist($reservation);
        }

        $this->entityManager->flush();

        $io->success("Checked out ${checkouts} reservations");

        return 0;
    }
}
