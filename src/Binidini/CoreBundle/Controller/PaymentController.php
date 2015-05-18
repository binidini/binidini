<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\Payment;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Binidini\CoreBundle\Service\AlfabankService;
use Binidini\CoreBundle\Entity\User;

class PaymentController extends ResourceController
{
    public function newAlfaAction(Request $request)
    {
        $amount = (int)$request->get('amount');
        $orderNumber = uniqid();
        $userId = $this->getUser()->getId();

        if ($amount >= 100) {

            $payment = $this->getAlfabank()->registerOrder($amount*100, $orderNumber, "Пополнение счета $userId" );

            if (isset($payment->errorCode)) {

                $request->getSession()->getFlashBag()->add(
                    'error',
                    $payment->errorMessage
                );
                return $this->redirectHandler->redirectToIndex();
            }

            $request->getSession()->set($payment->orderId, $orderNumber);

            return $this->redirectHandler->redirect($payment->formUrl);

        } else {
            $this->lashHelper->setFlash('error', 'registerOrderInAlfa');
            return $this->redirectHandler->redirectToIndex();
        }
    }

    public function depositAlfaSuccessAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();
        $orderId = $request->get('orderId');

        $res = $this->getAlfabank()->getOrderStatus($orderId);
        if (isset($res->OrderStatus) && $res->OrderStatus == 2 && $request->getSession()->get($orderId) === $res->OrderNumber) {
            $payment = new Payment();
            $payment
                ->setUser($user)
                ->setAmount($res->Amount / 100)
                ->setFlagCreditDebit(1)
                ->setHash($res->OrderNumber)
                ->setRef($orderId)
                ->setType(Payment::TYPE_DEPOSIT)
                ->setMethod(Payment::METHOD_ALFABANK_PAYMENT)
                ->setBalance($user->getBalance() + $user->getHoldAmount() + $res->Amount / 100)
                ->setDetails('Карта: ' . $res->Pan .', ' . substr($res->expiration, -2) . '/' . substr($res->expiration, 2,2) . ', ' . ucwords(strtolower($res->cardholderName)))
                ->setState(Payment::STATE_COMPLETED)
            ;

            $user->addBalance($res->Amount / 100);

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            $request->getSession()->remove($orderId);

            $this->flashHelper->setFlash('success', 'successPaymentInAlfa');
            return $this->redirectHandler->redirectToIndex();

        } else {
            $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
            return $this->redirectHandler->redirectToIndex();
        }
    }


    public function depositAlfaFailAction()
    {
        $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
        return $this->redirectHandler->redirectToIndex();
    }

    public function withdrawalAction(Request $request)
    {
        $amount = (int)$request->get('amount');
        $orderNumber = uniqid();
        /** @var User $user */
        $user = $this->getUser();
        if ($amount >= 100 && $amount < $user->getBalance()) {
            $payment = new Payment();
            $payment
                ->setUser($user)
                ->setAmount($amount)
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_WITHDRAWAL)
                ->setMethod(Payment::METHOD_ALFABANK_PAYMENT)
                ->setState(Payment::STATE_INIT)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();


            $this->flashHelper->setFlash('success', 'successWithdrawal');
            return $this->redirectHandler->redirectToIndex();
        } else {
            $this->flashHelper->setFlash('error', 'errorWithdrawal');
            return $this->redirectHandler->redirectToIndex();
        }
    }

    /**
     * @return AlfabankService
     */
    private function getAlfabank()
    {
        return $this->get('binidini.alfabank_service');

    }

}