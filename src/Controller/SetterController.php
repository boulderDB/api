<?php

namespace App\Controller;

use App\Entity\Setter;
use App\Form\SetterType;
use App\Repository\SetterRepository;
use App\Service\ContextService;
use App\Service\Serializer;
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
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        $statement = "SELECT setter.id, setter.username FROM setter INNER JOIN boulder_setters_v2 ON setter.id = boulder_setters_v2.setter_id INNER JOIN setter_locations ON setter.id = boulder_setters_v2.setter_id WHERE setter_locations.location_id = {$this->contextService->getLocation()->getId()}  GROUP BY setter.id;";
        $connection = $this->entityManager->getConnection();

        $query = $connection->prepare($statement);
        $query->execute();

        return $this->json($query->fetchAllAssociative());
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function current()
    {
        $statement = "SELECT setter.id, setter.username FROM setter INNER JOIN boulder_setters_v2 ON setter.id = boulder_setters_v2.setter_id INNER JOIN boulder ON boulder_setters_v2.boulder_id = boulder.id INNER JOIN setter_locations ON setter.id = boulder_setters_v2.setter_id WHERE boulder.status = 'active' AND setter_locations.location_id = {$this->contextService->getLocation()->getId()}  GROUP BY setter.id;";
        $connection = $this->entityManager->getConnection();

        $query = $connection->prepare($statement);
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
        $form->submit(self::decodePayLoad($request));

        if ($this->setterRepository->exists("username", $form->getData()->getUsername())) {
            $form->get("username")->addError(
                new FormError('This username is already taken')
            );
        }

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

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
