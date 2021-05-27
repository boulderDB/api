<?php

namespace App\Command;

use App\Factory\RedisConnectionFactory;
use App\Mails;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

class SendNotificationsCommand extends Command
{
    protected static $defaultName = "blocbeta:notifications:send";

    private \Redis $redis;
    private UserRepository $userRepository;
    private MailerInterface $mailer;
    private NotificationService $notificationService;

    public function __construct(
        UserRepository $userRepository,
        MailerInterface $mailer,
        NotificationService $notificationService,
        string $name = null
    )
    {
        $this->redis = RedisConnectionFactory::create();
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->notificationService = $notificationService;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $keys = $this->redis->keys('notification:*');
        $progress = $io->createProgressBar(count($keys));

        foreach ($keys as $key) {
            $data = json_decode($this->redis->get($key), true);

            /**
             * @var \App\Entity\User $user
             */
            $user = $this->userRepository->find($data["user"]);

            if (!$user) {
                $io->error("User {$user->getId()} not found");
                continue;
            }

            $recipient = $user->getEmail();
            $type = $data["type"] ?? null;

            if ($_ENV["APP_DEBUG"] !== "prod") {
                $recipient = $_ENV["DEBUG_MAIL"];
            }

            if (!$type) {
                $io->error("No type given");
                continue;
            }

            $html = $this->notificationService->renderMail("$type-notification.twig", $data);

            try {
                $email = (new Email())
                    ->from($_ENV["MAILER_FROM"])
                    ->to($recipient)
                    ->subject("BoulderDB – New $type")
                    ->html($html);
            } catch (RfcComplianceException $exception) {
                $io->error("Invalid email provided {$user->getEmail()}");
                continue;
            }

            try {
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                $io->error("Failed to send message");
                continue;
            }

            $this->redis->del($key);
            $progress->advance();
        }

        $progress->finish();

        return 0;
    }
}