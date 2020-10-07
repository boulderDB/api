<?php

namespace App\EventListener;

use App\Entity\Reservation;
use App\Factory\RedisConnectionFactory;
use Carbon\Carbon;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ReservationListener implements EventSubscriber
{
    private MailerInterface $mailer;
    private \Redis $redis;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->redis = RedisConnectionFactory::create();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Reservation) {
            return;
        }

        $checksum = md5($subject->getHashId() . $subject->getUser()->getId());
        $this->redis->set($checksum, $subject->getId());

        $locationName = $subject->getRoom()->getLocation()->getName();
        $reservationDate = Carbon::instance($subject->getDate())->toDayDateTimeString();

        $clientHostname = $_ENV['CLIENT_HOSTNAME'];
        $cancellationLink = "{$clientHostname}/reservation/cancel/{$checksum}";

        $email = (new Email())
            ->from($_ENV["MAILER_FROM"])
            ->to($subject->getUser()->getEmail())
            ->subject("Your Time Slot reservation @{$locationName} on {$reservationDate}")
            ->html("<a href='{$cancellationLink}'>Cancel</a>");

        $this->mailer->send($email);

    }
}