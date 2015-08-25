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
use Binidini\CoreBundle\Exception\InsufficientUserBalance;
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

            $paymentOrder = $this->getAlfabank()->registerOrder($amount*100, $hash, "Пополнение счета {$user->getId()}");

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

            if ($this->config->isApiRequest()) {
                return new JsonResponse(['ErrorCode' => 101, 'ErrorMessage' => 'Сумма пополнения должна быть больше 100 рублей']);
            }

            $this->flashHelper->setFlash('error', 'registerOrderInAlfa');
            return $this->redirectHandler->redirectToIndex();
        }
    }

    public function depositAlfaSuccessAction(Request $request)
    {
        $orderId = $request->get('orderId');

        $payment = $this->getRepository()->findOneBy(['ref' => $orderId]);
        $user = $payment->getUser();

        $res = $this->getAlfabank()->getOrderStatus($orderId);

        if (isset($res->OrderStatus) && $res->OrderStatus == 2 && isset($payment) && $payment->getHash() === $res->OrderNumber && $payment->getState() === Payment::STATE_INIT) {
            $payment
                ->setAmount($res->Amount / 100)
                ->setBalance($user->getBalance() + $user->getHoldAmount() + $res->Amount / 100)
                ->setDetails('Карта: ' . $res->Pan .', ' . substr($res->expiration, -2) . '/' . substr($res->expiration, 2,2) . ', ' . ucwords(strtolower($res->cardholderName .'.')))
                ->setState(Payment::STATE_COMPLETED)
                ->setPaymentAt(new \DateTime())
            ;

            $user->addBalance($res->Amount / 100);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->flashHelper->setFlash('success', 'successPaymentInAlfa');

        } else {
            $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
        }

        return $this->redirectHandler->redirectToIndex();

    }


    public function depositAlfaFailAction(Request $request)
    {
        $orderId = $request->get('orderId');

        $payment = $this->getRepository()->findOneBy(['ref' => $orderId]);
        $user = $payment->getUser();

        $res = $this->getAlfabank()->getOrderStatus($orderId);

        if (isset($res) && isset($payment) && $payment->getHash() === $res->OrderNumber && $payment->getState() === Payment::STATE_INIT) {
            $payment
                ->setBalance($user->getBalance() + $user->getHoldAmount())
                ->setDetails($res->ErrorMessage .' ('. $res->ErrorCode . '). ' . 'Карта: ' . $res->Pan .', ' . substr($res->expiration, -2) . '/' . substr($res->expiration, 2,2) . ', ' . ucwords(strtolower($res->cardholderName)))
                ->setState(Payment::STATE_FAILED)
                ->setPaymentAt(new \DateTime())
            ;

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        $this->flashHelper->setFlash('error', 'failPaymentInAlfa');
        return $this->redirectHandler->redirectToIndex();
    }

    public function refundAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $orderId = $request->get('order_id');

        /** @var Payment $payment */
        $payment = $this->getRepository()->findOneBy(['user'=>$user->getId(), 'ref' => $orderId]);

        //проверка есть ли на счете деньги
        if ($payment->getAmount() > $user->getBalance()) {
            if ($this->config->isApiRequest()) {
                return new JsonResponse(['ErrorCode' => 102, 'ErrorMessage' => 'Недостаточно средст для вывода.']);
            }
            throw new InsufficientUserBalance("Недостаточно средст для вывода.");
        }

        $res = $this->getAlfabank()->getOrderStatus($orderId);

        if (isset($res) && isset($payment) && $payment->getHash() === $res->OrderNumber) {

            $refund = $this->getAlfabank()->refund($orderId, $payment->getAmount()*100);

            if ($refund->errorCode === "0") {
                $payment
                    ->setDetails($payment->getDetails() . ' Сделан возврат.')
                    ->setState(Payment::STATE_RETURNED)
                ;
                $user->addBalance(-$payment->getAmount());

                $withdrawal = new Payment();
                $withdrawal
                    ->setUser($user)
                    ->setAmount($payment->getAmount())
                    ->setRef($payment->getHash())
                    ->setFlagCreditDebit(-1)
                    ->setType(Payment::TYPE_WITHDRAWAL)
                    ->setMethod(Payment::METHOD_ALFABANK_PAYMENT)
                    ->setState(Payment::STATE_COMPLETED)
                    ->setBalance($user->getBalance() + $user->getHoldAmount())
                    ->setDetails('Возврат на карту по платежу №' . $payment->getId())
                ;

                $em = $this->getDoctrine()->getManager();
                $em->persist($withdrawal);
                $em->flush();

                $this->flashHelper->setFlash('success', 'successRefundInAlfa');
            } else {
                $this->flashHelper->setFlash('error', 'failRefundInAlfa');
            }

            if ($this->config->isApiRequest()) {
                return new JsonResponse(json_decode($refund));
            }
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