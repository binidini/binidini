<?php

namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Exception\InsufficientFrozenAmount;
use Binidini\CoreBundle\Exception\InsufficientUserBalance;
use Binidini\CoreBundle\Model\SenderCarrierAwareInterface;
use Binidini\CoreBundle\Model\UserAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\VirtualProperty;


/**
 * Shipping
 *
 * @ORM\Table(name = "shipping")
 * @ORM\Entity
 * @Gedmo\Loggable
 * @ExclusionPolicy("all")
 */
class Shipping implements UserAwareInterface, SenderCarrierAwareInterface
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

    const GEOPOINT_TYPE_COORDINATION = 'coordination';
    const GEOPOINT_TYPE_ADDRESS = 'address';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127)
     * @Assert\NotBlank()
     * @Assert\Length(max=64)
     * @Expose
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Length(max=4000)
     * @Expose
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
     * @Expose
     */
    private $deliveryPrice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payment_guarantee", type="boolean", options={"default" = false})
     * @Expose
     */
    private $paymentGuarantee;

    /**
     * @var integer
     *
     * @ORM\Column(name="insurance", type="integer", options={"default" = 0})
     * @Assert\Range(min=0)
     * @Expose
     */
    private $insurance;

    /**
     * @var string
     *
     * @ORM\Column(name="pickup_address", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     * @Expose
     */
    private $pickupAddress;

    /**
     * @var double
     *
     * @ORM\Column(name="pickup_longitude", type="decimal", precision=12, scale=8, nullable=true )
     * @Expose
     */
    private $pickupLongitude;

    /**
     * @var double
     *
     * @ORM\Column(name="pickup_latitude", type="decimal", precision=12, scale=8, nullable=true)
     * @Expose
     */
    private $pickupLatitude;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_address", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     * @Expose
     */
    private $deliveryAddress;

    /**
     * @var double
     *
     * @ORM\Column(name="delivery_longitude", type="decimal", precision=12, scale=8, nullable=true)
     * @Expose
     */
    private $deliveryLongitude;

    /**
     * @var double
     *
     * @ORM\Column(name="delivery_latitude", type="decimal", precision=12, scale=8, nullable=true)
     * @Expose
     */
    private $deliveryLatitude;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pickup_datetime", type="datetime", nullable=true   )
     * @Expose
     */
    private $pickupDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_datetime", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @Expose
     */
    private $deliveryDatetime;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="parcels")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="shipments")
     * @ORM\JoinColumn(name="carrier_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    private $carrier;

    /**
     * Shipping state.
     *
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="state", type="string", length=32)
     * @Expose
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

    /**
     * @var bool
     *
     * @ORM\Column(name="has_user_review", type="boolean", options={"default" = false})
     */
    private $hasUserReview;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_carrier_review", type="boolean", options={"default" = false})
     */
    private $hasCarrierReview;

    /**
     * @Assert\File(
     *     maxSize = "1M",
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/png", "image/gif"},
     *     mimeTypesMessage = "Вы можете загрузить фото только в формате: JPG, GIF или PNG"
     * )
     */
    public $imgFile;

    /**
     * @ORM\Column(name="img_path", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     * @Expose
     */
    private $imgPath;

    public $imgBase64;
    public $fileName;

    /**
     * @var integer
     *
     * @ORM\Column(name="delivery_code", type="integer", options={"default" = 0})
     */
    private $deliveryCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="category", type="integer", options={"default" = 0})
     */
    private $category;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->state = Shipping::STATE_NEW;
        $this->hasCarrierReview = false;
        $this->hasUserReview = false;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->hasCarrierReview = false;
        $this->hasUserReview = false;

        $this->deliveryDatetime = new \DateTime();
        $this->deliveryDatetime->modify('+3 hours');
        $this->deliveryDatetime->setTimestamp(floor($this->deliveryDatetime->getTimestamp() / 3600) * 3600);

        $this->imgPath = 'parcels/pics/tytymyty_'.rand(1,18).'.jpg';
        $this->category = 0;
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
        $this->releaseCarrier();
        $this->releaseSender();
    }

    public function releaseSender()
    {
        if ($this->paymentGuarantee && $this->deliveryPrice > 0) {
            try {
                $this->user->release($this->deliveryPrice);
            } catch (InsufficientFrozenAmount $ex) {
                throw new InsufficientFrozenAmount("У отправителя не достаточно средств");
            }
        }
    }

    public function releaseCarrier()
    {
        if ($this->insurance > 0) {
            try {
                $this->carrier->release($this->insurance);
            } catch (InsufficientFrozenAmount $ex) {
                throw new InsufficientFrozenAmount("У перевозчика не достаточно средств");
            }
        }
    }

    public function payInsurance()
    {
        if ($this->insurance > 0) {
            try {
                $this->carrier->decreaseHoldBalance($this->insurance);
                $this->user->addBalance($this->insurance);
            } catch (InsufficientFrozenAmount $ex) {
                throw new InsufficientFrozenAmount("У перевозчика не достаточно средств");
            }
        }
    }

    public function payPayment(){
        if ($this->paymentGuarantee && $this->deliveryPrice > 0) {
            try {
                $this->user->decreaseHoldBalance($this->insurance);
                $this->carrier->addBalance($this->insurance);
            } catch (InsufficientFrozenAmount $ex) {
                throw new InsufficientFrozenAmount("У отправителя не достаточно средств");
            }
        }
    }

    /**
     * Get id
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
     * Get Accepted Bid
     *
     * @return \Binidini\CoreBundle\Entity\Bid
     */
    public function getAcceptedBid()
    {
        foreach($this->getBids() as $bid)
        {
            if ($bid->isAccepted()) {
                return $bid;
            }
        }
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
     * @param User $user
     * @return Shipping
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
     * Set sender
     *
     * @param User $user
     * @return Shipping
     */
    public function setSender(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getSender()
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

    /**
     * Checking than is new status
     * @return bool
     */
    public function isNew()
    {
        return $this->state === self::STATE_NEW;
    }

    /**
     * Checking can accept bid
     * @return bool
     */
    public function canAcceptBid()
    {
        return in_array($this->state, [self::STATE_NEW]);
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

    /**
     * Checking can be shipping has review
     *
     * @return bool
     */
    public function canHasReview()
    {
        return in_array($this->getState(), [self::STATE_CONFLICT, self::STATE_COMPLETED]);
    }

    /**
     * Set hasUserReview
     *
     * @param boolean $hasUserReview
     * @return Shipping
     */
    public function setHasUserReview($hasUserReview)
    {
        $this->hasUserReview = $hasUserReview;

        return $this;
    }

    /**
     * Get hasUserReview
     *
     * @return boolean 
     */
    public function getHasUserReview()
    {
        return $this->hasUserReview;
    }

    /**
     * Set hasCarrierReview
     *
     * @param boolean $hasCarrierReview
     * @return Shipping
     */
    public function setHasCarrierReview($hasCarrierReview)
    {
        $this->hasCarrierReview = $hasCarrierReview;

        return $this;
    }

    /**
     * Get hasCarrierReview
     *
     * @return boolean 
     */
    public function getHasCarrierReview()
    {
        return $this->hasCarrierReview;
    }

    /**
     * Set pickupLongitude
     *
     * @param string $pickupLongitude
     * @return Shipping
     */
    public function setPickupLongitude($pickupLongitude)
    {
        $this->pickupLongitude = $pickupLongitude;

        return $this;
    }

    /**
     * Get pickupLongitude
     *
     * @return string 
     */
    public function getPickupLongitude()
    {
        return $this->pickupLongitude;
    }

    /**
     * Set pickupLatitude
     *
     * @param string $pickupLatitude
     * @return Shipping
     */
    public function setPickupLatitude($pickupLatitude)
    {
        $this->pickupLatitude = $pickupLatitude;

        return $this;
    }

    /**
     * Get pickupLatitude
     *
     * @return string 
     */
    public function getPickupLatitude()
    {
        return $this->pickupLatitude;
    }

    /**
     * Set deliveryLongitude
     *
     * @param string $deliveryLongitude
     * @return Shipping
     */
    public function setDeliveryLongitude($deliveryLongitude)
    {
        $this->deliveryLongitude = $deliveryLongitude;

        return $this;
    }

    /**
     * Get deliveryLongitude
     *
     * @return string 
     */
    public function getDeliveryLongitude()
    {
        return $this->deliveryLongitude;
    }

    /**
     * Set deliveryLatitude
     *
     * @param string $deliveryLatitude
     * @return Shipping
     */
    public function setDeliveryLatitude($deliveryLatitude)
    {
        $this->deliveryLatitude = $deliveryLatitude;

        return $this;
    }

    /**
     * Get deliveryLatitude
     *
     * @return string 
     */
    public function getDeliveryLatitude()
    {
        return $this->deliveryLatitude;
    }

    public function getPickupGeoPoint()
    {
        if ($this->getPickupGeoPointType() == self::GEOPOINT_TYPE_COORDINATION) {
            return sprintf("[%f, %f]", $this->pickupLatitude, $this->pickupLongitude);
        } else {
            return sprintf("%s", $this->pickupAddress);
        }
    }

    public function  getPickupGeoPointType(){
        if ($this->pickupLongitude && $this->pickupLatitude){
            return self::GEOPOINT_TYPE_COORDINATION;
        } else {
            return self::GEOPOINT_TYPE_ADDRESS;
        }
    }

    public function getDeliveryGeoPoint()
    {
        if ($this->getDeliveryGeoPointType() == self::GEOPOINT_TYPE_COORDINATION) {
            return sprintf("[%f, %f]", $this->deliveryLatitude, $this->deliveryLongitude);
        } else {
            return sprintf("%s", $this->deliveryAddress);
        }
    }

    public function  getDeliveryGeoPointType(){

        if ($this->deliveryLongitude && $this->deliveryLatitude){
            return self::GEOPOINT_TYPE_COORDINATION;
        } else {
            return self::GEOPOINT_TYPE_ADDRESS;
        }
    }

    /**
     * Set imgPath
     *
     * @param string $imgPath
     * @return Shipping
     */
    public function setImgPath($imgPath)
    {
        $this->imgPath = $imgPath;

        return $this;
    }

    /**
     * Get imgPath
     *
     * @return string 
     */
    public function getImgPath()
    {
        return $this->imgPath;
    }


    /**
     * Set deliveryCode
     *
     * @param integer $deliveryCode
     * @return Shipping
     */
    public function setDeliveryCode($deliveryCode)
    {
        $this->deliveryCode = $deliveryCode;

        return $this;
    }

    /**
     * Get deliveryCode
     *
     * @return integer 
     */
    public function getDeliveryCode()
    {
        return $this->deliveryCode;
    }

    /**
     * Set category
     *
     * @param integer $category
     * @return Shipping
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return integer 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
