<?php

namespace App\Controller;

use App\Components\Controller\ContextualizedControllerTrait;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Factory\ResponseFactory;
use BlocBeta\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setter")
 */
class SetterController extends AbstractController
{
    use ContextualizedControllerTrait;

    private $entityManager;
    private $contextService;
    private $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index(Request $request)
    {
        $connection = $this->entityManager->getConnection();
        $parameters = [
            'role' => '%"' . addcslashes($this->contextService->getLocationRole(User::ROLE_SETTER), '%_') . '"%'
        ];

        $statement = 'select id, username from users where roles like :role';

        if ($request->query->has('withActiveBoulders')) {
            $parameters['status'] = 'active';
            $parameters['locationId'] = 28;

            $statement = "SELECT users.id, users.username, count(boulder.id) AS boulders FROM users INNER JOIN boulder_setters ON users.id = boulder_setters.user_id INNER JOIN boulder ON boulder_setters.boulder_id = boulder.id WHERE boulder.status = :status AND boulder.tenant_id = :locationId AND roles like :role GROUP BY users.id";
        }

        $query = $connection->prepare($statement);
        $query->execute($parameters);

        $results = $query->fetchAll();

        return $this->json($results);
    }

    /**
     * @Route("/{userId}/invite", methods={"POST"})
     */
    public function invite(string $userId)
    {
        $this->denyUnlessLocationAdmin();

        $this->redis->set("setter_role_invite", hash('sha256', "setter_role_invite_$userId"), 3600);

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{userId}/revoke", methods={"PUT"})
     */
    public function revoke(string $userId)
    {
        $this->denyUnlessLocationAdmin();

        $userRepository = $this->entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $userRepository->find($userId);

        if (!$user) {
            return $this->json(
                ResponseFactory::createError("User ${userId} not found", Response::HTTP_NOT_FOUND),
                Response::HTTP_NOT_FOUND
            );
        }

        $user->removeRole($this->contextService->getLocationRole(User::ROLE_SETTER));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_OK);
    }

    /**
     * @Route("/admins", methods={"GET"})
     */
    public function admins()
    {
        $this->denyUnlessLocationAdmin();

        $connection = $this->entityManager->getConnection();
        $parameters = [
            'role' => '%"' . addcslashes($this->contextService->getLocationRole(User::ROLE_ADMIN), '%_') . '"%'
        ];

        $statement = 'select id, username from users where roles like :role';

        $query = $connection->prepare($statement);
        $query->execute($parameters);
        $users = $query->fetchAll();

        return $this->json($users);
    }
}