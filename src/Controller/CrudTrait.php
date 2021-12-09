<?php

namespace App\Controller;

use App\Entity\DeactivatableInterface;
use App\Entity\LocationResourceInterface;
use App\Entity\UserResourceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method createForm(string $type, $data = null, array $options = []): FormInterface
 * @property \Doctrine\ORM\EntityManagerInterface entityManager
 */
trait CrudTrait
{
    use ResponseTrait;
    use RequestTrait;

    private function handleForm(Request $request, $resource, string $formType): FormInterface
    {
        if (is_string($resource)) {
            $object = new $resource;
        } else {
            $object = $resource;
        }

        $form = $this->createForm($formType, $object);
        $form->submit(self::decodePayLoad($request));

        return $form;
    }

    private function createEntity(Request $request, $resource, string $formType, callable $prePersist = null)
    {
        $form = $this->handleForm($request, $resource, $formType);
        $object = $form->getData();

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        if (is_callable($prePersist)) {
            $prePersist($object);
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();

        return $this->createdResponse($object);
    }

    private function readEntity(string $resource, string $id, array $groups = ["default"])
    {
        $entity = new $resource;

        if ($entity instanceof LocationResourceInterface) {
            if (!property_exists($this, "contextService")) {
                throw new \LogicException("Cannot read resource '$resource' without location context");
            }

            $object = $this->entityManager->getRepository($resource)->findOneBy([
                "id" => $id,
                "location" => $this->contextService->getLocation()->getId()
            ]);
        } else {
            $object = $this->entityManager->getRepository($resource)->find($id);
        }

        if (!$object) {
            return $this->resourceNotFoundResponse($resource::RESOURCE_NAME, $id);
        }

        return $this->okResponse($object, $groups);
    }

    private function updateEntity(Request $request, string $resource, string $formType, string $id, bool $clearMissing = false)
    {
        $object = $this->entityManager->getRepository($resource)->find($id);

        if (!$object) {
            return $this->resourceNotFoundResponse($resource::RESOURCE_NAME, $id);
        }

        if ($object instanceof UserResourceInterface) {
            if (!method_exists($this, "getUser")) {
                throw new AccessDeniedHttpException("Access denied");
            }

            if (!$this->getUser() instanceof UserInterface) {
                throw new AccessDeniedHttpException("Access denied");
            }

            if ($this->getUser() !== $object->getUser()) {
                throw new AccessDeniedHttpException("Access denied");
            }
        }

        /**
         * @var \Symfony\Component\Form\Form $form
         */
        $form = $this->createForm($formType, $object);
        $form->submit(self::decodePayLoad($request), $clearMissing);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    private function deleteEntity(string $resource, string $id, bool $deactivate = false)
    {
        $object = $this->entityManager->getRepository($resource)->find($id);

        if (!$object) {
            return $this->resourceNotFoundResponse($resource::RESOURCE_NAME, $id);
        }

        if ($object instanceof UserResourceInterface) {
            if (!method_exists($this, "getUser")) {
                throw new AccessDeniedHttpException("Access denied");
            }

            if (!$this->getUser() instanceof UserInterface) {
                throw new AccessDeniedHttpException("Access denied");
            }

            if ($this->getUser() !== $object->getUser()) {
                throw new AccessDeniedHttpException("Access denied");
            }
        }

        if ($deactivate && !$object instanceof DeactivatableInterface) {
            $class = DeactivatableInterface::class;
            throw new \LogicException("Cannot deactivate $resource as it does not implement $class");
        }

        if ($deactivate && $object instanceof DeactivatableInterface && $object->isActive()) {
            $object->setActive(false);
            $object->setName($object->getName() . " (deactivated)");
            $this->entityManager->persist($object);
        } else {
            $this->entityManager->remove($object);
        }

        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}