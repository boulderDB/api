<?php

namespace App\Controller;

use App\Command\ProcessAccountDeletionsCommand;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Form\PasswordChangeType;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Form\UserType;
use App\Repository\LocationRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\StorageClient;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    private UserPasswordHasherInterface $passwordEncoder;
    private MailerInterface $mailer;
    private ContextService $contextService;
    private TokenStorageInterface $tokenStorage;
    private TokenExtractorInterface $tokenExtractor;
    private LocationRepository $locationRepository;
    private StorageClient $storageClient;
    private ParameterBagInterface $parameterBag;
    private NotificationRepository $notificationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordEncoder,
        MailerInterface $mailer,
        ContextService $contextService,
        TokenStorageInterface $tokenStorage,
        TokenExtractorInterface $tokenExtractor,
        LocationRepository $locationRepository,
        StorageClient $storageClient,
        ParameterBagInterface $parameterBag,
        NotificationRepository $notificationRepository

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
        $this->parameterBag = $parameterBag;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @Route("/notifications", methods={"GET"}, name="me_read_notifications")
     */
    public function notifications()
    {
        return $this->okResponse($this->notificationRepository->findAll());
    }

    /**
     * @Route("/me", methods={"GET"}, name="me_read")
     */
    public function readMe()
    {
        /**s
         * @var User $user
         */
        $user = $this->getUser();

        return $this->okResponse($user, ["self"]);
    }

    /**
     * @Route("/me", methods={"PUT"}, name="me_update")
     */
    public function updateMe(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $currentMail = $user->getEmail();

        $form = $this->createForm(UserType::class, $user);

        $form->add(...UserType::notificationsField());
        $form->submit(self::decodePayLoad($request), false);

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

        return $this->noContentResponse();
    }

    /**
     * @Route("/me", methods={"DELETE"}, name="me_delete")
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

        return $this->okResponse([
            "message" => "Your account was scheduled for deletion and will be removed on {$current->format('c')}",
            "time" => $current->format('c')
        ]);
    }

    /**
     * @Route("/me/password", methods={"PUT"}, name="change_password")
     */
    public function changePassword(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $form = $this->createForm(PasswordChangeType::class);
        $form->submit(json_decode($request->getContent(), true));
        $data = $form->getData();

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $isValid = $this->passwordEncoder->isPasswordValid($user, $data["oldPassword"]);

        if (!$isValid) {
            return $this->unauthorizedResponse("Invalid credentials");
        }

        $password = $this->passwordEncoder->hashPassword($user, $data["newPassword"]);
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->okResponse([
            "message" => "Your password was updated"
        ]);
    }

    /**
     * @Route("/password-reset", methods={"POST"}, name="password_reset_request")
     */
    public function requestReset(Request $request)
    {
        self::rateLimit($request, 'reset', 50);

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
        $user = $this->userRepository->loadUserByUsername($email);

        if (!$user) {
            return $this->resourceNotFoundResponse("User", $email);
        }

        $clientHostname = $_ENV['CLIENT_HOSTNAME'];
        $storageKey = "pending_password_reset_{$user->getId()}";
        $hash = hash('sha256', $storageKey);

        $this->redis->set($hash, $user->getId(), self::PASSWORD_RESET_EXPIRY);

        $html = $this->notificationService->renderMail("password-reset-request.twig", [
            "link" => "$clientHostname/password-reset/$hash"
        ]);

        $email = (new Email())
            ->from($_ENV["MAILER_FROM"])
            ->to($user->getEmail())
            ->subject('BoulderDB Password reset')
            ->html($html);

        $this->mailer->send($email);

        return $this->noContentResponse();
    }

    /**
     * @Route("/password-reset/{hash}", methods={"GET"}, name="password_reset_check_hash")
     */
    public function checkReset(string $hash)
    {
        if (!$this->redis->exists($hash)) {
            return $this->resourceNotFoundResponse("Hash", $hash);
        }

        return $this->noContentResponse();
    }

    /**
     * @Route("/password-reset/{hash}", methods={"POST"}, name="password_reset")
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

        $password = $this->passwordEncoder->hashPassword($user, $form->getData()['password']);
        $user->setPassword($password);
        $this->redis->del($hash);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/register", methods={"POST"}, name="register")
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

        $password = $this->passwordEncoder->hashPassword($user, $user->getPlainPassword());

        $user->setPassword($password);
        $user->setVisible(true);

        $this->entityManager->persist($user);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return $this->badRequestResponse("Username or email already taken");
        }

        return $this->createdResponse($user);
    }

    /**
     * @Route("/locations", methods={"GET"}, name="locations_index")
     */
    public function locations()
    {
        $locations = $this->locationRepository->getPublic();

        return $this->okResponse($locations);
    }

    /**
     * @Route("/locations/{id}", methods={"GET"}, name="locations_read")
     */
    public function location(string $id)
    {
        $location = $this->locationRepository->getPublicById($id);

        if (!$location) {
            return $this->resourceNotFoundResponse("Location", $id);
        }

        return $this->okResponse($location, ["default", "detail"]);
    }

    /**
     * @Route("/{location}/ping", methods={"GET"}, name="ping")
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

        if ($user->getLastVisitedLocation()?->getId() !== $location?->getId()) {
            $user->setLastVisitedLocation($location);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $this->noContentResponse();
    }

    /**
     * @Route("/upload", methods={"POST"}, name="upload")
     */
    public function upload(Request $request)
    {
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
