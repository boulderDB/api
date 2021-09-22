<?php

namespace App\Command;

use App\Factory\RedisConnectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PopulateClientVersionCommand extends Command
{
    protected static $defaultName = 'boulderdb:populate-client-version';

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

        try {
            $contents = file_get_contents($this->parameterBag->get('kernel.project_dir') . "/public/meta.json");
        } catch (\Exception $exception) {
            $io->error("meta.json file not found");

            return 1;
        }

        $meta = json_decode($contents, true);
        $version = $meta["version"];

        $versions = $this->redis->lRange("client-versions", 0, -1);
        $versions = array_filter(array_unique($versions));

        if (!in_array($version, $versions)) {
            $this->redis->lPush("client-versions", $version);
            $io->success("Client version $version populated");
        } else {
            $io->note("No new version populated");
        }

        return 0;
    }
}