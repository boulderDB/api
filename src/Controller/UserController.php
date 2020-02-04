<?php

namespace App\Controller;

use App\Components\Constants;
use App\Factory\RedisConnectionFactory;
use App\Service\ContextService;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $entityManager;
    private $contextService;
    private $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        Swift_Mailer $mailer
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/setters")
     */
    public function setters()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, username from users where roles like :role';
        $query = $connection->prepare($statement);

//        // todo: enable for v2
//        $query->execute([
//            'role' => '%"' . $this->contextService->getLocationRole(Constants::ROLE_SETTER) . '"%'
//        ]);

        $query->execute([
            'role' => "%" . addcslashes(Constants::ROLE_SETTER, '%_') . "%"
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }

    /**
     * @Route("/admins")
     */
    public function admins()
    {
        $this->denyAccessUnlessGranted(Constants::ROLE_ADMIN);

        $connection = $this->entityManager->getConnection();
        $statement = 'select id, username from users where roles like :role';
        $query = $connection->prepare($statement);

        $query->execute([
            'role' => '%"' . $this->contextService->getLocationRole(Constants::ROLE_ADMIN) . '"%'
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }

    /**
     * @Route("/invite", methods={"POST", "OPTIONS"})
     */
    public function sendRoleInvite(Request $request)
    {
        $this->denyAccessUnlessGranted(Constants::ROLE_ADMIN);

        $redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/invite/accept", methods={"POST", "OPTIONS"})
     */
    public function acceptRoleInvite()
    {
        $this->denyAccessUnlessGranted(Constants::ROLE_ADMIN);
    }
}