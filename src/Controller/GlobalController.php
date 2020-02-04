<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ApiControllerTrait;
use App\Entity\User;
use App\Form\UserType;
use App\Serializer\UserSerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GlobalController extends AbstractController
{
    use ApiControllerTrait;

    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/me", methods={"get"})
     */
    public function getMe()
    {
        /**s
         * @var User $user
         */
        $user = $this->getUser();

        return $this->json(UserSerializer::serialize($user));
    }

    /**
     * @Route("/me", methods={"put"})
     */
    public function updateMe(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route("/showuser/{id}")
     */
    public function showUser(int $id)
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, username, gender from users where id = :id';
        $query = $connection->prepare($statement);

        $query->execute([
            'id' => $id
        ]);

        $user = $query->fetch();

        if (!$user) return $this->json("User {$id} not found", Response::HTTP_FOUND);

        return $this->json($user);
    }

    /**
     * @Route("/searchuser")
     */
    public function searchUsers(Request $request)
    {
        $this->denyAccessUnlessGranted(Constants::ROLE_ADMIN);

        if (!$request->query->has('term')) {
            return $this->json("Missing request parameter term", Response::HTTP_BAD_REQUEST);
        }

        $term = $request->query->get('term');

        $builder = $this->entityManager->createQueryBuilder();

        $users = $builder
            ->from(User::class, 'user')
            ->distinct()
            ->select('user.id, user.username, user.gender, user.roles')
            ->where('user.visible = true')
            ->andWhere($builder->expr()->like('lower(user.username)', ':term'))
            ->orWhere($builder->expr()->like('lower(user.email)', ':term'))
            ->setParameter('term', '%' . addcslashes(strtolower($term), '%') . '%')
            ->orderBy('user.username')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();

        $users = array_map(function ($user) {
            $user['roles'] = array_values($user['roles']);

            return $user;
        }, $users);

        return $this->json($users);
    }

    /**
     * @Route("/location")
     */
    public function location()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, name, url, public from tenant';
        $query = $connection->prepare($statement);

        $query->execute();
        $results = $query->fetchAll();

        return $this->json($results);
    }
}