<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Entity\User;
use App\Form\UserRoleType;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    use RequestTrait;
    use ResponseTrait;
    use ContextualizedControllerTrait;
    use CrudTrait;

    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private ContextService $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->contextService = $contextService;
    }

    /**
     * @Route("/users", methods={"GET"}, name="location_users_index")
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        $locationId = $this->contextService->getLocation()?->getId();

        $admins = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::ADMIN, $locationId, true)
        );

        $setters = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::SETTER, $locationId, true)
        );

        $collection = new ArrayCollection(array_unique(array_merge($setters, $admins), SORT_REGULAR));

        return $this->okResponse(array_values($collection->toArray()), ["default", "admin"]);
    }

    /**
     * @Route("/users/{id}", requirements={"id": "\d+"}, methods={"GET"}, name="location_users_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(User::class, $id, ["default", "admin"]);
    }

    /**
     * @Route("/users/{id}", methods={"PUT"}, name="location_users_update")
     */
    public function updateUser(Request $request, int $id)
    {
        $this->denyUnlessLocationAdmin();
        $locationId = $this->contextService->getLocation()?->getId();

        /**
         * @var User $user
         */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->resourceNotFoundResponse("User", $id);
        }

        $form = $this->createForm(UserRoleType::class);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        /* filter roles that do not match current location to preserve them */
        $roles = array_values(array_unique(array_filter($user->getRoles(), function ($role) use ($locationId) {
            return !ContextService::isLocationRole($role, $locationId);
        })));

        $user->setRoles([
            ...$roles,
            ...$form->getData()["roles"]
        ]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}