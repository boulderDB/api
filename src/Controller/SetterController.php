<?php

namespace App\Controller;

use App\Entity\Setter;
use App\Form\SetterType;
use App\Repository\SetterRepository;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setter")
 */
class SetterController extends AbstractController
{
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
     * @Route("", methods={"GET"})
     */
    public function index(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $active = $request->query->get("active");

        $sql = SetterRepository::getIndexStatement(
            $this->contextService->getLocation()->getId(),
            $active === "true"
        );

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->execute();

        return $this->json($query->fetchAllAssociative());
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function current()
    {
        $sql = SetterRepository::getCurrentStatement($this->contextService->getLocation()->getId());

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->execute();

        return $this->json($query->fetchAllAssociative());
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $setter = new Setter();

        $form = $this->createForm(SetterType::class, $setter);
        $form->submit(self::decodePayLoad($request), false);

        if ($this->setterRepository->exists("username", $form->getData()->getUsername())) {
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
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $setter = $this->setterRepository->find($id);
        $currentUsername = $setter->getUsername();

        if (!$setter) {
            return $this->resourceNotFoundResponse("Setter", $id);
        }

        $form = $this->createForm(SetterType::class, $setter);
        $form->submit(self::decodePayLoad($request), false);

        if ($this->setterRepository->exists("username", $form->getData()->getUsername()) && $currentUsername !== $form->getData()->getUsername()) {
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
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        $setter = $this->setterRepository->find($id);

        if (!$setter) {
            return $this->resourceNotFoundResponse("Setter", $id);
        }

        $this->entityManager->remove($setter);

        try {
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $exception) {
            return $this->conflictResponse("This setter is referenced and cannot be deleted.");
        }

        return $this->noContentResponse();
    }
}
