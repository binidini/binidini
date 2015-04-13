<?php

namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Model\SenderCarrierAwareInterface;
use Binidini\CoreBundle\Model\UserAwareInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Bid
 *
 * @ORM\Table(name = "bid")
 * @ORM\Entity
 */
class Bid implements UserAwareInterface, SenderCarrierAwareInterface
{
    const GRAPH             = 'simple';

    const STATE_NEW        = 'new';
    const STATE_ACCEPTED   = 'accepted';
    const STATE_AGREED     = 'agreed';
    const STATE_REJECTED   = 'rejected';
    const STATE_CANCELED   = 'canceled';
    const STATE_AUTO_REJECTED = 'auto_rejected';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer", options={"default" = 0})
     * @Assert\NotBlank()
     * @Assert\Range(min=0)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Shipping", inversedBy="bids")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=false)
     */
    protected $shipping;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bids")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     */
    protected $sender;

    /**
     * Bid state.
     *
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=32)
     */
    protected $state = Bid::STATE_NEW;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     * Set comment
     *
     * @param string $comment
     * @return Bid
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set shipping
     *
     * @param \Binidini\CoreBundle\Entity\Shipping $shipping
     * @return Bid
     */
    public function setShipping(\Binidini\CoreBundle\Entity\Shipping $shipping = null)
    {
        $this->shipping = $shipping;

        $this->sender = $shipping->getUser();

        return $this;
    }

    /**
     * Get shipping
     *
     * @return \Binidini\CoreBundle\Entity\Shipping 
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Bid
     */
    public function setUser(User $user)
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
     * Set carrier
     *
     * @param User $user
     * @return Bid
     */
    public function setCarrier(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getCarrier()
    {
        return $this->user;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Bid
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Bid
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
     * Set state
     *
     * @param string $state
     * @return Bid
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    public function isNew()
    {
        return $this->state === self::STATE_NEW;
    }

    public function isAutoRejected()
    {
        return $this->state === self::STATE_AUTO_REJECTED;
    }

    public function isAccepted()
    {
        return $this->state === self::STATE_ACCEPTED;
    }

    public function isAgreed()
    {
        return $this->state === self::STATE_AGREED;
    }
    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Bid
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
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->setUpdatedAt(new \DateTime());
    }


    /**
     * Set sender
     *
     * @param User $sender
     * @return Bid
     */
    public function setSender(User $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

}
