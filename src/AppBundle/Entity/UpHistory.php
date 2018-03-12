<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Groups;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="up_history")
 */
class UpHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", options={"unsigned":true})
     */
    private $subscribersBefore;

    /**
     * @ORM\Column(type="bigint", options={"unsigned":true})
     */
    private $subscribersAfter;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Groups", inversedBy="upHistories")
     */
    private $group;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subscriversBefore
     *
     * @param integer $subscribersBefore
     *
     * @return UpHistory
     */
    public function setSubscribersBefore($subscribersBefore)
    {
        $this->subscribersBefore = $subscribersBefore;

        return $this;
    }

    /**
     * Get subscriversBefore
     *
     * @return integer
     */
    public function getSubscribersBefore()
    {
        return $this->subscribersBefore;
    }

    /**
     * Set subscribersAfter
     *
     * @param integer $subscribersAfter
     *
     * @return UpHistory
     */
    public function setSubscribersAfter($subscribersAfter)
    {
        $this->subscribersAfter = $subscribersAfter;

        return $this;
    }

    /**
     * Get subscribersAfter
     *
     * @return integer
     */
    public function getSubscribersAfter()
    {
        return $this->subscribersAfter;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return UpHistory
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set group
     *
     * @param Groups $group
     *
     * @return UpHistory
     */
    public function setGroup(Groups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return Groups
     */
    public function getGroup()
    {
        return $this->group;
    }
}
