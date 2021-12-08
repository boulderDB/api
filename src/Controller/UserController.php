<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    use CrudTrait;

    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"GET"})
     */
    public function read(int $id)
    {
        return $this->readEntity(User::class, $id);
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(Request $request)
    {
        $username = $request->query->get("username");

        if (!$username) {
            return $this->badRequestResponse("No username provided");
        }

        return $this->okResponse($this->userRepository->searchByUsername($username));
    }
}
