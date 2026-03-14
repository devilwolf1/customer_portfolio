<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueProductName extends Constraint
{
    public $message = 'Le nom du produit "{{ value }}" est déjà utilisé.';
}
