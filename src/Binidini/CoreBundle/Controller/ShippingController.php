<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\Payment;
use Binidini\CoreBundle\Entity\Shipping;
use Binidini\CoreBundle\Exception\IncorrectDeliveryCode;
use Binidini\CoreBundle\Exception\TransitionCannotBeApplied;
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

    public function checkDeliveryCodeAction(Request $request) {
        /** @var $shipping Shipping */
        $shipping = $this->findOr404($request);

        if ($shipping->getCarrier() == $this->getUser()) {

            $shipping->setDeliveryCode($shipping->getDeliveryCode() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipping);
            $em->flush();

            $deliveryCode = (int)$request->get('delivery_code');
            if ($deliveryCode != (10000 + $shipping->getId()*$this->container->getParameter('delivery_code_prime')%10000)) {
                throw new IncorrectDeliveryCode('Код подтверждения доставки некорректен.');
            }

        }

        return $this->updateStateAction($request, 'deliver');
    }

    public function showAction(Request $request)
    {
        if ($this->config->isApiRequest()) {
            return parent::showAction($request);
        } else {
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
        foreach ($entries as $key => $entry) {
            if (!isset($usersInHistory[$entry->getUsername()])) {
                $usersInHistory[$entry->getUsername()] = $userManager->findUserBy(['id' => $entry->getUsername()]);;
            }
        }
        $data = [
            'entries' => $entries,
        ];
        if (!$this->config->isApiRequest()){
          $data['history_users'] = $usersInHistory;
        }
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('history_index.html'))
            ->setData(
               $data
            );
        return $this->handleView($view);

    }

    public function resolveAction(Request $request)
    {
        /** @var Shipping $shipping */
        $shipping = $this->findOr404($request);
        $graph = $this->stateMachineGraph;
        $stateMachine = $this->get('sm.factory')->get($shipping, $graph);
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
            $shipping->releaseSender();
        } elseif ($payment == 'carrier') {

            $guarantee = new Payment();
            $guarantee
                ->setUser($shipping->getCarrier())
                ->setAmount($shipping->getDeliveryPrice())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(1)
                ->setType(Payment::TYPE_GUARANTEE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getCarrier()->getBalance() + $shipping->getCarrier()->getHoldAmount() + $shipping->getDeliveryPrice())
                ->setDetails('Начисление гарантии по заказу №' . $shipping->getId())
            ;

            $guarantee2 = new Payment();
            $guarantee2
                ->setUser($shipping->getSender())
                ->setAmount($shipping->getDeliveryPrice())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_GUARANTEE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getSender()->getBalance() + $shipping->getSender()->getHoldAmount() - $shipping->getDeliveryPrice())
                ->setDetails('Списание гарантии по заказу №' . $shipping->getId())
            ;

            $shipping->payPayment();

            $em = $this->getDoctrine()->getManager();
            $em->persist($guarantee);
            $em->persist($guarantee2);
            $em->flush();
        }

        if ($insurance == 'carrier') {
            $shipping->releaseCarrier();
        } elseif ($insurance == 'sender') {
            $insurance = new Payment();
            $insurance
                ->setUser($shipping->getCarrier())
                ->setAmount($shipping->getInsurance())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_INSURANCE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getCarrier()->getBalance() + $shipping->getCarrier()->getHoldAmount() - $shipping->getInsurance())
                ->setDetails('Списание страховки по заказу №' . $shipping->getId())
                ->setPaymentAt($insurance->getPaymentAt()->modify("+1 second"))
            ;

            $insurance2 = new Payment();
            $insurance2
                ->setUser($shipping->getSender())
                ->setAmount($shipping->getInsurance())
                ->setRef($shipping->getId())
                ->setFlagCreditDebit(1)
                ->setType(Payment::TYPE_INSURANCE)
                ->setMethod(Payment::METHOD_INTERNAL_PAYMENT)
                ->setState(Payment::STATE_COMPLETED)
                ->setBalance($shipping->getSender()->getBalance() + $shipping->getSender()->getHoldAmount() + $shipping->getInsurance())
                ->setDetails('Начисление страховки по заказу №' . $shipping->getId())
                ->setPaymentAt($insurance2->getPaymentAt()->modify("+1 second"))
            ;

            $shipping->payInsurance();

            $em = $this->getDoctrine()->getManager();
            $em->persist($insurance);
            $em->persist($insurance2);
            $em->flush();

        }
        $this->domainManager->update($shipping);

        return $this->redirectHandler->redirectToReferer();
    }

}