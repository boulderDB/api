<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class NotificationService
{
    private LocationRepository $locationRepository;
    private ParameterBagInterface $parameterBag;
    private Environment $twig;

    public function __construct(LocationRepository $locationRepository, ParameterBagInterface $parameterBag)
    {
        $this->locationRepository = $locationRepository;
        $this->parameterBag = $parameterBag;

        $loader = new FilesystemLoader($this->parameterBag->get('kernel.project_dir') . '/mails');
        $this->twig = new Environment($loader);
    }

    public function getUserNotifications(User $user): Collection
    {
        /**
         * @var \App\Entity\Location[] $locations
         */
        $locations = $this->locationRepository->findAll();
        $notifications = new ArrayCollection();

        foreach ($locations as $location) {
            $locationAdminRole = ContextService::getLocationRoleName('ADMIN', $location->getId(), true);

            foreach (Notification::getDefaultTypes() as $type) {
                $notifications->add(self::createNotification($user, $location, $type));
            }

            // if is admin, add notifications
            if (in_array($locationAdminRole, $user->getRoles(), true)) {
                foreach (Notification::getAdminTypes() as $type) {
                    $notifications->add(self::createNotification($user, $location, $type));
                }
            }
        }

        return $notifications;
    }

    public function renderMail(string $template, array $variables): string
    {
        return $this->twig->render($template, $variables);
    }

    private static function createNotification(User $user, Location $location, string $type): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setLocation($location);
        $notification->setType($type);

        return $notification;
    }
}