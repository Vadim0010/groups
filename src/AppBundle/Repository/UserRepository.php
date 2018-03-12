<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    /**
     * Получить количество пользователей
     *
     * @return mixed
     */
    public function getCountUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Получить недавно зарегистрированных пользователей
     *
     * @param $date
     * @param $limit
     * @return mixed
     */
    public function getRecentlyRegisteredUsers($date, $limit)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.profile', 'profile')
            ->addSelect('profile')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
