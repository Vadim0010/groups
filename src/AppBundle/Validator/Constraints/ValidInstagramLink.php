<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidInstagramLink extends Constraint
{
    public $message = 'The link "{{ string }}" this link is not instagram';
}