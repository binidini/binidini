<?php

namespace Binidini\CoreBundle\Service;

use Binidini\CoreBundle\Entity\Shipping;
use Binidini\SearchBundle\Document\Shipment;
use Doctrine\ODM\MongoDB\DocumentManager;

class ShippingLogicService
{
    protected $security;
    protected $dm;

    public function __construct(SecurityService $security, DocumentManager $documentManager)
    {
        $this->security = $security;
        $this->dm = $documentManager;
    }

    public function removeShipment(Shipping $shipping)
    {
        if ($shipment = $this->dm->find('\Binidini\SearchBundle\Document\Shipment', $shipping->getId())) {
            $this->dm->remove($shipment);
            $this->dm->flush($shipment);
        }
    }

    public function addShipment(Shipping $shipping)
    {
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

    public function beforeComplete(Shipping $shipping)
    {
        $this->security->checkCarrier($shipping);
        $shipping->release();
        $shipping->getCarrier()->incrementCarrierCount();
        $shipping->getSender()->incrementSenderCount();
    }

}