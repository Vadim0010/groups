<?php

namespace AppBundle\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SearchListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
//            FormEvents::PRE_SET_DATA => 'preSetData',
//            FormEvents::POST_SET_DATA => 'postSetData',
//            FormEvents::PRE_SUBMIT => 'preSubmit',
//            FormEvents::SUBMIT => 'submit',
//            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    public function postSetData(FormEvent $event)
    {
    }

    public function postSubmit(FormEvent $event)
    {
    }

    public function preSetData(FormEvent $event)
    {
    }

    public function preSubmit(FormEvent $event)
    {
    }

    public function submit(FormEvent $event)
    {
    }

}