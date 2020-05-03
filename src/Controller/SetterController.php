<?php

namespace App\Controller;

use App\Components\Constants;
use App\Entity\User;
use App\Factory\ResponseFactory;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setter")
 */
class SetterController extends AbstractController
{
    private $entityManager;
    private $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
    }

    /**
     * @Route("")
     */
    public function setters()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, username from users where roles like :role';
        $query = $connection->prepare($statement);

        $query->execute([
            'role' => '%"' . addcslashes($this->contextService->getLocationRole(Constants::ROLE_SETTER), '%_') . '"%'
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }

    /**
     * @Route("/{id}/revoke", methods={"PUT"})
     */
    public function revoke(string $id)
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(
                ResponseFactory::createError("User ${id} not found", Response::HTTP_NOT_FOUND),
                Response::HTTP_NOT_FOUND
            );
        }

        $user->removeRole($this->contextService->getLocationRole(Constants::ROLE_SETTER));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_OK);
    }
}