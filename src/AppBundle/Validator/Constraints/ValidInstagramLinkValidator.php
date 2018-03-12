<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidInstagramLinkValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if(! preg_match(\AppBundle\Service\InstagramApiClient::GROUP_ID, $value)){
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;
        }
    }
}