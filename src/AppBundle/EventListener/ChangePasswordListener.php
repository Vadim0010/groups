<?php

namespace AppBundle\EventListener;

use AppBundle\Service\Notice;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChangePasswordListener implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Notice
     */
    private $noty;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(UrlGeneratorInterface $router, Notice $noty, TranslatorInterface $translator)
    {
        $this->noty = $noty;
        $this->router = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess'
        ];
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $url = $this->router->generate('fos_user_change_password');
        $event->setResponse(new RedirectResponse($url));
        $this->noty->success($this->translator->trans('password_changed_successfully'));
    }
}