<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Exception\InsufficientFrozenAmount;
use Binidini\CoreBundle\Exception\InsufficientUserBalance;
use Binidini\CoreBundle\Model\UserAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Shipping
 *
 * @ORM\Table(name = "shipping")
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Shipping implements UserAwareInterface
{
    const GRAPH             = 'logic';

    const TRANSITION_ACCEPT = 'accept';
    const TRANSITION_RECALL = 'recall';

    const STATE_INIT        = 'init';
    const STATE_NEW         = 'new';
    const STATE_ACCEPTED    = 'accepted';
    const STATE_DELIVERED   = 'delivered';     #awaiting_payment
    const STATE_PAID        = 'paid';          #awaiting_payment_confirmation
    const STATE_COMPLETED   = 'completed';
    const STATE_REJECTED    = 'rejected';      #awaiting_cancel_confirmation
    const STATE_CANCELED    = 'canceled';
    const STATE_CONFLICT    = 'conflict';

    //время в минутах, втечение которого перевозчик может отозвать заявку после принятия ее отправителем
    const CARRIER_RECALL_TIME = 10;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127)
     * @Assert\NotBlank()
     * @Assert\Length(max=64)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Length(max=4000)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="x", type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $x;

    /**
     * @var integer
     *
     * @ORM\Column(name="y", type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $y;

    /**
     * @var integer
     *
     * @ORM\Column(name="z", type="integer", nullable=true)
     * @Assert\Range(min=0)
     */
    private $z;

    /**
     * @var integer
     *
     * @ORM\Column(name="delivery_price", type="integer", options={"default" = 0})
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     * @Assert\Range(min=0)
     */
    private $deliveryPrice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payment_guarantee", type="boolean", options={"default" = false})
     */
    private $paymentGuarantee;

    /**
     * @var integer
     *
     * @ORM\Column(name="insurance", type="integer", options={"default" = 0})
     * @Assert\NotBlank()
     * @Assert\Range(min=0)
     */
    private $insurance;

    /**
     * @var string
     *
     * @ORM\Column(name="pickup_address", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     */
    private $pickupAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_address", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     */
    private $deliveryAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pickup_datetime", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $pickupDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_datetime", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $deliveryDatetime;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="parcels")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="shipments")
     * @ORM\JoinColumn(name="carrier_id", referencedColumnName="id")
     */
    private $carrier;

    /**
     * Shipping state.
     *
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="state", type="string", length=32)
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="Bid", mappedBy="shipping")
     */
    private $bids;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="shipping")
     */
    private $messages;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
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
        $this->items = new ArrayCollection();
        $this->state = Shipping::STATE_NEW;

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function hold()
    {
        if ($this->insurance > 0) {
            try {
                $this->carrier->hold($this->insurance);
            } catch (InsufficientUserBalance $ex) {
                throw new InsufficientUserBalance("У перевозчика нет средств на страховку.");
            }
        }

        if ($this->paymentGuarantee && $this->deliveryPrice > 0) {
            try {
                $this->user->hold($this->deliveryPrice);
            } catch (InsufficientUserBalance $ex) {
                //вернем деньги перевозчику
                $this->carrier->release($this->insurance);
                throw new InsufficientUserBalance("У отправителя нет средств на гарантию.");
            }
        }
    }

    public function release()
    {
        if ($this->insurance > 0) {
            try {
                $this->carrier->release($this->insurance);
            } catch (InsufficientFrozenAmount $ex) {
                throw new InsufficientFrozenAmount("У перевозчика недостаточно средств для разморозки.");
            }
        }

        if ($this->paymentGuarantee && $this->deliveryPrice > 0) {
            try {
                $this->user->release($this->deliveryPrice);
            } catch (InsufficientFrozenAmount $ex) {
                //заморозим обратно деньги перевозчика
                $this->carrier->hold($this->insurance);
                throw new InsufficientFrozenAmount("У отправителя недостаточно средств для разморозки.");
            }
        }
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
     * Set carrier
     *
     * @param \Binidini\CoreBundle\Entity\User $carrier
     * @return Shipping
     */
    public function setCarrier(\Binidini\CoreBundle\Entity\User $carrier = null)
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * Get carrier
     *
     * @return \Binidini\CoreBundle\Entity\User 
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * Add bids
     *
     * @param \Binidini\CoreBundle\Entity\Bid $bids
     * @return Shipping
     */
    public function addBid(\Binidini\CoreBundle\Entity\Bid $bids)
    {
        $this->bids[] = $bids;

        return $this;
    }

    /**
     * Remove bids
     *
     * @param \Binidini\CoreBundle\Entity\Bid $bids
     */
    public function removeBid(\Binidini\CoreBundle\Entity\Bid $bids)
    {
        $this->bids->removeElement($bids);
    }

    /**
     * Get bids
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBids()
    {
        return $this->bids;
    }

    /**
     * Set pickupAddress
     *
     * @param string $pickupAddress
     * @return Shipping
     */
    public function setPickupAddress($pickupAddress)
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    /**
     * Get pickupAddress
     *
     * @return string 
     */
    public function getPickupAddress()
    {
        return $this->pickupAddress;
    }

    /**
     * Set deliveryAddress
     *
     * @param string $deliveryAddress
     * @return Shipping
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return string 
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set pickupDatetime
     *
     * @param \DateTime $pickupDatetime
     * @return Shipping
     */
    public function setPickupDatetime($pickupDatetime)
    {
        $this->pickupDatetime = $pickupDatetime;

        return $this;
    }

    /**
     * Get pickupDatetime
     *
     * @return \DateTime 
     */
    public function getPickupDatetime()
    {
        return $this->pickupDatetime;
    }

    /**
     * Set deliveryDatetime
     *
     * @param \DateTime $deliveryDatetime
     * @return Shipping
     */
    public function setDeliveryDatetime($deliveryDatetime)
    {
        $this->deliveryDatetime = $deliveryDatetime;

        return $this;
    }

    /**
     * Get deliveryDatetime
     *
     * @return \DateTime 
     */
    public function getDeliveryDatetime()
    {
        return $this->deliveryDatetime;
    }

    /**
     * Set user
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @return Shipping
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Shipping
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



    /**
     * Set deliveryPrice
     *
     * @param integer $deliveryPrice
     * @return Shipping
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    /**
     * Get deliveryPrice
     *
     * @return integer 
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Shipping
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
     * Set description
     *
     * @param string $description
     * @return Shipping
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
     * Set weight
     *
     * @param integer $weight
     * @return Shipping
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set x
     *
     * @param integer $x
     * @return Shipping
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return integer 
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param integer $y
     * @return Shipping
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return integer 
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set z
     *
     * @param integer $z
     * @return Shipping
     */
    public function setZ($z)
    {
        $this->z = $z;

        return $this;
    }

    /**
     * Get z
     *
     * @return integer 
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Shipping
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
     * @return Shipping
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
     * Add messages
     *
     * @param \Binidini\CoreBundle\Entity\Message $messages
     * @return Shipping
     */
    public function addMessage(\Binidini\CoreBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Binidini\CoreBundle\Entity\Message $messages
     */
    public function removeMessage(\Binidini\CoreBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function isNew(){
        return $this->state === self::STATE_NEW;
    }

    /**
     * Set paymentGuarantee
     *
     * @param boolean $paymentGuarantee
     * @return Shipping
     */
    public function setPaymentGuarantee($paymentGuarantee)
    {
        $this->paymentGuarantee = $paymentGuarantee;

        return $this;
    }

    /**
     * Get paymentGuarantee
     *
     * @return boolean 
     */
    public function getPaymentGuarantee()
    {
        return $this->paymentGuarantee;
    }

    /**
     * Set insurance
     *
     * @param integer $insurance
     * @return Shipping
     */
    public function setInsurance($insurance)
    {
        $this->insurance = $insurance;

        return $this;
    }

    /**
     * Get insurance
     *
     * @return integer 
     */
    public function getInsurance()
    {
        return $this->insurance;
    }
}
