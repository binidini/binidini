<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\Shipping;
use Binidini\CoreBundle\Form\Type\BidType;
use Binidini\CoreBundle\Form\Type\MessageType;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

class ShippingController extends ResourceController
{
    protected $stateMachineGraph = Shipping::GRAPH;

    public function showAction(Request $request)
    {
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);
        /** @var $repository LogEntryRepository */
        $repository = $this->getDoctrine()->getManager()->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $entries = $repository->getLogEntries($shipping);
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData(
                [
                    'entries' => $entries,
                    'shipping' => $shipping,
                    'bid_form' => $this->createForm(new BidType())->createView(),
                    'message_form' => $this->createForm(new MessageType())->createView()
                ]
            );
        return $this->handleView($view);

    }



}