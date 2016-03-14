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
use Doctrine\ORM\EntityManager;
use SM\Factory\Factory as StateMachineFactory;
use Symfony\Component\Security\Core\SecurityContextInterface;

class BidLogicService
{
    protected $security;
    protected $sls;
    protected $em;
    protected $smFactory;

    public function __construct(SecurityService $security, ShippingLogicService $shippingLogicService, EntityManager $entityManager, StateMachineFactory $sm)
    {
        $this->security = $security;
        $this->sls = $shippingLogicService;
        $this->em = $entityManager;
        $this->smFactory = $sm;
    }

     public function agreeTransition(Bid $bid)
    {
        $this->security->checkCarrier($bid);

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

        $this->sls->removeShipment($shipping);

        foreach ($shipping->getBids() as $b)
        {
            if ( $b->isNew() || $b->isAccepted() ) {
                $b->setState(Bid::STATE_AUTO_REJECTED);
                $this->em->flush($b);
            }
        }
    }

}