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
use Binidini\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use SM\Factory\Factory as StateMachineFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class BidLogicService
{
    protected $securityContext;
    protected $sls;
    protected $em;
    protected $smFactory;

    public function __construct(SecurityContextInterface $securityContext, ShippingLogicService $shippingLogicService, EntityManager $entityManager, StateMachineFactory $sm)
    {
        $this->securityContext = $securityContext;
        $this->sls = $shippingLogicService;
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

    public function agreeTransition(Bid $bid)
    {
        $this->checkCarrier($bid);

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

    public function notifyUser (Bid $bid, $transition)
    {
        $user = $bid->getUser();
        $this->notify($user, $transition);
    }

    public function notifySender (Bid $bid, $transition)
    {
        $sender = $bid->getSender();
        $this->notify($sender, $transition);
    }

    private function notify (User $user, $transition)
    {
        if ($user->getSmsN(constant('User::BIT_'.strtoupper($transition).'_BID'))) {

        }
        if ($user->getEmailN(constant('User::BIT_'.strtoupper($transition).'_BID'))) {

        }
    }

    private function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }
}