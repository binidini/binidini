<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\EventListener;

use Binidini\SearchBundle\Document\Shipment;
use Binidini\SearchBundle\Document\ShipmentItem;
use Doctrine\ODM\MongoDB\DocumentManager;
use Sylius\Component\Resource\Event\ResourceEvent;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Listener responsible to copy shipping object into mongodb
 */
class ShippingListener
{
    private $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }


    /**
     * Copy shipping into mongodb
     */
    public function onShippingPostCreate(ResourceEvent $event)
    {
        /**
         * @var \Binidini\CoreBundle\Entity\Shipping $shipping
         */
        $shipping = $event->getSubject();

        $shipment = new Shipment();
        $shipment
            ->setId($shipping->getId())
            ->setDeliveryPrice($shipping->getDeliveryPrice())
            ->setPaymentGuarantee($shipping->getPaymentGuarantee())
            ->setDeliveryAddress($shipping->getDeliveryAddress())
            ->setDeliveryDatetime($shipping->getDeliveryDatetime())
            ->setPickupAddress($shipping->getPickupAddress())
            ->setPickupDatetime($shipping->getPickupDatetime())
            ->setName($shipping->getName())
            ->setDescription($shipping->getDescription())
            ->setWeight($shipping->getWeight())
            ->setInsurance($shipping->getInsurance())
            ->setX($shipping->getX())
            ->setY($shipping->getY())
            ->setZ($shipping->getZ());

        $this->dm->persist($shipment);
        $this->dm->flush();
    }

}