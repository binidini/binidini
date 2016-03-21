<?php

namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Entity\Review;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Resource\Event\ResourceEvent;


/**
 * Listener for add counter to user when someone leave review
 */
class ReviewListener
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function onReviewPostCreate(ResourceEvent $event)
    {
        /** @var Review $review */
        $review = $event->getSubject();
        $userTo = $review->getUserTo();
        $shipping = $review->getShipping();
        $isSenderReview = $userTo->getId() == $shipping->getUser()->getId();
        $rating = $review->getRating();
        if ($rating > 0 && $rating <= 5) {
            if ($isSenderReview) {
                //оценка поставлена сендеру
                $newRatingAmount = $userTo->getSenderRatingAmount() + $rating;
                $newRatingCount = $userTo->getSenderRatingCount() + 1;
                $userTo->setSenderRatingAmount($newRatingAmount);
                $userTo->setSenderRatingCount($newRatingCount);
                $userTo->setSenderRating($newRatingAmount / $newRatingCount);
            } else {
                //оценка поставлена кариеру
                $newRatingAmount = $userTo->getCarrierRatingAmount() + $rating;
                $newRatingCount = $userTo->getCarrierRatingCount() + 1;
                $userTo->setCarrierRatingAmount($newRatingAmount);
                $userTo->setCarrierRatingCount($newRatingCount);
                $userTo->setCarrierRating($newRatingAmount / $newRatingCount);
            }
            $this->em->persist($userTo);
        }
        if ($isSenderReview) {
            $shipping->setHasCarrierReview(true);
        } else {
            $shipping->setHasUserReview(true);
        }
        $this->em->persist($shipping);
        $this->em->flush();

    }
}