<?php

namespace App\Controller;

use Symfony\Component\Form\FormInterface;

trait FormErrorTrait
{
    public static function getFormErrors(FormInterface $form)
    {
        $errors = [];

        if ($form instanceof FormInterface) {

            foreach ($form->getErrors() as $error) {
                if ($error->getMessage() === "This form should not contain extra fields.") {
                    continue;
                }

                $errors[] = $error->getMessage();
            }

            foreach ($form->getExtraData() as $key => $value) {
                $errors[$key] = ["This field is unknown."];
            }

            foreach ($form->all() as $key => $child) {

                /**
                 * @var $child FormInterface|null
                 */
                $error = self::getFormErrors($child);

                if ($error) {
                    $errors[$key] = $error;
                }
            }
        }

        return $errors;
    }
}