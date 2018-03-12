<?php

namespace AppBundle\HelperClasses;

trait EntityClass
{
    /**
     * Найти сущность по ID
     *
     * @param $em
     * @param $entity_id
     * @param $repository
     * @return null|object
     */
    public function getEntity($em, $entity_id, $repository)
    {
        return is_numeric($entity_id) ? $em->getRepository($repository)->find($entity_id) : null;
    }
}