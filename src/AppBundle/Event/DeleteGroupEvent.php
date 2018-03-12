<?php

namespace AppBundle\Event;

use AppBundle\Entity\Groups;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class DeleteGroupEvent extends Event
{
    protected $canDeleteGroup = false;

    protected $user;

    private $group;

    public function __construct(User $user, Groups $group)
    {
        $this->user = $user;
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getCanDeleteGroup()
    {
        return $this->canDeleteGroup;
    }

    /**
     * @param $canDeleteGroup
     *
     * @return $this
     */
    public function setCanDeleteGroup($canDeleteGroup)
    {
        $this->canDeleteGroup = $canDeleteGroup;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Groups
     */
    public function getGroup(): Groups
    {
        return $this->group;
    }

    /**
     * @param Groups $group
     */
    public function setGroup(Groups $group)
    {
        $this->group = $group;
    }
}