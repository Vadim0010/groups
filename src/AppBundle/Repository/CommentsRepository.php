<?php

namespace AppBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CommentsRepository extends NestedTreeRepository
{
    /**
     * Получить количество комментариев
     *
     * @return mixed
     */
    public function getCountComments()
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
