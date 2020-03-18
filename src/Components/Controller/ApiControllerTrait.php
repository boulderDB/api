<?php

namespace App\Components\Controller;

use Symfony\Component\Form\FormInterface;

trait ApiControllerTrait
{
    private static function isValidId($id): bool
    {
        return (int)$id > 0;
    }


    private function getFormErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }
}