<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Service;

use Binidini\CoreBundle\Entity\Shipping;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ShippingLogicService
{
    protected $securityContext;
    protected $dm;

    public function __construct(SecurityContextInterface $securityContext, DocumentManager $documentManager)
    {
        $this->securityContext = $securityContext;
        $this->dm = $documentManager;
    }

    public function checkSender(Shipping $shipping)
    {
        if ($shipping->getUser()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь отправителем. Данная операция запрещена.");
    }

    public function checkCarrier(Shipping $shipping)
    {
        if ( is_null($shipping->getCarrier()) or  $shipping->getCarrier()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь перевозчиком. Данная операция запрещена.");
    }

    public function removeShipment(Shipping $shipping)
    {
        if ($shipment = $this->dm->find('\Binidini\SearchBundle\Document\Shipment', $shipping->getId())) {
            $this->dm->remove($shipment);
            $this->dm->flush($shipment);
        }
    }

    public function checkResolver(Shipping $shipping)
    {
        throw new AccessDeniedHttpException("Данная операция не реализована.");
    }

    protected function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }
}