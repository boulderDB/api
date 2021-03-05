<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\AbstractQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    use RequestTrait;
    use ResponseTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function show(int $id)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $user = $builder
            ->from(User::class, "user")
            ->select("user.id, user.username")
            ->where("user.visible = true")
            ->andWhere("user.id = :id")
            ->setParameter("id", $id)
            ->getQuery()
            ->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $this->okResponse($user);
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $term = $request->query->get("username");

        if (!$term) {
            return $this->badRequestResponse("No term provided");
        }

        $builder = $this->entityManager->createQueryBuilder();

        $users = $builder
            ->from(User::class, "user")
            ->distinct()
            ->select("user.id, user.username")
            ->where("user.visible = true")
            ->andWhere($builder->expr()->like("lower(user.username)", ":term"))
            ->setParameter("term", "%" . addcslashes(strtolower($term), "%") . "%")
            ->orderBy("user.username")
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->okResponse($users);
    }
}
