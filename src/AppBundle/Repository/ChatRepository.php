<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ChatRepository extends EntityRepository
{
    public function getChatListForUser($user)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->leftJoin('c.user', 'r', 'WITH', 'r <> :user')
            ->leftJoin('c.message', 'm', 'WITH', 'm.isRead = :read AND m.sender <> :user ')
            ->leftJoin('c.group', 'g')
            ->addSelect('m')
            ->addSelect('g')
            ->addSelect('r')
            ->andWhere('u = :user')
            ->setParameters([
                'user' => $user,
                'read' => false
            ])
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findChat($sender, $recipient, $group, $subject)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 's')
            ->innerJoin('c.user', 'r')
            ->andWhere('s = :sender')
            ->andWhere('r = :recipient')
            ->andWhere('c.group = :group')
            ->andWhere('c.subject = :subject')
            ->setParameters([
                'sender' => $sender,
                'recipient' => $recipient,
                'group' => $group,
                'subject' => $subject
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
