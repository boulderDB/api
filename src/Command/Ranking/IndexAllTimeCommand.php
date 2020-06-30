<?php

namespace App\Command\Ranking;

use App\Entity\Ascent;
use App\Entity\Location;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexAllTimeCommand extends Command
{
    protected static $defaultName = 'blocbeta:ranking:index-all-time';

    private $locationRepository;
    private $boulderRepository;
    private $userRepository;
    private $redis;

    public function __construct(
        LocationRepository $locationRepository,
        BoulderRepository $boulderRepository,
        UserRepository $userRepository,
        string $name = null
    )
    {
        parent::__construct($name);

        $this->locationRepository = $locationRepository;
        $this->boulderRepository = $boulderRepository;
        $this->userRepository = $userRepository;

        $this->redis = RedisConnectionFactory::create();
    }

    protected function configure()
    {
        $this->setDescription('Index all time rankings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var Location[] $locations
         */
        $locations = $this->locationRepository->findAll();

        foreach ($locations as $location) {
            $locationId = $location->getId();

            $io->writeln("Processing location $locationId");

            $from = new \DateTime();
            $from->modify('-6 months');

            /**
             * @var User[] $users
             */
            $users = $this->userRepository->createQueryBuilder('user')
                ->where('user.visible = true')
                ->andWhere('user.lastActivity > :from')
                ->setParameter('from', $from)
                ->getQuery()
                ->getResult();

            $totalBoulders = $this->boulderRepository->createQueryBuilder('boulder')
                ->select('count(boulder.id)')
                ->where('boulder.location = :location')
                ->setParameter('location', $location->getId())
                ->getQuery()
                ->getOneOrNullResult()[1];

            $ranking = [];

            foreach ($users as $user) {

                $flashes = $user->getAscents()->filter(function ($ascent) use ($locationId) {

                    /**
                     * @var Ascent $ascent
                     */
                    return $ascent->getType() === Ascent::ASCENT_FLASH && $ascent->getLocation()->getId() === $locationId;
                })->count();

                $tops = $user->getAscents()->filter(function ($ascent) use ($locationId) {

                    /**
                     * @var Ascent $ascent
                     */
                    return $ascent->getType() === Ascent::ASCENT_TOP && $ascent->getLocation()->getId() === $locationId;
                })->count();

                $total = $flashes + $tops;

                $percentage = round(($total / $totalBoulders) * 100);

                $ranking[$user->getId()] = [
                    'percentage' => $percentage,
                    'boulders' => $total,
                    'flashes' => $flashes,
                    'tops' => $tops,
                    'user' => [
                        'id' => $user->getId(),
                        'gender' => $user->getGender(),
                        'lastActivity' => $user->getLastActivity()->format('c'),
                        'username' => $user->getUsername(),
                        'media' => $user->getMedia(),
                    ]
                ];
            }

            usort($ranking, function ($a, $b) {
                return $a['boulders'] < $b['boulders'];
            });

            $key = 1;

            foreach ($ranking as &$rank) {
                $rank['rank'] = $key;
                $key++;
            }

            $this->redis->set($location->getId() . '-all-time-ranking', json_encode($ranking));
        }

        $io->success('All time ranking indexed successfully');

        return 0;
    }
}
