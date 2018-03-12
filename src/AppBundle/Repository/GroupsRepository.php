<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GroupsRepository extends EntityRepository
{
    public function getDqlOrderedGroups()
    {
        return $this->createQueryBuilder('groups')
            ->leftJoin('groups.user', 'user')
            ->leftJoin('groups.currency', 'currency')
            ->addSelect('currency')
            ->orderBy('groups.updatedAt', 'DESC')
        ;
    }

    /**
     * @param $user
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException|null
     */
    public function getUserTopGroup($user)
    {
        $result = $this->createQueryBuilder('groups')
            ->select('MAX(groups.subscribers) as subsribers')
            ->andWhere('groups.user=:user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if($result){
            return $result['subsribers'];
        }

        return $result;
    }

    /**
     * @param $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDqlUserGroups($user)
    {
        return $this->createQueryBuilder('groups')
            // ->leftJoin('groups.currency', 'currency')
            // ->addSelect('currency')
            ->andWhere('groups.user=:user')
            ->setParameter('user', $user)
            ->orderBy('groups.updatedAt', 'DESC')
        ;
    }

    /**
     * Запрос при поиске
     *
     * @param array $option
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function searchDqlGroups(array $option)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $names = $this->handleGroupNameWhenSearching($accessor->getValue($option, '[name]'));
        $subscribers = $this->handleGroupNumbersWhenSearching($accessor, $accessor->getValue($option, '[subscribers]'));
        $price = $this->handleGroupNumbersWhenSearching($accessor, $accessor->getValue($option, '[price]'));
        $gain = $this->handleGroupNumbersWhenSearching($accessor, $accessor->getValue($option, '[gain]'));
        $verify = $accessor->getValue($option, '[isVerify]');

        $qb = $this->createQueryBuilder('groups');

        $groups = $qb
            ->leftJoin('groups.user', 'user')
            ->leftJoin('groups.currency', 'currency')
            ->addSelect('currency')
        ;

        if ($names) {
            $orX = $qb->expr()->orX();

            foreach ($names as $key => $name) {
                $orX->add(
                    $qb->expr()->like('LOWER(groups.name)', ':name_' . $key)
                );
                $groups->setParameter('name_' . $key, $name );
            }

            $groups
                ->andWhere($orX)
            ;
        }

        if ($accessor->getValue($subscribers, '[is_search]')) {
            $sub_from = $accessor->getValue($subscribers, '[from]');
            $sub_to = $accessor->getValue($subscribers, '[to]');
            $groups
                ->andWhere('groups.subscribers BETWEEN :sub_from AND :sub_to')
                ->setParameter('sub_from', $sub_from ?? 0)
                ->setParameter('sub_to', $sub_to ?? PHP_INT_MAX)
            ;
        }

        if ($accessor->getValue($price, '[is_search]')) {
            $pr_from = $accessor->getValue($price, '[from]');
            $pr_to = $accessor->getValue($price, '[to]');
            $groups
                ->andWhere('groups.price BETWEEN :pr_from AND :pr_to')
                ->setParameter('pr_from', $pr_from ?? 0)
                ->setParameter('pr_to', $pr_to ?? PHP_INT_MAX)
            ;
        }

        if ($accessor->getValue($gain, '[is_search]')) {
            $gain_from = $accessor->getValue($gain, '[from]');
            $gain_to = $accessor->getValue($gain, '[to]');
            $groups
                ->andWhere('groups.gain BETWEEN :gain_from AND :gain_to')
                ->setParameter('gain_from', $gain_from ?? 0)
                ->setParameter('gain_to', $gain_to ?? PHP_INT_MAX)
            ;
        }

        if ($verify) {
            $groups->andWhere('groups.isVerify = 1');
        }

        $groups->orderBy('groups.updatedAt', 'DESC');


        return $groups;
    }

    /**
     * Получить количество групп
     *
     * @return mixed
     */
    public function getCountGroups()
    {
        return $this->createQueryBuilder('g')
            ->select('count(g.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Получить недавно добавленные группы
     *
     * @param $date
     * @param $limit
     * @return mixed
     */
    public function getRecentlyAddedGroups($date, $limit)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('g.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Обновить количество просмотров
     *
     * @param $group
     * @param $countVisitors
     * @return mixed
     */
    public function updateVisitors($group, $countVisitors)
    {
        return $this->createQueryBuilder('g')
            ->update()
            ->set('g.visitors', ':visitors')
            ->andWhere('g = :group')
            ->setParameter('visitors', $countVisitors)
            ->setParameter('group', $group)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Обработать поле "Ключевые слова" при поиске
     *
     * @param $name
     *
     * @return array|bool|mixed|string
     */
    private function handleGroupNameWhenSearching($name)
    {
        if ($name) {
            $search = [',', '.', ' - ', ':', ';', '!', '?', '/', '\\', '|', '=', '+', '(', ')', '[', ']', '{', '}'];
            $names = str_replace($search, ' ', $name);
            $names = mb_strtolower($names);
            $names = explode(' ', $names);

            foreach ($names as $key => $item) {
                $names[$key] = '%' . trim($item) . '%';
            }

            return $names;
        }

        return false;
    }

    /**
     * Обработать поля "От" и "До" при поиске
     *
     * @param $accessor
     * @param $array
     *
     * @return array
     */
    private function handleGroupNumbersWhenSearching($accessor, $array)
    {
        $from = $accessor->getValue($array, '[from]');
        $to = $accessor->getValue($array, '[to]');

        $data = [
            'is_search' => $from || $to ? true : false,
            'from' => $from ?? null,
            'to' => $to ?? null
        ];

        return $data;
    }
}
