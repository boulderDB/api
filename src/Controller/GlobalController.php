<?php

namespace App\Controller;

use App\Command\ProcessAccountDeletionsCommand;
use App\Entity\Location;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Form\UserType;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use App\Service\StorageClient;
use App\Service\ContextService;
use App\Service\Serializer;
use App\Service\SerializerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Namshi\JOSE\JWS;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GlobalController extends AbstractController
{
    const ACCOUNT_DELETION_TIMEOUT = '+1 day';
    const PASSWORD_RESET_EXPIRY = 60 * 60;

    use FormErrorTrait;
    use ResponseTrait;
    use RequestTrait;
    use RateLimiterTrait;

    private EntityManagerInterface $entityManager;
    private \Redis $redis;
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $passwordEncoder;
    private MailerInterface $mailer;
    private ContextService $contextService;
    private TokenStorageInterface $tokenStorage;
    private TokenExtractorInterface $tokenExtractor;
    private LocationRepository $locationRepository;
    private StorageClient $storageClient;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        MailerInterface $mailer,
        ContextService $contextService,
        TokenStorageInterface $tokenStorage,
        TokenExtractorInterface $tokenExtractor,
        LocationRepository $locationRepository,
        StorageClient $storageClient
    )
    {
        $this->entityManager = $entityManager;
        $this->redis = RedisConnectionFactory::create();
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->contextService = $contextService;
        $this->tokenStorage = $tokenStorage;
        $this->tokenExtractor = $tokenExtractor;
        $this->locationRepository = $locationRepository;
        $this->storageClient = $storageClient;
    }

    /**
     * @Route("/context", methods={"GET"})
     */
    public function context()
    {
        /**
         * @var JWTUserToken $token
         */
        $token = $this->tokenStorage->getToken();
        $user = $this->getUser();

        /**
         * @var Location $location
         */
        $location = $this->locationRepository->find($user->getLastVisitedLocation());

        $jws = JWS::load($token->getCredentials());

        return $this->okResponse([
            "expiration" => $jws->getPayload()['exp'],
            "target" => Request::createFromGlobals()->query->get("target"),
            "targetLocation" => $location->getUrl(),
            "location" => [
                "id" => $location->getId(),
                "name" => $location->getName(),
                "url" => $location->getUrl()
            ],
            "fullRegistration" => $user->getLastName() && $user->getLastName(),
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "roles" => $user->getRoles(),
                "visible" => $user->isVisible(),
            ],
        ]);
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

        return $this->okResponse(Serializer::serialize($user, [SerializerInterface::GROUP_DETAIL]));
    }

    /**
     * @Route("/me", methods={"PUT"})
     */
    public function updateMe(Request $request)
    {
        $user = $this->getUser();
        $currentMail = $user->getEmail();

        $form = $this->createForm(UserType::class, $user);

        $form->submit(json_decode($request->getContent(), true), false);

        if ($this->userRepository->userExists("email", $form->getData()->getEmail()) && $currentMail !== $form->getData()->getEmail()) {
            $form->get("email")->addError(
                new FormError('This email is already taken')
            );
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
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

        $this->redis->set(
            ProcessAccountDeletionsCommand::getAccountDeletionCacheKey($user->getId()),
            $current->getTimestamp()
        );

        return $this->json([
            "message" => "Your account was scheduled for deletion and will be removed on {$current->format('c')}",
            "time" => $current->format('c')
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/request-reset", methods={"POST"})
     */
    public function requestReset(Request $request)
    {
        self::rateLimit($request, 'reset', 10);

        $form = $this->createForm(PasswordResetRequestType::class);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $email = $form->getData()['email'];

        if (!$this->userRepository->userExists('email', $email)) {
            $form->get('email')->addError(
                new FormError("E-Mail '$email' not found")
            );
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        /**
         * @var User $user
         */
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            return $this->resourceNotFoundResponse("User", $email);
        }

        $clientHostname = $_ENV['CLIENT_HOSTNAME'];
        $storageKey = "pending_password_reset_{$user->getId()}";
        $hash = hash('sha256', $storageKey);

        $this->redis->set($hash, $user->getId(), self::PASSWORD_RESET_EXPIRY);

        $email = (new Email())
            ->from($_ENV["MAILER_FROM"])
            ->to($user->getEmail())
            ->subject('Password reset')
            ->html("<p>Please use the following <a href='$clientHostname/password-reset/$hash'>link</a> to reset your password.</p>");

        $this->mailer->send($email);

        return $this->noContentResponse();
    }

    /**
     * @Route("/reset/{hash}", methods={"GET"})
     */
    public function checkReset(string $hash)
    {
        if (!$this->redis->exists($hash)) {
            return $this->resourceNotFoundResponse("Hash", $hash);
        }

        return $this->noContentResponse();
    }

    /**
     * @Route("/reset/{hash}", methods={"POST"})
     */
    public function reset(Request $request, string $hash)
    {
        if (!$this->redis->exists($hash)) {
            return $this->resourceNotFoundResponse("Hash", $hash);
        }

        $userId = $this->redis->get($hash);

        /**
         * @var User $user
         */
        $user = $this->userRepository->find($userId);

        $form = $this->createForm(PasswordResetType::class);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $password = $this->passwordEncoder->encodePassword($user, $form->getData()['password']);
        $user->setPassword($password);
        $this->redis->del($hash);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->noContentResponse();
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
        $form->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);
        $form->add('firstName', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);
        $form->add('lastName', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $form->add(...UserType::passWordField());

        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        if ($form->isSubmitted()) {
            // check bot traps and return fake id response if filled
            if (isset($form->getExtraData()['phone']) || isset($form->getExtraData()['fax'])) {
                return $this->noContentResponse();
            }

            if ($this->userRepository->userExists('email', $form->getData()->getEmail())) {
                $form->get("email")->addError(
                    new FormError('This email is already taken')
                );
            }

            if ($this->userRepository->userExists('username', $form->getData()->getUsername())) {
                $form->get("username")->addError(
                    new FormError('This username is already taken')
                );
            }
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $this->entityManager->persist($user);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return $this->badRequestResponse("Username or email already taken");
        }

        return $this->createdResponse($user);
    }

    /**
     * @Route("/location", methods={"GET"})
     */
    public function locations()
    {
        $locations = $this->locationRepository->findAll();

        return $this->okResponse(Serializer::serialize($locations));
    }

    /**
     * @Route("/location/{id}", methods={"GET"})
     */
    public function location(string $id)
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            return $this->resourceNotFoundResponse("Location", $id);
        }

        return $this->okResponse(Serializer::serialize($location, [SerializerInterface::GROUP_DETAIL]));
    }

    /**
     * @Route("/telemetry", methods={"POST"})
     */
    public function telemetry(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            return $this->unauthorizedResponse();
        }

        $form = $this->createFormBuilder(null, ["csrf_protection" => false])
            ->add("version", TextType::class, ["constraints" => [new NotBlank()]])
            ->getForm();

        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $versions = $this->redis->lRange("client-versions", 0, -1);
        $currentVersion = $form->getData()["version"];

        $lastVersion = $this->redis->get("client-version:user={$user->getId()}");

        $data = [
            "updates" => []
        ];

        if ($lastVersion !== $currentVersion) {
            $updates = array_slice($versions, array_search($currentVersion, $versions));

            foreach ($updates as $version) {
                $data["updates"][] = [
                    "version" => $version,
                    "instructions" => "https://storage.boulderdb.de/boulderdb-internal/instructions/{$version}.md"
                ];
            }
        }

        $this->redis->set("client-version:user={$user->getId()}", $currentVersion);

        return $this->okResponse($data);
    }

    /**
     * @Route("/{location}/ping", methods={"GET"})
     */
    public function ping()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            return $this->unauthorizedResponse();
        }

        $location = $this->contextService->getLocation();

        if ($user->getLastVisitedLocation() !== $location->getId()) {
            $user->setLastVisitedLocation($location->getId());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $hash = hash('sha256', $this->tokenStorage->getToken()->getCredentials());
        $this->redis->select(RedisConnectionFactory::DB_TRACKING);
        $this->redis->incr("session={$hash}:user={$user->getId()}:location={$location->getId()}");

        return $this->noContentResponse();
    }

    /**
     * @Route("/cancel-reservation/{hash}", methods={"get"})
     */
    public function cancel(Request $request, string $hash)
    {
        self::rateLimit($request, "cancel-reservation", 10, 60);

        $id = $this->redis->get($hash);

        if (!$id) {
            return $this->resourceNotFoundResponse("Cancellation hash", $hash);
        }

        $statement = "DELETE FROM reservation WHERE id = :reservationId";
        $query = $this->entityManager->getConnection()->prepare($statement);

        $query->execute([
            "reservationId" => $id,
        ]);

        $this->redis->del($hash);

        return $this->noContentResponse();
    }

    /**
     * @Route("/upload", methods={"POST"})
     */
    public function upload(Request $request)
    {
        self::rateLimit($request, "upload", 10, 60);

        /**
         * @var UploadedFile $file
         */
        $file = $request->files->get("file");
        $resource = $this->storageClient->upload($file);

        return $this->okResponse([
            "file" => $resource
        ]);
    }
}
