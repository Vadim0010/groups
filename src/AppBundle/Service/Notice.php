<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @method alert($message)
 * @method success($message)
 * @method warning($message)
 * @method error($message)
 */
class Notice
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    private function setFlash($message, $type)
    {
        $this->session->getFlashBag()->add('noty', [
            'type' => $type,
            'text' => $message
        ]);
    }

    /**
     * Методы которые будут работать
     * alert, success, warning, error
     *
     * @param $type
     * @param $msg
     */
    public function __call($type, $msg)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $this->setFlash($accessor->getValue($msg, '[0]'), $type);
    }
}