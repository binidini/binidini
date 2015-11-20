<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\Payment;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Binidini\CoreBundle\Entity\Promotion;
use Binidini\CoreBundle\Entity\User;


class PromotionController extends ResourceController
{
    public function checkAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $promoCode = $request->get('promo_code');

        /** @var Promotion $promotion */
        $promotion = $this->getRepository()->findOneBy(['code' => $promoCode]);

        $dt = new \DateTime();

        if ( isset($promotion) &&
             $promotion->getStartAt() <= $user->getCreatedAt() &&
             $promotion->getFinishAt() >= $user->getCreatedAt()
             && $promotion->getStartAt() <= $dt
             && $promotion->getFinishAt() >=$dt) {

            $payments = $user->getPayments();

            $codeIsUsed = false;
            foreach ($payments as $payment) {
                if ($payment->getRef() === $promoCode) {
                    $codeIsUsed = true;
                }
            }

            if ($codeIsUsed) {
                $this->flashHelper->setFlash('error', 'promoCodeIsNotValid');
            } else {
                $user->addBalance($promotion->getAmount());

                $deposit = new Payment();
                $deposit
                    ->setUser($user)
                    ->setAmount($promotion->getAmount())
                    ->setRef($promotion->getCode())
                    ->setFlagCreditDebit(1)
                    ->setType(Payment::TYPE_PROMOTION)
                    ->setMethod(Payment::METHOD_PROMO_CODE)
                    ->setState(Payment::STATE_COMPLETED)
                    ->setBalance($user->getBalance() + $user->getHoldAmount())
                    ->setDetails('Промо код:' . $promotion->getCode());

                $em = $this->getDoctrine()->getManager();
                $em->persist($deposit);
                $em->flush();

                $this->flashHelper->setFlash('info', 'promoCodeIsApplied');
            }

        } else {
            $this->flashHelper->setFlash('error', 'promoCodeIsNotValid');
        }

        return $this->redirectHandler->redirectToReferer();
    }

}