<?php

namespace App\Controller;

use App\Entity\DeactivatableInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method createForm(string $type, $data = null, array $options = []): FormInterface
 * @property \Doctrine\ORM\EntityManagerInterface entityManager
 */
trait CrudTrait
{
    use ResponseTrait;
    use RequestTrait;

    private function createEntity(Request $request, $resource, string $formType)
    {
        if (is_string($resource)) {
            $object = new $resource;
        } else {
            $object = $resource;
        }

        if (!is_object($object)) {
            $type = gettype($resource);
            throw new \InvalidArgumentException("Cannot handle type '$type' as resource");
        }

        $form = $this->createForm($formType, $object);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($object);
        $this->entityManager->flush();

        return $this->createdResponse($object);
    }

    private function readEntity(string $resource, string $id, array $groups = ["default", "detail"])
    {
        $object = $this->entityManager->getRepository($resource)->find($id);

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

        return $this->createdResponse($object);
    }

    private function deleteEntity(string $resource, string $id, bool $deactivate = false)
    {
        $object = $this->entityManager->getRepository($resource)->find($id);

        if (!$object) {
            return $this->resourceNotFoundResponse($resource::RESOURCE_NAME, $id);
        }

        if ($deactivate && !$object instanceof DeactivatableInterface) {
            $class = DeactivatableInterface::class;
            throw new \LogicException("Cannot deactivate $resource as it does not implement $class");
        }

        if ($deactivate) {
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