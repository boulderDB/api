<?php

namespace App\EventListener;

use App\Entity\Reservation;
use App\Factory\RedisConnectionFactory;
use Carbon\Carbon;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            Events::postPersist,
            Events::prePersist
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Reservation) {
            return;
        }

        if (!$subject->getFirstName() || !$subject->getLastName()) {
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE, "Incomplete user registration.");
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

        $checksum = md5($subject->getHashId() . $subject->getId(). $_ENV["APP_SECRET"]);
        $this->redis->set($checksum, $subject->getId());

        $locationName = $subject->getRoom()->getLocation()->getName();
        $reservationDate = Carbon::instance($subject->getDate())->toDateString();

        $clientHostname = $_ENV['CLIENT_HOSTNAME'];
        $cancellationLink = "{$clientHostname}/cancel-reservation/{$checksum}";

        if ($_ENV["APP_DEBUG"]) {
            return;
        }

        $email = (new Email())
            ->from($_ENV["MAILER_FROM"])
            ->to($subject->getEmail())
            ->subject("Your Time Slot reservation @{$locationName} on {$reservationDate} from {$subject->getStartTime()} to {$subject->getEndTime()}")
            ->html("
                <h1>BlocBeta</h1>
                <p>
                    Your Time Slot reservation @$locationName on $reservationDate from {$subject->getStartTime()} to {$subject->getEndTime()}
                </p>

                <br/>
                <br/>
                
                <p>
                    If you cannot attend to your slot, please <a href='{$cancellationLink}'>cancel it!</a>
                </p>
            ");

        try {
            $this->mailer->send($email);
        } catch (\Exception $exception) {

        }
    }
}
