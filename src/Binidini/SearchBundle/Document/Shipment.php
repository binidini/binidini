<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\SearchBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document (collection = "shipment")
 */
class Shipment
{
    /**
     * @MongoDB\Id(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @MongoDB\Field(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @MongoDB\Field(name="description", type="string")
     */
    private $description;

    /**
     * @var integer
     *
     * @MongoDB\Field(name="delivery_price", type="int")
     */
    private $deliveryPrice;

    /**
     * @var integer
     *
     * @MongoDB\Field(name="guarantee_cost", type="int")
     */
    private $guaranteeCost;

    /**
     * @var string
     *
     * @MongoDB\Field(name="pickup_address", type="string")
     */
    private $pickupAddress;

    /**
     * @var string
     *
     * @MongoDB\Field(name="delivery_address", type="string")
     */
    private $deliveryAddress;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(name="pickup_datetime", type="date")
     */
    private $pickupDatetime;

    /**
     * @var \DateTime
     *
     * @MongoDB\Field(name="delivery_datetime", type="date")
     */
    private $deliveryDatetime;


    /**
     * @var integer
     *
     * @MongoDB\Field(name="weight", type="int")
     */
    private $weight;

    /**
     * @var integer
     *
     * @MongoDB\Field(name="x", type="int")
     */
    private $x;

    /**
     * @var integer
     *
     * @MongoDB\Field(name="y", type="int")
     */
    private $y;

    /**
     * @var integer
     *
     * @MongoDB\Field(name="z", type="int")
     */
    private $z;

    public function __construct()
    {
        $this->items = new ArrayCollection();

        $this->deliveryPrice = 0;
    }
    
    /**
     * Set pickupAddress
     *
     * @param string $pickupAddress
     * @return self
     */
    public function setPickupAddress($pickupAddress)
    {
        $this->pickupAddress = $pickupAddress;
        return $this;
    }

    /**
     * Get pickupAddress
     *
     * @return string $pickupAddress
     */
    public function getPickupAddress()
    {
        return $this->pickupAddress;
    }

    /**
     * Set deliveryAddress
     *
     * @param string $deliveryAddress
     * @return self
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return string $deliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set pickupDatetime
     *
     * @param date $pickupDatetime
     * @return self
     */
    public function setPickupDatetime($pickupDatetime)
    {
        $this->pickupDatetime = $pickupDatetime;
        return $this;
    }

    /**
     * Get pickupDatetime
     *
     * @return date $pickupDatetime
     */
    public function getPickupDatetime()
    {
        return $this->pickupDatetime;
    }

    /**
     * Set deliveryDatetime
     *
     * @param date $deliveryDatetime
     * @return self
     */
    public function setDeliveryDatetime($deliveryDatetime)
    {
        $this->deliveryDatetime = $deliveryDatetime;
        return $this;
    }

    /**
     * Get deliveryDatetime
     *
     * @return date $deliveryDatetime
     */
    public function getDeliveryDatetime()
    {
        return $this->deliveryDatetime;
    }

      /**
     * Set deliveryPrice
     *
     * @param integer $deliveryPrice
     * @return self
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;
        return $this;
    }

    /**
     * Get deliveryPrice
     *
     * @return integer $deliveryPrice
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }


    /**
     * Set id
     *
     * @param custom_id $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return custom_id $id
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set weight
     *
     * @param int $weight
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * Get weight
     *
     * @return int $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set x
     *
     * @param int $x
     * @return self
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * Get x
     *
     * @return int $x
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param int $y
     * @return self
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * Get y
     *
     * @return int $y
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set z
     *
     * @param int $z
     * @return self
     */
    public function setZ($z)
    {
        $this->z = $z;
        return $this;
    }

    /**
     * Get z
     *
     * @return int $z
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * Set guaranteeCost
     *
     * @param int $guaranteeCost
     * @return self
     */
    public function setGuaranteeCost($guaranteeCost)
    {
        $this->guaranteeCost = $guaranteeCost;
        return $this;
    }

    /**
     * Get guaranteeCost
     *
     * @return int $guaranteeCost
     */
    public function getGuaranteeCost()
    {
        return $this->guaranteeCost;
    }
}
