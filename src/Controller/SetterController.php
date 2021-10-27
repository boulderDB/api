<?php

namespace App\Controller;

use App\Entity\Setter;
use App\Form\SetterType;
use App\Repository\SetterRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setters")
 */
class SetterController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use ResponseTrait;
    use RequestTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private SetterRepository $setterRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        SetterRepository $setterRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->setterRepository = $setterRepository;
    }

    /**
     * @Route(methods={"GET"}, name="setters_index")
     */
    public function index(Request $request)
    {
        $locationId = $this->contextService->getLocation()?->getId();
        $filters = $request->get("filter");

        if ($filters) {
            return $this->okResponse($this->setterRepository->queryWhere(
                $this->getLocationId(),
                ["active" => "bool"],
                $filters
            ));
        }

        return $this->okResponse($this->setterRepository->getCurrent($locationId));
    }

    /**
     * @Route(methods={"POST"}, name="setters_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();
        $locationId = $this->contextService->getLocation()?->getId();

        $setter = new Setter();

        $form = $this->createForm(SetterType::class, $setter);
        $form->submit(self::decodePayLoad($request), false);

        if ($this->setterRepository->exists("username", $form->getData()->getUsername(), $locationId)) {
            $form->get("username")->addError(
                new FormError('This username is already taken')
            );
        }

        if (!$form->get("username")) {
            $setter->setUsername($form->get("user")->getUsername());
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $setter->addLocation($this->contextService->getLocation());

        $this->entityManager->persist($setter);
        $this->entityManager->flush();

        return $this->createdResponse($setter);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="setters_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $locationId = $this->contextService->getLocation()?->getId();

        $setter = $this->setterRepository->find($id);

        if (!$setter) {
            return $this->resourceNotFoundResponse(Setter::RESOURCE_NAME, $id);
        }

        $currentUsername = $setter->getUsername();

        $form = $this->createForm(SetterType::class, $setter);
        $form->submit(self::decodePayLoad($request), false);

        if ($this->setterRepository->exists("username", $form->getData()->getUsername(), $locationId) && $currentUsername !== $form->getData()->getUsername()) {
            $form->get("username")->addError(
                new FormError('This username is already taken')
            );
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($setter);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="setters_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(Setter::class, $id, true);
    }
}
