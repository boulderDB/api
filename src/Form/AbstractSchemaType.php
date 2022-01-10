<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractSchemaType extends AbstractType implements SchemaTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->getSchema() as $field) {
            $builder->add($field["name"], $field["type"], $field["options"] ?? []);
        }
    }
}