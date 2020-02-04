<?php

namespace App\Components\Controller;

use Symfony\Component\Form\FormInterface;

trait ApiControllerTrait
{
    private function getFormErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }
}