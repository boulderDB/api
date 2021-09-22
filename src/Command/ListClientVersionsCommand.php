<?php

namespace App\Command;

use App\Factory\RedisConnectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ListClientVersionsCommand extends Command
{
    protected static $defaultName = 'boulderdb:list-client-version';

    private \Redis $redis;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ParameterBagInterface $parameterBag,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->redis = RedisConnectionFactory::create();
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this->setDescription("Track new client versions to redis store");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $versions = $this->redis->lRange("client-versions", 0, -1);
        $versions = array_filter(array_unique($versions));

        $io->table(["version"], array_map(function ($version) {
            return [$version];
        }, $versions));

        return 0;
    }
}