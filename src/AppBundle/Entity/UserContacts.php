<?php

namespace AppBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_contacts")
 */
class UserContacts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(max=255, min=3)
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="profile")
     */
    private $user;

    /**
     * @Assert\Length(max=255, min=3)
     * @ORM\Column(type="string", nullable=true)
     */
    private $skype;

    /**
     * @Assert\Length(max=255, min=3)
     * @ORM\Column(type="string", nullable=true)
     */
    private $viber;

    /**
     * @Assert\Length(max=255, min=3)
     * @ORM\Column(type="string", nullable=true)
     */
    private $telegram;

    /**
     * @Assert\Email()
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

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
     * Set skype
     *
     * @param string $skype
     *
     * @return UserContacts
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * Set viber
     *
     * @param string $viber
     *
     * @return UserContacts
     */
    public function setViber($viber)
    {
        $this->viber = $viber;

        return $this;
    }

    /**
     * Get viber
     *
     * @return string
     */
    public function getViber()
    {
        return $this->viber;
    }

    /**
     * Set telegram
     *
     * @param string $telegram
     *
     * @return UserContacts
     */
    public function setTelegram($telegram)
    {
        $this->telegram = $telegram;

        return $this;
    }

    /**
     * Get telegram
     *
     * @return string
     */
    public function getTelegram()
    {
        return $this->telegram;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserContacts
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserContacts
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
