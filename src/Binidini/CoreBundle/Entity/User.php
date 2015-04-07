<?php

namespace Binidini\CoreBundle\Entity;

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
 * @Gedmo\Uploadable(allowOverwrite=true, filenameGenerator="SHA1")
 *
 */
class User extends BaseUser
{
    const TYPE_INDIVIDUAL = 1;
    const TYPE_BUSINESS = 2;
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
     * @ORM\Column(name="img_path", type="string", length=255, nullable=true)
     * @Gedmo\UploadableFileName
     */
    private $imgPath;

    /**
     * @var string
     *
     * @ORM\Column(name="patronymic", type="string", length=128, nullable=true)
     */
    private $patronymic;

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


    public function __construct()
    {
        parent::__construct();

        $this->parcels = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->bids = new ArrayCollection();
        $this->payments = new ArrayCollection();

        $this->balance = 0;
        $this->holdAmount = 0;

        $this->type = User::TYPE_INDIVIDUAL;
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
                $name = $this->lastName . ' ' . $this->firstName;
            } else {
                $name = $this->firstName . ' ' . $this->patronymic;
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
        $name = $this->lastName . ' ' . $this->firstName;
        if ($this->patronymic) {
            $name .= ' ' . $this->patronymic;
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
        return $this->imgPath;
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
}
