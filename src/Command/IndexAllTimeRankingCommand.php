<?php

namespace App\Command;

use App\Components\Constants;
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

class IndexAllTimeRankingCommand extends Command
{
    protected static $defaultName = 'blocbeta:index-all-time-ranking';

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
        $this->setDescription('Index all time ranking');
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

            /**
             * @var User[] $users
             */
            $users = $this->userRepository->getActivePastHalfYear();

            $totalBoulders = $this->boulderRepository->createQueryBuilder('boulder')
                ->select('count(boulder.id)')
                ->where('boulder.location = :location')
                ->setParameter('location',$location->getId())
                ->getQuery()
                ->getOneOrNullResult()[1];

            $ranking = [];

            foreach ($users as $user) {

                $flashes = $user->getAscents()->filter(function ($ascent) use ($locationId) {

                    /**
                     * @var Ascent $ascent
                     */
                    return $ascent->getType() === Constants::ASCENT_FLASHED && $ascent->getLocation()->getId() === $locationId;
                })->count();

                $tops = $user->getAscents()->filter(function ($ascent) use ($locationId) {

                    /**
                     * @var Ascent $ascent
                     */
                    return $ascent->getType() === Constants::ASCENT_TOPPED && $ascent->getLocation()->getId() === $locationId;
                })->count();

                $total = $flashes + $tops;

                $percentage = round(($total / $totalBoulders) * 100);

                $ranking[$user->getId()] = [
                    'percentage' => $percentage,
                    'boulders' => $total,
                    'flashed' => $flashes,
                    'topped' => $tops,
                    'userId' => $user->getId(),
                    'userVisible' => $user->isVisible(),
                    'username' => $user->getUsername(),
                    'avatar' => $user->getMedia(),
                    'lastActivity' => $user->getLastActivity(),
                    'gender' => $user->getGender()
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
