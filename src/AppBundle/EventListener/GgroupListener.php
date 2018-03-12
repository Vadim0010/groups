<?php

namespace AppBundle\EventListener;

use AppBundle\AppBundleEvents;
use AppBundle\Event\DeleteGroupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GgroupListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            AppBundleEvents::DELETING_GROUP => 'checkUser'
        ];
    }

    /**
     * Проверяем причастност ьпользователя к группе
     *
     * @param DeleteGroupEvent $event
     */
    public function checkUser(DeleteGroupEvent $event)
    {
        $user = $event->getUser();
        $group = $event->getGroup();

        $event->setCanDeleteGroup(
            $user->getId() === $group->getUser()->getId()
        );
    }
}