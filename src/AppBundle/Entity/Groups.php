<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Chat;
use AppBundle\Entity\Currency;
use AppBundle\Entity\UpHistory;
use AppBundle\Entity\User;
use AppBundle\Entity\Comments;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Validator\Constraints as LocalAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupsRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Groups
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="socialGroup")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Comments", mappedBy="group", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $comments;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     * @LocalAssert\ValidInstagramLink()
     * @ORM\Column(type="string", length=100)
     */
    private $link;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min="0")
     * @LocalAssert\ContainsNumeric()
     * @ORM\Column(type="decimal", precision=14, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isComment;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     */
    private $subscribers;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", unique=true, length=150)
     */
    private $slug;

    /**
     * @Assert\Range(min="0")
     * @LocalAssert\ContainsNumeric()
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    private $gain;

    /**
     * @Assert\Range(min="0")
     * @LocalAssert\ContainsNumeric()
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    private $expense;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerify = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=15)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=2, options={"fixed" = true})
     */
    private $social;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true, "default":0})
     */
    private $visitors;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $groupAvatar;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Chat", mappedBy="group")
     */
    private $chat;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UpHistory", mappedBy="group")
     * @ORM\OrderBy({"id":"DESC"})
     */
    private $upHistories;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Currency", inversedBy="group")
     */
    private $currency;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->visitors = 0;
        $this->chat = new ArrayCollection();
        $this->upHistories = new ArrayCollection();
    }

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
     * Set link
     *
     * @param string $link
     *
     * @return Groups
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Groups
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set isComment
     *
     * @param boolean $isComment
     *
     * @return Groups
     */
    public function setIsComment($isComment)
    {
        $this->isComment = $isComment;

        return $this;
    }

    /**
     * Get isComment
     *
     * @return boolean
     */
    public function getIsComment()
    {
        return $this->isComment;
    }

    /**
     * Set subscribers
     *
     * @param integer $subscribers
     *
     * @return Groups
     */
    public function setSubscribers($subscribers)
    {
        $this->subscribers = $subscribers;

        return $this;
    }

    /**
     * Get subscribers
     *
     * @return integer
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Groups
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Groups
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set gain
     *
     * @param string $gain
     *
     * @return Groups
     */
    public function setGain($gain)
    {
        $this->gain = $gain;

        return $this;
    }

    /**
     * Get gain
     *
     * @return string
     */
    public function getGain()
    {
        return $this->gain;
    }

    /**
     * Set expense
     *
     * @param string $expense
     *
     * @return Groups
     */
    public function setExpense($expense)
    {
        $this->expense = $expense;

        return $this;
    }

    /**
     * Get expense
     *
     * @return string
     */
    public function getExpense()
    {
        return $this->expense;
    }

    /**
     * Set isVerify
     *
     * @param boolean $isVerify
     *
     * @return Groups
     */
    public function setIsVerify($isVerify)
    {
        $this->isVerify = $isVerify;

        return $this;
    }

    /**
     * Get isVerify
     *
     * @return boolean
     */
    public function getIsVerify()
    {
        return $this->isVerify;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Groups
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Groups
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set social
     *
     * @param string $social
     *
     * @return Groups
     */
    public function setSocial($social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * Get social
     *
     * @return string
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set visitors
     *
     * @param integer $visitors
     *
     * @return Groups
     */
    public function setVisitors($visitors)
    {
        $this->visitors = $visitors;

        return $this;
    }

    /**
     * Get visitors
     *
     * @return integer
     */
    public function getVisitors()
    {
        return $this->visitors;
    }

    /**
     * Set groupAvatar
     *
     * @param string $groupAvatar
     *
     * @return Groups
     */
    public function setGroupAvatar($groupAvatar)
    {
        $this->groupAvatar = $groupAvatar;

        return $this;
    }

    /**
     * Get groupAvatar
     *
     * @return string
     */
    public function getGroupAvatar()
    {
        return $this->groupAvatar;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Groups
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
     * @return Groups
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
     * @return Groups
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
     * Set user
     *
     * @param User $user
     *
     * @return Groups
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

    /**
     * Add comment
     *
     * @param Comments $comment
     *
     * @return Groups
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
     * @return Groups
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
     * Add upHistory
     *
     * @param UpHistory $upHistory
     *
     * @return Groups
     */
    public function addUpHistory(UpHistory $upHistory)
    {
        $this->upHistories[] = $upHistory;

        return $this;
    }

    /**
     * Remove upHistory
     *
     * @param UpHistory $upHistory
     */
    public function removeUpHistory(UpHistory $upHistory)
    {
        $this->upHistories->removeElement($upHistory);
    }

    /**
     * Get upHistories
     *
     * @return \Doctrine\Common\Collections\Collection|UpHistory[]
     */
    public function getUpHistories()
    {
        return $this->upHistories;
    }

    /**
     * Set currency
     *
     * @param Currency $currency
     *
     * @return Groups
     */
    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }
}
