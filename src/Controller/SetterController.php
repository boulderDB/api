<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ContextualizedControllerTrait;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Factory\ResponseFactory;
use App\Service\ContextService;
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
    public function setters(Request $request)
    {
        $connection = $this->entityManager->getConnection();
        $parameters = [
            'role' => '%"' . addcslashes($this->contextService->getLocationRole(Constants::ROLE_SETTER), '%_') . '"%'
        ];

        $statement = 'select id, username from users where roles like :role';

        if ($request->query->get('hasActiveSets')) {
//            $parameters[] = [];
//            $statement = 'select users.id, users.username, count(boulder.id) from users inner join boulder where roles like :role';
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

        $user->removeRole($this->contextService->getLocationRole(Constants::ROLE_SETTER));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_OK);
    }
}