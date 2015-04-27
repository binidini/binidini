<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\Shipping;
use Binidini\CoreBundle\Form\Type\BidType;
use Binidini\CoreBundle\Form\Type\MessageType;
use Binidini\CoreBundle\Form\Type\ReviewType;
use FOS\UserBundle\Doctrine\UserManager;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShippingController extends ResourceController
{
    protected $stateMachineGraph = Shipping::GRAPH;

    public function showAction(Request $request)
    {
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData(
                [
                    'shipping' => $shipping,
                    'bid_form' => $this->createForm(new BidType())->createView(),
                    'message_form' => $this->createForm(new MessageType())->createView(),
                    'review_form' => $this->createForm(new ReviewType())->createView(),
                ]
            );
        return $this->handleView($view);
    }

    public function historyIndexAction(Request $request){
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);
        /** @var $repository LogEntryRepository */
        $repository = $this->getDoctrine()->getManager()->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $entries = $repository->getLogEntries($shipping);
        $usersInHistory = [];
        /** @var $userManager UserManager */
        $userManager = $this->get('fos_user.user_manager');
        $currentUser = $user = $this->getUser();
        foreach ($entries as $key => $entry) {
            if (!isset($usersInHistory[$entry->getUsername()])) {
                if ($entry->getUsername() == $currentUser->getId()) {
                    $user = $currentUser;
                } else {
                    $user = $userManager->findUserBy(['id' => $currentUser->getId()]);
                }
                $usersInHistory[$entry->getUsername()] = $user;
            }
        }
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('history_index.html'))
            ->setData(
                [
                    'entries' => $entries,
                    'history_users' => $usersInHistory,
                ]
            );
        return $this->handleView($view);

    }

    public function resolveAction(Request $request)
    {
        /** @var Shipping $resource */
        $resource = $this->findOr404($request);
        $graph = $this->stateMachineGraph;
        $stateMachine = $this->get('sm.factory')->get($resource, $graph);
        $transition = 'resolve';
        if (!$stateMachine->can($transition)) {
            throw new NotFoundHttpException(
                sprintf(
                    'The requested transition %s cannot be applied on the given %s with graph %s.',
                    $transition,
                    $this->config->getResourceName(),
                    $this->stateMachineGraph
                )
            );
        }
        $stateMachine->apply($transition);

        $payment = $request->get('payment');
        $insurance = $request->get('insurance');

        if ($payment == 'sender') {
            $resource->releaseSender();
        } elseif ($payment == 'carrier') {
            $resource->payPayment();
        }

        if ($insurance == 'carrier') {
            $resource->releaseCarrier();
        } elseif ($insurance == 'sender') {
            $resource->payInsurance();
        }
        $this->domainManager->update($resource);

        return $this->redirectHandler->redirectToReferer();
    }
}