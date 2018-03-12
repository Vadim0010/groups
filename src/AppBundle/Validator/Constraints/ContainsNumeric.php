<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsNumeric extends Constraint
{
    public $message = 'The string "{{ string }}" contains an illegal character: it can only contain numbers.';
}