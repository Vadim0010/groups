<?php

namespace AppBundle\Entity;

use AppBundle\AppBundle;
use AppBundle\Entity\Chat;
use AppBundle\Entity\Comments;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Message;
use AppBundle\Entity\Reports;
use AppBundle\Entity\UserContacts;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Groups", mappedBy="user")
     */
    protected $socialGroup;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comments", mappedBy="user")
     */
    protected $comments;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Chat", mappedBy="user")
     */
    protected $chat;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="sender")
     */
    protected $sent_message;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\UserContacts", mappedBy="user", cascade={"all"})
     */
    protected $profile;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reports", mappedBy="sender")
     */
    protected $reports;

    /**
     * @ORM\OneToMany(targetEntity="InstagramBundle\Entity\InstagramPosts", mappedBy="user", cascade={"all"})
     */
    protected $instagramPosts;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $avatar;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActivityAt;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default"="2017-01-01 00:00:00"})
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default"="2017-01-01 00:00:00"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        parent::__construct();
        $this->socialGroup = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->chat = new ArrayCollection();
        $this->sent_message = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    /**
     * Add socialGroup
     *
     * @param Groups $socialGroup
     *
     * @return User
     */
    public function addSocialGroup(Groups $socialGroup)
    {
        $this->socialGroup[] = $socialGroup;

        return $this;
    }

    /**
     * Remove socialGroup
     *
     * @param Groups $socialGroup
     */
    public function removeSocialGroup(Groups $socialGroup)
    {
        $this->socialGroup->removeElement($socialGroup);
    }

    /**
     * Get socialGroup
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSocialGroup()
    {
        return $this->socialGroup;
    }

    /**
     * Add comment
     *
     * @param Comments $comment
     *
     * @return User
     */
    public function addComment(Comments $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param Comments $comment
     */
    public function removeComment(Comments $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add chat
     *
     * @param Chat $chat
     *
     * @return User
     */
    public function addChat(Chat $chat)
    {
        $this->chat[] = $chat;

        return $this;
    }

    /**
     * Remove chat
     *
     * @param Chat $chat
     */
    public function removeChat(Chat $chat)
    {
        $this->chat->removeElement($chat);
    }

    /**
     * Get chat
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * Add sentMessage
     *
     * @param Message $sentMessage
     *
     * @return User
     */
    public function addSentMessage(Message $sentMessage)
    {
        $this->sent_message[] = $sentMessage;

        return $this;
    }

    /**
     * Remove sentMessage
     *
     * @param Message $sentMessage
     */
    public function removeSentMessage(Message $sentMessage)
    {
        $this->sent_message->removeElement($sentMessage);
    }

    /**
     * Get sentMessage
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSentMessage()
    {
        return $this->sent_message;
    }

    /**
     * Set profile
     *
     * @param UserContacts $profile
     *
     * @return User
     */
    public function setProfile(UserContacts $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return UserContacts
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    public function getImagePath()
    {
        if($this->avatar){
            return $this->avatar;
        }

        return 'images/no-photo.png';
    }

    /**
     * Add report.
     *
     * @param Reports $report
     *
     * @return User
     */
    public function addReport(Reports $report)
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * Remove report.
     *
     * @param Reports $report
     */
    public function removeReport(Reports $report)
    {
        $this->reports->removeElement($report);
    }

    /**
     * Get reports.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set lastActivityAt
     *
     * @param \DateTime $lastActivityAt
     *
     * @return User
     */
    public function setLastActivityAt($lastActivityAt)
    {
        $this->lastActivityAt = $lastActivityAt;

        return $this;
    }

    /**
     * Get lastActivityAt
     *
     * @return \DateTime
     */
    public function getLastActivityAt()
    {
        return $this->lastActivityAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return User
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return bool
     */
    public function isActiveNow()
    {
        $delay = new \DateTime('3 minutes ago');

        return ( $this->getLastActivityAt() > $delay );
    }
}
