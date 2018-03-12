<?php

namespace AppBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RedirectAfterRegistrationSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;
    
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegisterSuccess'
        ];
    }

    /**
     * Редиректим пользователя на главную страницу после регестрации
     *
     * @param FormEvent $event
     */
    public function onRegisterSuccess(FormEvent $event)
    {
        $url = $this->getTargetPath($event->getRequest()->getSession(), 'main');
        if(! $url){
            $url = $this->router->generate('home');
        }
        $response = new RedirectResponse($url);

        $event->setResponse($response);
    }
}