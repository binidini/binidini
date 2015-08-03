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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Binidini\CoreBundle\Service\AlfabankService;
use Binidini\CoreBundle\Entity\User;

class PaymentController extends ResourceController
{
    public function newAlfaAction(Request $request)
    {
        $amount = (int)$request->get('amount');
        $hash = uniqid();
        $user = $this->getUser();

        if ($amount >= 100) {

            $paymentOrder = $this->getAlfabank()->registerOrder($amount*100, $hash, "Пополнение счета {$user->getId()}" );

            if (isset($paymentOrder->errorCode)) {

                $request->getSession()->getFlashBag()->add(
                    'error',
                    $paymentOrder->errorMessage
                );
                return $this->redirectHandler->redirectToIndex();
            }

            $payment = new Payment();
            $payment
                ->setUser($this->getUser())
                ->setAmount($amount)
                ->setFlagCreditDebit(1)
                ->setHash($hash)
                ->setRef($paymentOrder->orderId)
                ->setType(Payment::TYPE_DEPOSIT)
                ->setMethod(Payment::METHOD_ALFABANK_PAYMENT)
                ->setBalance($user->getBalance() + $user->getHoldAmount())
                ->setDetails('Платеж инициирован.')
                ->setState(Payment::STATE_INIT)
            ;
            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();

            if ($this->config->isApiRequest()) {
                return new JsonResponse(['payment_id' => $payment->getId(), 'form_url' => $paymentOrder->formUrl]);
            }

            return $this->redirectHandler->redirect($paymentOrder->formUrl);

        } else {
            $this->flashHelper->setFlash('error', 'registerOrderInAlfa');
            return $this->redirectHandler->redirectToIndex();
        }
    }

    public function depositAlfaSuccessAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $orderId = $request->get('orderId');


        $payment = $this->getRepository()->findOneBy(['user'=>$user->getId(), 'ref' => $orderId]);

        $res = $this->getAlfabank()->getOrderStatus($orderId);

        if (isset($res->OrderStatus) && $res->OrderStatus == 2 && isset($payment) && $payment->getHash() === $res->OrderNumber) {
            $payment
                ->setAmount($res->Amount / 100)
                ->setBalance($user->getBalance() + $user->getHoldAmount() + $res->Amount / 100)
                ->setDetails('Карта: ' . $res->Pan .', ' . substr($res->expiration, -2) . '/' . substr($res->expiration, 2,2) . ', ' . ucwords(strtolower($res->cardholderName)))
                ->setState(Payment::STATE_COMPLETED)
            ;

            $user->addBalance($res->Amount / 100);

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();
            $this->flashHelper->setFlash('success', 'successPaymentInAlfa');


            return $this->redirectHandler->redirectToIndex();

        } else {
            $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
            return $this->redirectHandler->redirectToIndex();
        }
    }


    public function depositAlfaFailAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $orderId = $request->get('orderId');


        $payment = $this->getRepository()->findOneBy(['user'=>$user->getId(), 'ref' => $orderId]);
        $res = $this->getAlfabank()->getOrderStatus($orderId);

        if (isset($res) && isset($payment) && $payment->getHash() === $res->OrderNumber) {
            $payment
                ->setBalance($user->getBalance() + $user->getHoldAmount())
                ->setDetails($res->ErrorMessage .' ('. $res->ErrorCode . '). ' . 'Карта: ' . $res->Pan .', ' . substr($res->expiration, -2) . '/' . substr($res->expiration, 2,2) . ', ' . ucwords(strtolower($res->cardholderName)))
                ->setState(Payment::STATE_FAILED)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);
            $em->flush();
        }

        $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
        return $this->redirectHandler->redirectToIndex();
    }

    public function refund(Request $request)
    {

    }

    public function withdrawalAction(Request $request)
    {
        $amount = (int)$request->get('amount');
        $orderNumber = uniqid();
        /** @var User $user */
        $user = $this->getUser();
        if ($amount >= 100 && $amount <= $user->getBalance()) {
            $payment = new Payment();
            $payment
                ->setUser($user)
                ->setAmount($amount)
                ->setFlagCreditDebit(-1)
                ->setType(Payment::TYPE_WITHDRAWAL)
                ->setMethod(Payment::METHOD_ALFABANK_PAYMENT)
                ->setState(Payment::STATE_INIT)
                ->setBalance($user->getBalance() + $user->getHoldAmount())
                ->setDetails('Запрос обрабатывается')
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