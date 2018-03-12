<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsNumericValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // ^[0-9]+([.|,][0-9]+)?$
        if($value != '' and ! is_numeric($value)){
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}