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

use Binidini\CoreBundle\Entity\Bid;
use Binidini\CoreBundle\Entity\Shipping;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class BidLogicService
{
    protected $securityContext;
    protected $dm;
    protected $em;
    protected $smFactory;

    public function __construct(SecurityContextInterface $securityContext, DocumentManager $documentManager, EntityManager $entityManager, $sm)
    {
        $this->securityContext = $securityContext;
        $this->dm = $documentManager;
        $this->em = $entityManager;
        $this->smFactory = $sm;
    }

    public function checkSender(Bid $bid)
    {
        if ($bid->getShipping()->getUser()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь отправителем. Данная операция запрещена.");
    }

    public function checkCarrier(Bid $bid)
    {
        if ( is_null($bid->getUser()) or  $bid->getUser()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь перевозчиком. Данная операция запрещена.");
    }

    public function acceptTransition(Bid $bid)
    {
        $this->checkSender($bid);

        $shipping = $bid->getShipping();

        $shippingSM = $this->smFactory->get($shipping, Shipping::GRAPH);

        if (!$shippingSM->can(Shipping::TRANSITION_ACCEPT)) {
            throw new NotFoundHttpException(sprintf(
                'The requested transition %s cannot be applied on the given %s with graph %s.',
                Shipping::TRANSITION_ACCEPT,
                'Shipping',
                Shipping::GRAPH
            ));
        }

        $shipping->setDeliveryPrice($bid->getPrice());
        $shipping->setCarrier($bid->getUser());
        $shipping->hold();
        $shippingSM->apply(Shipping::TRANSITION_ACCEPT);
        $this->em->flush($shipping);

        $shipment = $this->dm->find('\Binidini\SearchBundle\Document\Shipment', $bid->getShipping()->getId());
        $this->dm->remove($shipment);
        $this->dm->flush($shipment);

        foreach ($shipping->getBids() as $bid)
        {
            if ($bid->isNew()) {
                $bid->setState(Bid::STATE_REJECTED);
                $this->em->flush($bid);
            }
        }
    }

    protected function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }
}