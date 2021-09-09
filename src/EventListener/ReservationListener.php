<?php

namespace App\EventListener;

use App\Entity\Reservation;
use App\Factory\RedisConnectionFactory;
use App\Service\NotificationService;
use Carbon\Carbon;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

class ReservationListener implements EventSubscriber
{
    private MailerInterface $mailer;
    private \Redis $redis;
    private NotificationService $notificationService;

    public function __construct(MailerInterface $mailer, NotificationService $notificationService)
    {
        $this->mailer = $mailer;
        $this->redis = RedisConnectionFactory::create();
        $this->notificationService = $notificationService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::prePersist,
            Events::postRemove
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Reservation) {
            return;
        }

        if (!$subject->getFirstName() || !$subject->getLastName()) {
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE, "Incomplete user registration. Please complete your registration details in the account page.");
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Reservation) {
            return;
        }

        if (!$subject->getEmail()) {
            return;
        }

        $checksum = md5($subject->getHashId() . $subject->getId() . $_ENV["APP_SECRET"]);
        $this->redis->set($checksum, $subject->getId());

        $locationName = $subject->getRoom()->getLocation()->getName();
        $reservationDate = Carbon::instance($subject->getDate())->toDateString();

        $clientHostname = $_ENV['CLIENT_HOSTNAME'];
        $cancellationLink = "{$clientHostname}/cancel-reservation/{$checksum}";

        if ($_ENV["APP_DEBUG"]) {
            return;
        }

        $html = $this->notificationService->renderMail("reservation-confirmation.twig", [
            "location" => $locationName,
            "date" => $reservationDate,
            "startTime" => $subject->getStartTime(),
            "endTime" => $subject->getEndTime(),
            "link" => $cancellationLink
        ]);

        try {
            $email = (new Email())
                ->from($_ENV["MAILER_FROM"])
                ->to($subject->getEmail())
                ->subject("Your Time Slot reservation @{$locationName} on {$reservationDate} from {$subject->getStartTime()} to {$subject->getEndTime()}")
                ->html($html);
            
        } catch (RfcComplianceException $exception) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid email provided");
        }

        try {
            $this->mailer->send($email);
        } catch (\Exception $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Failed to send reservation confirmation mail");
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Reservation) {
            return;
        }

        $checksum = md5($subject->getHashId() . $subject->getId() . $_ENV["APP_SECRET"]);
        $this->redis->del($checksum);
    }
}
