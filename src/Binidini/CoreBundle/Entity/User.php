<?php

namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Exception\AppException;
use Binidini\CoreBundle\Exception\InsufficientFrozenAmount;
use Binidini\CoreBundle\Exception\InsufficientUserBalance;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name = "user")
 *
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false)),
 * })
 *
 * @Gedmo\Uploadable(allowOverwrite=true, filenameGenerator="SHA1")
 *
 */
class User extends BaseUser
{
    const TYPE_INDIVIDUAL = 1;
    const TYPE_BUSINESS   = 2;

    const BIT_ACCEPT_BID = 0;
    const BIT_REJECT_BID = 1;
    const BIT_CANCEL_BID = 2;
    const BIT_RECALL_BID = 3;
    const BIT_AGREE_BID  = 4;

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

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=128, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=128, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="patronymic", type="string", length=128, nullable=true)
     */
    private $patronymic;

    /**
     * @var string
     * @ORM\Column(name="img_path", type="string", length=255, nullable=true)
     * @Gedmo\UploadableFileName
     */
    private $imgPath;

    /**
     * @ORM\OneToMany(targetEntity="Shipping", mappedBy="user")
     */
    private $parcels;

    /**
     * @ORM\OneToMany(targetEntity="Shipping", mappedBy="carrier")
     */
    private $shipments;

    /**
     * @var integer
     *
     * @ORM\Column(name="balance", type="integer", options={"default" = 0})
     */
    private $balance;

    /**
     * @var integer
     *
     * @ORM\Column(name="hold_amount", type="integer", options={"default" = 0})
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
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, options={"default" = 1})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="about_me", type="string", length=255, nullable=true)
     */
    private $aboutMe;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
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
     * @var bool
     *
     * @ORM\Column(name="email_verified", type="boolean", options={"default"=false})
     */
    private $emailVerified;

    /**
     * @var integer
     *
     * @ORM\Column(name="sender_rating", type="float", scale=2, options={"default"=0})
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


    public function __construct()
    {
        parent::__construct();

        $this->parcels = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->bids = new ArrayCollection();
        $this->payments = new ArrayCollection();

        $this->senderRating = 0;
        $this->senderRatingAmount= 0;
        $this->senderRatingCount = 0;
        $this->carrierRating = 0;
        $this->carrierRatingAmount = 0;
        $this->carrierRatingCount = 0;

        $this->balance = 0;
        $this->holdAmount = 0;

        $this->type = User::TYPE_INDIVIDUAL;

        $this->smsMask = 0b1111111111111111111111111;
        $this->emailMask = 0b1111111111111111111111111;
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

    public function getMobileMask()
    {
        return preg_replace('/(\d{3})\d{3}(\d{4})/', '+7 (${1}) xxx${2}', $this->getUsername());
    }

    public function getMobilePhone()
    {
        return sprintf('+7%1$s', $this->getUsername());
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
            $name = $this->getMobileMask();
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
     * @param \Binidini\CoreBundle\Entity\Shipping $parcels
     * @return User
     */
    public function addParcel(\Binidini\CoreBundle\Entity\Shipping $parcels)
    {
        $this->parcels[] = $parcels;

        return $this;
    }

    /**
     * Remove parcels
     *
     * @param \Binidini\CoreBundle\Entity\Shipping $parcels
     */
    public function removeParcel(\Binidini\CoreBundle\Entity\Shipping $parcels)
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
     * @param \Binidini\CoreBundle\Entity\Shipping $shipments
     * @return User
     */
    public function addShipment(\Binidini\CoreBundle\Entity\Shipping $shipments)
    {
        $this->shipments[] = $shipments;

        return $this;
    }

    /**
     * Remove shipments
     *
     * @param \Binidini\CoreBundle\Entity\Shipping $shipments
     */
    public function removeShipment(\Binidini\CoreBundle\Entity\Shipping $shipments)
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
     * @param \Binidini\CoreBundle\Entity\Bid $bids
     * @return User
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
     * Add payments
     *
     * @param \Binidini\CoreBundle\Entity\Payment $payments
     * @return User
     */
    public function addPayment(\Binidini\CoreBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param \Binidini\CoreBundle\Entity\Payment $payments
     */
    public function removePayment(\Binidini\CoreBundle\Entity\Payment $payments)
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
        if ($this->imgPath) {
            return $this->imgPath;
        } else {
            return 'default.png';
        }
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
     * @param bool $flag
     */
    public function setEmailBidRecallNotification($flag)
    {
        $this->setEmailN(self::BIT_RECALL_BID, $flag);
    }

    public function getSmsN($n) {
        return ($this->smsMask & (1 << $n)) != 0;
    }

    private function setSmsN($n, $new) {
        $this->smsMask = ($this->smsMask & ~(1 << $n)) | ($new << $n);
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
}
