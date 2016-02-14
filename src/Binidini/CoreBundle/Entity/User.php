<?php

namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Exception\InsufficientFrozenAmount;
use Binidini\CoreBundle\Exception\InsufficientUserBalance;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="Binidini\CoreBundle\Entity\UserRepository")
 * @ORM\Table(name = "user")
 *
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false)),
 * })
 *
 * @Gedmo\Uploadable(
 *      allowOverwrite=true,
 *      filenameGenerator="SHA1",
 *      allowedTypes="image/jpeg,image/pjpeg,image/png,image/x-png,image/gif",
 *      maxSize = 32000000
 * )
 * @ExclusionPolicy("all")
 *
 */
class User extends BaseUser
{
    const TYPE_INDIVIDUAL = 1;
    const TYPE_BUSINESS = 2;

    const PROFILE_TYPE_CARRIER = 1;
    const PROFILE_TYPE_SENDER = 2;
    const PROFILE_TYPE_CARRIER_AND_SENDER = 0;

    const BIT_ACCEPT_BID = 0;
    const BIT_REJECT_BID = 1;
    const BIT_CANCEL_BID = 2;
    const BIT_RECALL_BID = 3;
    const BIT_AGREE_BID  = 4;
    const BIT_CREATE_BID = 5;

    const BIT_CREATE_SHIPPING   = 10;
    const BIT_ACCEPT_SHIPPING   = 11;
    const BIT_DISPATCH_SHIPPING = 12;
    const BIT_LOAD_SHIPPING     = 13;
    const BIT_DELIVER_SHIPPING  = 14;
    const BIT_PAY_SHIPPING      = 15;
    const BIT_COMPLETE_SHIPPING = 16;
    const BIT_REJECT_SHIPPING   = 17;
    const BIT_REFUSE_SHIPPING   = 18;
    const BIT_CANCEL_SHIPPING   = 19;
    const BIT_NULLIFY_SHIPPING  = 20;
    const BIT_ANNUL_SHIPPING    = 21;
    const BIT_DISPUTE_SHIPPING  = 22;
    const BIT_DEBATE_SHIPPING   = 23;
    const BIT_RESOLVE_SHIPPING  = 24;

    const BIT_MESSAGE_SHIPPING    = 25;

    const PASSWORD_RECOVER_TTL = 259200;
    const PASSWORD_RECOVER_PREFIX = 'password_recover:';
    const PASSWORD_RECOVER_ATTEMPTS = 3;
    const PASSWORD_RECOVER_ATTEMPT_PREFIX = 'password_recover_attempts:';
    const PASSWORD_RECOVER_ATTEMPTS_TTL = 3600;

    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=128, nullable=true)
     * @Expose
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=128, nullable=true)
     * @Expose
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="patronymic", type="string", length=128, nullable=true)
     * @Expose
     */
    private $patronymic;

    /**
     * @var string
     * @ORM\Column(name="img_path", type="string", length=255, nullable=true)
     * @Gedmo\UploadableFileName
     * @Expose
     */
    private $imgPath;

    public $imgBase64;
    public $fileName;

    /**
     * @ORM\OneToMany(targetEntity="Shipping", mappedBy="user")
     */
    private $parcels;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user")
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="Shipping", mappedBy="carrier")
     */
    private $shipments;

    /**
     * @var integer
     *
     * @ORM\Column(name="balance", type="integer", options={"default" = 0})
     * @Expose
     */
    private $balance;

    /**
     * @var integer
     *
     * @ORM\Column(name="hold_amount", type="integer", options={"default" = 0})
     * @Expose
     */
    private $holdAmount;

    /**
     * @ORM\OneToMany(targetEntity="Bid", mappedBy="user")
     */
    private $bids;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="user")
     */
    private $payments;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=128, nullable=true)
     * @Expose
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, options={"default" = 1})
     * @Expose
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_type", type="integer", options={"default" = 0})
     * @Expose
     */
    private $profileType;

    /**
     * @var string
     *
     * @ORM\Column(name="about_me", type="string", length=255, nullable=true)
     * @Expose
     */
    private $aboutMe;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @Expose
     */

    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Expose
     */

    private $address;

    /**
     * @var integer
     *
     * @ORM\Column(name="sms_mask", type="integer", options={"default"=0})
     */

    private $smsMask;

    /**
     * @var integer
     *
     * @ORM\Column(name="email_mask", type="integer", options={"default"=0})
     */

    private $emailMask;

    /**
     * @var integer
     *
     * @ORM\Column(name="gcm_mask", type="integer", options={"default"=0})
     */

    private $gcmMask;

    /**
     * @var bool
     *
     * @ORM\Column(name="email_verified", type="boolean", options={"default"=false})
     */
    private $emailVerified;

    /**
     * @var integer
     *
     * @ORM\Column(name="sender_rating", type="float", scale=2, options={"default"=0})
     * @Expose
     */
    private $senderRating;

    /**
     * @var integer
     *
     * @ORM\Column(name="sender_rating_amount", type="integer", options={"default"=0})
     */
    private $senderRatingAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="sender_rating_count", type="integer", options={"default"=0})
     */
    private $senderRatingCount;

    /**
     * @var integer
     *
     * @ORM\Column(name="carrier_rating", type="float", scale=2, options={"default"=0})
     * @Expose
     */
    private $carrierRating;

    /**
     * @var integer
     *
     * @ORM\Column(name="carrier_rating_amount", type="integer", options={"default"=0})
     */
    private $carrierRatingAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="carrier_rating_count", type="integer", options={"default"=0})
     */
    private $carrierRatingCount;

    /**
     * Second password for recover
     *
     * @var string
     *
     * @ORM\Column(name="recover_password", type="string", length=255, nullable=true)
     */
    protected $recoverPassword;

    /**
     * The salt for recover password
     *
     * @var string
     *
     * @ORM\Column(name="recover_salt", type="string", length=255, nullable=true)
     */
    protected $recoverSalt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="sender_count", type="integer", options={"default"=0})
     */
    private $senderCount;

    /**
     * @ORM\OneToMany(targetEntity="GcmToken", mappedBy="user")
     */
    private $gcmTokens;


    /**
     * @var integer
     *
     * @ORM\Column(name="carrier_count", type="integer", options={"default"=0})
     */
    private $carrierCount;

    /**
     * @var double
     *
     * @ORM\Column(name="longitude", type="decimal", precision=12, scale=8, nullable=true)
     * @Expose
     */
    private $longitude;

    /**
     * @var double
     *
     * @ORM\Column(name="latitude", type="decimal", precision=12, scale=8, nullable=true)
     * @Expose
     */
    private $latitude;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Place", mappedBy="user")
     */
    private $places;

    public function __construct()
    {
        parent::__construct();

        $this->parcels = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->bids = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->places = new ArrayCollection();

        $this->senderRating = 0;
        $this->senderRatingAmount= 0;
        $this->senderRatingCount = 0;
        $this->carrierRating = 0;
        $this->carrierRatingAmount = 0;
        $this->carrierRatingCount = 0;

        $this->senderCount = 0;
        $this->carrierCount = 0;

        $this->balance = 0;
        $this->holdAmount = 0;

        $this->type = User::TYPE_INDIVIDUAL;
        $this->profileType = self::PROFILE_TYPE_CARRIER_AND_SENDER;

        $this->emailVerified = false;

        $this->senderRating = 0;
        $this->senderRatingAmount = 0;
        $this->senderRatingCount = 0;

        $this->carrierRating = 0;
        $this->carrierRatingAmount = 0;
        $this->carrierRatingCount = 0;

        $this->smsMask =   0b1111111111111111111111111111;
        $this->emailMask = 0b1111111111111111111111111111;
        $this->gcmMask =   0b1111111111111111111111111111;

        $this->imgPath = 'profile/'.rand(1,39).'.jpg';
    }

    public function hold($amount)
    {
        if ($amount > $this->balance) {
            throw new InsufficientUserBalance("Недостаточно средств на счете пользователя.");
        }
        $this->balance -= $amount;
        $this->holdAmount += $amount;
    }

    public function release($amount)
    {
        if ($amount > $this->holdAmount) {
            // такой ситуации быть не должно
            throw new InsufficientFrozenAmount("Нет средств для разморозки.");
        }
        $this->holdAmount -= $amount;
        $this->balance += $amount;
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
    }

    public function decreaseHoldBalance ($amount)
    {
        if ($amount > $this->holdAmount) {
            throw new InsufficientFrozenAmount("Нет средств для разморозки.");
        }
        $this->holdAmount -= $amount;
    }

    public function getEmailXXX()
    {
        return preg_replace('/(.*)@(.*)/', 'xxxxxx@${2}', $this->getEmailCanonical());
    }

    public function getMobileXXX()
    {
        return preg_replace('/(\d{3})\d{3}(\d{4})/', '+7 (${1}) xxx${2}', $this->getUsername());
    }

    public function getMobilePhone()
    {
        return preg_replace('/(\d{3})(\d{7})/', '+7 (${1}) ${2}', $this->getUsername());
    }

    /**
     * Display name for user
     */
    public function getName()
    {
        $name = '';
        if ($this->isIndividual()) {
            if (empty($this->patronymic)) {
                $name = $this->lastName.' '.$this->firstName;
            } else {
                $name = $this->firstName.' '.$this->patronymic;
            }
        } elseif ($this->isBusiness()) {
            $name .= $this->companyName;
        }

        // important! because  " " can be inside $name
        $name = trim($name);

        if (empty($name)) {
            $name = $this->getMobileXXX();
        }

        return $name;
    }

    public function getFio()
    {
        $name = $this->lastName.' '.$this->firstName;
        if ($this->patronymic) {
            $name .= ' '.$this->patronymic;
        }
        $name = trim($name);

        return $name;
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
     * Add parcels
     *
     * @param Shipping $parcels
     * @return User
     */
    public function addParcel(Shipping $parcels)
    {
        $this->parcels[] = $parcels;

        return $this;
    }

    /**
     * Remove parcels
     *
     * @param Shipping $parcels
     */
    public function removeParcel(Shipping $parcels)
    {
        $this->parcels->removeElement($parcels);
    }

    /**
     * Get parcels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParcels()
    {
        return $this->parcels;
    }

    /**
     * Add shipments
     *
     * @param Shipping $shipments
     * @return User
     */
    public function addShipment(Shipping $shipments)
    {
        $this->shipments[] = $shipments;

        return $this;
    }

    /**
     * Remove shipments
     *
     * @param Shipping $shipments
     */
    public function removeShipment(Shipping $shipments)
    {
        $this->shipments->removeElement($shipments);
    }

    /**
     * Get shipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * Add bids
     *
     * @param Bid $bids
     * @return User
     */
    public function addBid(Bid $bids)
    {
        $this->bids[] = $bids;

        return $this;
    }

    /**
     * Remove bids
     *
     * @param Bid $bids
     */
    public function removeBid(Bid $bids)
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
     * Add payments
     *
     * @param Payment $payments
     * @return User
     */
    public function addPayment(Payment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param Payment $payments
     */
    public function removePayment(Payment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     * @return User
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Set balance
     *
     * @param integer $balance
     * @return User
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @return integer
     */
    public function getBalance()
    {
        return $this->balance;
    }


    /**
     * Set companyName
     *
     * @param string $companyName
     * @return User
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }


    /**
     * Set type
     *
     * @param string $type
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isIndividual()
    {
        return $this->type == User::TYPE_INDIVIDUAL;
    }

    /**
     * @return bool
     */
    public function isBusiness()
    {
        return $this->type == User::TYPE_BUSINESS;
    }

    /**
     * Set holdAmount
     *
     * @param integer $holdAmount
     * @return User
     */
    public function setHoldAmount($holdAmount)
    {
        $this->holdAmount = $holdAmount;

        return $this;
    }

    /**
     * Get holdAmount
     *
     * @return integer
     */
    public function getHoldAmount()
    {
        return $this->holdAmount;
    }

    /**
     * Set aboutMe
     *
     * @param string $aboutMe
     * @return User
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    /**
     * Get aboutMe
     *
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * Set imgPath
     *
     * @param string $imgPath
     * @return User
     */
    public function setImgPath($imgPath)
    {
        $this->imgIsChanged = (bool)$imgPath;
        if ($this->imgIsChanged) {
            $this->backImage = $this->imgPath;
            $this->imgPath = $imgPath;
        }
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

    private $backImage = '';

    public function getBackImage()
    {
        return $this->backImage;
    }

    public function revertImage()
    {
        $this->imgPath = $this->backImage;
        return $this;
    }

    private $imgIsChanged = false;

    public function imgIsChanged()
    {
        return $this->imgIsChanged;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set smsMask
     *
     * @param integer $smsMask
     * @return User
     */
    public function setSmsMask($smsMask)
    {
        $this->smsMask = $smsMask;

        return $this;
    }

    /**
     * Get smsMask
     *
     * @return integer
     */
    public function getSmsMask()
    {
        return $this->smsMask;
    }

    /**
     * Set gcmMask
     *
     * @param integer $gcmMask
     * @return User
     */
    public function setGcmMask($gcmMask)
    {
        $this->gcmMask = $gcmMask;

        return $this;
    }

    /**
     * Get gcmMask
     *
     * @return integer
     */
    public function getGcmMask()
    {
        return $this->gcmMask;
    }

    /**
     * Set emailMask
     *
     * @param integer $emailMask
     * @return User
     */
    public function setEmailMask($emailMask)
    {
        $this->emailMask = $emailMask;

        return $this;
    }

    /**
     * Get emailMask
     *
     * @return integer
     */
    public function getEmailMask()
    {
        return $this->emailMask;
    }


### Start Gcm section

    /**
     * @return bool
     */
    public function getGcmBidCreateNotification()
    {
        return $this->getGcmN(self::BIT_CREATE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setGcmBidCreateNotification($flag)
    {
        $this->setGcmN(self::BIT_CREATE_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmBidAcceptNotification()
    {
        return $this->getGcmN(self::BIT_ACCEPT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setGcmBidAcceptNotification($flag)
    {
        $this->setGcmN(self::BIT_ACCEPT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmBidAgreeNotification()
    {
        return $this->getGcmN(self::BIT_AGREE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setGcmBidAgreeNotification($flag)
    {
        $this->setGcmN(self::BIT_AGREE_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmBidRejectNotification()
    {
        return $this->getGcmN(self::BIT_REJECT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setGcmBidRejectNotification($flag)
    {
        $this->setGcmN(self::BIT_REJECT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmBidRecallNotification()
    {
        return $this->getGcmN(self::BIT_RECALL_BID);
    }

    /**
     * @param bool $flag
     */
    public function setGcmBidRecallNotification($flag)
    {
        $this->setGcmN(self::BIT_RECALL_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingDeliverNotification()
    {
        return $this->getGcmN(self::BIT_DELIVER_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingDeliverNotification($flag)
    {
        $this->setGcmN(self::BIT_DELIVER_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingPayNotification()
    {
        return $this->getGcmN(self::BIT_PAY_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingPayNotification($flag)
    {
        $this->setGcmN(self::BIT_PAY_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingCompleteNotification()
    {
        return $this->getGcmN(self::BIT_COMPLETE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingCompleteNotification($flag)
    {
        $this->setGcmN(self::BIT_COMPLETE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingRefuseNotification()
    {
        return $this->getGcmN(self::BIT_REFUSE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingRefuseNotification($flag)
    {
        $this->setGcmN(self::BIT_REFUSE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingDisputeNotification()
    {
        return $this->getGcmN(self::BIT_DISPUTE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingDisputeNotification($flag)
    {
        $this->setGcmN(self::BIT_DISPUTE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingDebateNotification()
    {
        return $this->getGcmN(self::BIT_DEBATE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingDebateNotification($flag)
    {
        $this->setGcmN(self::BIT_DEBATE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingMessageNotification()
    {
        return $this->getGcmN(self::BIT_MESSAGE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingMessageNotification($flag)
    {
        $this->setGcmN(self::BIT_MESSAGE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getGcmShippingCreateNotification()
    {
        return $this->getGcmN(self::BIT_CREATE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setGcmShippingCreateNotification($flag)
    {
        $this->setGcmN(self::BIT_CREATE_SHIPPING, $flag);
    }

### End Gcm section

### Start Sms section

    /**
     * @return bool
     */
    public function getSmsBidCreateNotification()
    {
        return $this->getSmsN(self::BIT_CREATE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setSmsBidCreateNotification($flag)
    {
        $this->setSmsN(self::BIT_CREATE_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsBidAcceptNotification()
    {
        return $this->getSmsN(self::BIT_ACCEPT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setSmsBidAcceptNotification($flag)
    {
        $this->setSmsN(self::BIT_ACCEPT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsBidAgreeNotification()
    {
        return $this->getSmsN(self::BIT_AGREE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setSmsBidAgreeNotification($flag)
    {
        $this->setSmsN(self::BIT_AGREE_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsBidRejectNotification()
    {
        return $this->getSmsN(self::BIT_REJECT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setSmsBidRejectNotification($flag)
    {
        $this->setSmsN(self::BIT_REJECT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsBidRecallNotification()
    {
        return $this->getSmsN(self::BIT_RECALL_BID);
    }

    /**
     * @param bool $flag
     */
    public function setSmsBidRecallNotification($flag)
    {
        $this->setSmsN(self::BIT_RECALL_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingDeliverNotification()
    {
        return $this->getSmsN(self::BIT_DELIVER_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingDeliverNotification($flag)
    {
        $this->setSmsN(self::BIT_DELIVER_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingPayNotification()
    {
        return $this->getSmsN(self::BIT_PAY_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingPayNotification($flag)
    {
        $this->setSmsN(self::BIT_PAY_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingCompleteNotification()
    {
        return $this->getSmsN(self::BIT_COMPLETE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingCompleteNotification($flag)
    {
        $this->setSmsN(self::BIT_COMPLETE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingRefuseNotification()
    {
        return $this->getSmsN(self::BIT_REFUSE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingRefuseNotification($flag)
    {
        $this->setSmsN(self::BIT_REFUSE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingDisputeNotification()
    {
        return $this->getSmsN(self::BIT_DISPUTE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingDisputeNotification($flag)
    {
        $this->setSmsN(self::BIT_DISPUTE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingDebateNotification()
    {
        return $this->getSmsN(self::BIT_DEBATE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setSmsShippingDebateNotification($flag)
    {
        $this->setSmsN(self::BIT_DEBATE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingMessageNotification()
    {
        return $this->getSmsN(self::BIT_MESSAGE_SHIPPING);
    }
    /**
     * @param bool $flag
     */
    public function setSmsShippingMessageNotification($flag)
    {
        $this->setSmsN(self::BIT_MESSAGE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getSmsShippingCreateNotification()
    {
        return $this->getSmsN(self::BIT_CREATE_SHIPPING);
    }
    /**
     * @param bool $flag
     */
    public function setSmsShippingCreateNotification($flag)
    {
        $this->setSmsN(self::BIT_CREATE_SHIPPING, $flag);
    }

    // EMail section

    /**
     * @return bool
     */
    public function getEmailBidCreateNotification()
    {
        return $this->getEmailN(self::BIT_CREATE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setEmailBidCreateNotification($flag)
    {
        $this->setEmailN(self::BIT_CREATE_BID, $flag);
    }

    /**
    * @return bool
     */
    public function getEmailBidAcceptNotification()
    {
        return $this->getEmailN(self::BIT_ACCEPT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setEmailBidAcceptNotification($flag)
    {
        $this->setEmailN(self::BIT_ACCEPT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailBidAgreeNotification()
    {
        return $this->getEmailN(self::BIT_AGREE_BID);
    }

    /**
     * @param bool $flag
     */
    public function setEmailBidAgreeNotification($flag)
    {
        $this->setEmailN(self::BIT_AGREE_BID, $flag);
    }


    /**
     * @return bool
     */
    public function getEmailShippingDeliverNotification()
    {
        return $this->getEmailN(self::BIT_DELIVER_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingDeliverNotification($flag)
    {
        $this->setEmailN(self::BIT_DELIVER_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingPayNotification()
    {
        return $this->getEmailN(self::BIT_PAY_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingPayNotification($flag)
    {
        $this->setEmailN(self::BIT_PAY_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingCompleteNotification()
    {
        return $this->getEmailN(self::BIT_COMPLETE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingCompleteNotification($flag)
    {
        $this->setEmailN(self::BIT_COMPLETE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingRefuseNotification()
    {
        return $this->getEmailN(self::BIT_REFUSE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingRefuseNotification($flag)
    {
        $this->setEmailN(self::BIT_REFUSE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingDisputeNotification()
    {
        return $this->getEmailN(self::BIT_DISPUTE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingDisputeNotification($flag)
    {
        $this->setEmailN(self::BIT_DISPUTE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailBidRejectNotification()
    {
        return $this->getEmailN(self::BIT_REJECT_BID);
    }

    /**
     * @param bool $flag
     */
    public function setEmailBidRejectNotification($flag)
    {
        $this->setEmailN(self::BIT_REJECT_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailBidRecallNotification()
    {
        return $this->getEmailN(self::BIT_RECALL_BID);
    }

    /**
     * @param bool $flag
     */
    public function setEmailBidRecallNotification($flag)
    {
        $this->setEmailN(self::BIT_RECALL_BID, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingDebateNotification()
    {
        return $this->getEmailN(self::BIT_DEBATE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingDebateNotification($flag)
    {
        $this->setEmailN(self::BIT_DEBATE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingMessageNotification()
    {
        return $this->getEmailN(self::BIT_MESSAGE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingMessageNotification($flag)
    {
        $this->setEmailN(self::BIT_MESSAGE_SHIPPING, $flag);
    }

    /**
     * @return bool
     */
    public function getEmailShippingCreateNotification()
    {
        return $this->getEmailN(self::BIT_CREATE_SHIPPING);
    }

    /**
     * @param bool $flag
     */
    public function setEmailShippingCreateNotification($flag)
    {
        $this->setEmailN(self::BIT_CREATE_SHIPPING, $flag);
    }

    // End notifications

    public function getSmsN($n) {
        return ($this->smsMask & (1 << $n)) != 0;
    }

    private function setSmsN($n, $new) {
        $this->smsMask = ($this->smsMask & ~(1 << $n)) | ($new << $n);
    }

    public function getGcmN($n) {
        return ($this->gcmMask & (1 << $n)) != 0;
    }

    private function setGcmN($n, $new) {
        $this->gcmMask = ($this->gcmMask & ~(1 << $n)) | ($new << $n);
    }

    public function getEmailN($n) {
        return ($this->emailMask & (1 << $n)) != 0;
    }

    private function setEmailN($n, $new=true) {
        $this->emailMask = ($this->emailMask & ~(1 << $n)) | ($new << $n);
    }

    /**
     * Set emailVerified
     *
     * @param boolean $emailVerified
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Get emailVerified
     *
     * @return boolean 
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Set senderRating
     *
     * @param float $senderRating
     * @return User
     */
    public function setSenderRating($senderRating)
    {
        $this->senderRating = $senderRating;

        return $this;
    }

    /**
     * Get senderRating
     *
     * @return float
     */
    public function getSenderRating()
    {
        return $this->senderRating;
    }

    /**
     * Set senderRatingCount
     *
     * @param integer $senderRatingCount
     * @return User
     */
    public function setSenderRatingCount($senderRatingCount)
    {
        $this->senderRatingCount = $senderRatingCount;

        return $this;
    }

    /**
     * Get senderRatingCount
     *
     * @return integer 
     */
    public function getSenderRatingCount()
    {
        return $this->senderRatingCount;
    }

    /**
     * Set carrierRating
     *
     * @param float $carrierRating
     * @return User
     */
    public function setCarrierRating($carrierRating)
    {
        $this->carrierRating = $carrierRating;

        return $this;
    }

    /**
     * Get carrierRating
     *
     * @return float
     */
    public function getCarrierRating()
    {
        return $this->carrierRating;
    }

    /**
     * Set carrierRatingCount
     *
     * @param integer $carrierRatingCount
     * @return User
     */
    public function setCarrierRatingCount($carrierRatingCount)
    {
        $this->carrierRatingCount = $carrierRatingCount;
        return $this;
    }

    /**
     * Get carrierRatingCount
     *
     * @return integer 
     */
    public function getCarrierRatingCount()
    {
        return $this->carrierRatingCount;
    }

    /**
     * Set senderRatingAmount
     *
     * @param integer $senderRatingAmount
     * @return User
     */
    public function setSenderRatingAmount($senderRatingAmount)
    {
        $this->senderRatingAmount = $senderRatingAmount;

        return $this;
    }

    /**
     * Get senderRatingAmount
     *
     * @return integer 
     */
    public function getSenderRatingAmount()
    {
        return $this->senderRatingAmount;
    }

    /**
     * Set carrierRatingAmount
     *
     * @param integer $carrierRatingAmount
     * @return User
     */
    public function setCarrierRatingAmount($carrierRatingAmount)
    {
        $this->carrierRatingAmount = $carrierRatingAmount;

        return $this;
    }

    /**
     * Get carrierRatingAmount
     *
     * @return integer 
     */
    public function getCarrierRatingAmount()
    {
        return $this->carrierRatingAmount;
    }

    /**
     * Set recoverPassword
     *
     * @param string $recoverPassword
     * @return User
     */
    public function setRecoverPassword($recoverPassword)
    {
        $this->recoverPassword = $recoverPassword;

        return $this;
    }

    /**
     * Get recoverPassword
     *
     * @return string 
     */
    public function getRecoverPassword()
    {
        return $this->recoverPassword;
    }

    /**
     * Set recoverSalt
     *
     * @param string $recoverSalt
     * @return User
     */
    public function setRecoverSalt($recoverSalt)
    {
        $this->recoverSalt = $recoverSalt;

        return $this;
    }

    /**
     * Get recoverSalt
     *
     * @return string 
     */
    public function getRecoverSalt()
    {
        return $this->recoverSalt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
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
     * Set senderCount
     *
     * @param integer $senderCount
     * @return User
     */
    public function setSenderCount($senderCount)
    {
        $this->senderCount = $senderCount;

        return $this;
    }

    /**
     * Get senderCount
     *
     * @return integer 
     */
    public function getSenderCount()
    {
        return $this->senderCount;
    }

    /**
     * Set carrierCount
     *
     * @param integer $carrierCount
     * @return User
     */
    public function setCarrierCount($carrierCount)
    {
        $this->carrierCount = $carrierCount;

        return $this;
    }

    /**
     * Get carrierCount
     *
     * @return integer 
     */
    public function getCarrierCount()
    {
        return $this->carrierCount;
    }

    public function incrementCarrierCount($count = 1)
    {
        $this->carrierCount += $count;

        return $this;
    }

    public function incrementSenderCount($count = 1)
    {
        $this->senderCount += $count;

        return $this;
    }

    public function isAdmin(){
        return $this->hasRole(self::ROLE_ADMIN) || $this->isSuperAdmin();
    }

    /**
     * Set profileType
     *
     * @param integer $profileType
     * @return User
     */
    public function setProfileType($profileType)
    {
        $this->profileType = $profileType;

        return $this;
    }

    /**
     * Get profileType
     *
     * @return integer 
     */
    public function getProfileType()
    {
        return $this->profileType;
    }

    public function isCarrier()
    {
        return $this->profileType == self::PROFILE_TYPE_CARRIER_AND_SENDER || $this->profileType == self::PROFILE_TYPE_CARRIER;
    }

    public function isSender()
    {
        return $this->profileType == self::PROFILE_TYPE_CARRIER_AND_SENDER || $this->profileType == self::PROFILE_TYPE_SENDER;
    }


    /**
     * Add gcmTokens
     *
     * @param GcmToken $gcmTokens
     * @return User
     */
    public function addGcmToken(GcmToken $gcmTokens)
    {
        $this->gcmTokens[] = $gcmTokens;

        return $this;
    }

    /**
     * Remove gcmTokens
     *
     * @param GcmToken $gcmTokens
     */
    public function removeGcmToken(GcmToken $gcmTokens)
    {
        $this->gcmTokens->removeElement($gcmTokens);
    }

    /**
     * Get gcmTokens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGcmTokens()
    {
        return $this->gcmTokens;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get credentials_expire_at
     *
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }


    /**
     * Set longitude
     *
     * @param string $longitude
     * @return User
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return User
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
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
     * Add places
     *
     * @param \Binidini\CoreBundle\Entity\Place $places
     * @return User
     */
    public function addPlace(\Binidini\CoreBundle\Entity\Place $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param \Binidini\CoreBundle\Entity\Place $places
     */
    public function removePlace(\Binidini\CoreBundle\Entity\Place $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Add locations
     *
     * @param \Binidini\CoreBundle\Entity\Location $locations
     * @return User
     */
    public function addLocation(\Binidini\CoreBundle\Entity\Location $locations)
    {
        $this->locations[] = $locations;

        return $this;
    }

    /**
     * Remove locations
     *
     * @param \Binidini\CoreBundle\Entity\Location $locations
     */
    public function removeLocation(\Binidini\CoreBundle\Entity\Location $locations)
    {
        $this->locations->removeElement($locations);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }
}
