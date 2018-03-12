<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ReportsRepository extends EntityRepository
{
    /**
     * Получить количество новых писем
     *
     * @return mixed
     */
    public function getCountNewReports()
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.isRead = :read')
            ->setParameter('read', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Получить новые письма
     *
     * @return mixed
     */
    public function getNewReports()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isRead = :read')
            ->setParameter('read', false)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
