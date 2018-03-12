<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Chat;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    /**
     * Получить сообщения у чата
     *
     * @param Chat $chat
     * @param $limit
     * @return array
     */
    public function getChatMessages(Chat $chat, $limit)
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.chat', 'c')
            ->andWhere('c.id = :chat')
            ->setParameter('chat', $chat)
            ->orderBy('m.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Получить количество новых сообщений для текущего пользователя
     *
     * @param $user
     * @return mixed
     */
    public function getCountNewMessage($user)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->innerJoin('m.chat', 'c')
            ->innerJoin('c.user', 'u')
            ->andWhere('u.id = :user')
            ->andWhere('m.isRead = :read')
            ->andWhere('m.sender <> :user')
            ->setParameters([
                'user' => $user->getId(),
                'read' => false
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Получить список новых сообщений для текущего пользователя
     *
     * @param $user
     * @param $count
     * @return mixed
     */
    public function getNewMessage($user, $count)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.chat', 'c')
            ->leftJoin('m.sender', 'sender')
            ->innerJoin('c.user', 'u')
            ->addSelect('c')
            ->addSelect('sender')
            ->andWhere('u.id = :user')
            ->andWhere('m.isRead = :read')
            ->andWhere('m.sender <> :user')
            ->setParameters([
                'user' => $user->getId(),
                'read' => false
            ])
            ->setMaxResults($count)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Получить количество сообщений
     *
     * @return mixed
     */
    public function getCountMessages()
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
