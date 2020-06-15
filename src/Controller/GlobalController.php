<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Serializer\LocationSerializer;
use App\Serializer\UserSerializer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GlobalController extends AbstractController
{
    const ACCOUNT_DELETION_TIMEOUT = '+1 day';

    use ApiControllerTrait;

    private $entityManager;
    private $redis;
    private $userRepository;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->entityManager = $entityManager;
        $this->redis = RedisConnectionFactory::create();
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
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
    public function register(Request $request)
    {
        self::rateLimit($request, 'register');

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->add(...UserType::usernameField());
        $form->add(...UserType::passWordField());

        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted()) {
            // check bot traps and return fake id response if filled
            if ($form->getExtraData()['firstName'] || $form->getExtraData()['lastName']) {
                return $this->created(42);
            }

            if ($this->userRepository->userExists('email', $form->getData()->getEmail())) {
                $form->get('email')->addError(
                    new FormError('This email is already taken')
                );
            }

            if ($this->userRepository->userExists('username', $form->getData()->getUsername())) {
                $form->get('username')->addError(
                    new FormError('This username is already taken')
                );
            }
        }

        if (!$form->isValid()) {
            return $this->badRequest($this->getFormErrors($form));
        }

        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->created($user->getId());
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

        return $this->json(
            array_map(function ($result) {
                return LocationSerializer::serializeArray($result);
            }, $results)
        );
    }
}