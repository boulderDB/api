<?php

namespace App\Controller;

use App\Entity\User;
use App\Factory\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function show(string $id)
    {
        $connection = $this->entityManager->getConnection();
        $parameters = [
            'id' => $id,
            'visible' => true
        ];

        $statement = 'select id, username from users where id = :id and visible = :visible';

        $query = $connection->prepare($statement);
        $query->execute($parameters);
        $result = $query->fetch();

        if (!$result) {
            return $this->json(
                ResponseFactory::createError("User '{$id}' not found", Response::HTTP_NOT_FOUND),
                Response::HTTP_NOT_FOUND
            );
        };

        return $this->json($result);
    }
}