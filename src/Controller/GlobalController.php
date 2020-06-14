<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Form\UserType;
use App\Serializer\UserSerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GlobalController extends AbstractController
{
    const ACCOUNT_DELETION_TIMEOUT = '+1 day';

    use ApiControllerTrait;

    private $entityManager;
    private $redis;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/me", methods={"GET"})
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
     * @Route("/me", methods={"PUT"})
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
     * @Route("/me", methods={"DELETE"})
     */
    public function deleteMe()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $user->setActive(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $current = new \DateTime();
        $current->modify(self::ACCOUNT_DELETION_TIMEOUT);

        $this->redis->set("user_account_deletion_{$user->getId()}", $current->getTimestamp());

        return $this->json([
            "message" => "Your account was scheduled for deletion and will be removed on {$current->format('c')}",
            "time" => $current->format('c')
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register()
    {

    }

    /**
     * @Route("/location", methods={"GET"})
     */
    public function location()
    {
        $fields = [
            'id',
            'name',
            'url',
            'public',
            'city',
            'zip',
            'address_line_one',
            'address_line_two',
            'country_code',
            'image',
            'website',
            'facebook',
            'instagram',
            'twitter',
        ];

        $fields = implode(', ', $fields);

        $connection = $this->entityManager->getConnection();
        $statement = "select {$fields} from tenant where public = true";
        $query = $connection->prepare($statement);

        $query->execute();
        $results = $query->fetchAll();

        return $this->json($results);
    }
}