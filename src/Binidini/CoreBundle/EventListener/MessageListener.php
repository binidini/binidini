<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Service\NotificationService;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Resource\Event\ResourceEvent;

/**
 * Listener responsible for messages
 */
class MessageListener
{
    private $em;
    private $notifier;

    public function __construct(EntityManager $em, NotificationService $notifier)
    {
        $this->em = $em;
        $this->notifier = $notifier;
    }


    /**
     * Setup Message "Recipient"
     */
    public function onMessagePostCreate(ResourceEvent $event)
    {
        /**
         * @var \Binidini\CoreBundle\Entity\Message $message
         */
        $message = $event->getSubject();

        if ($message->getUser()->getId() == $message->getShipping()->getSender()->getId()) {
            $message->setRecipient($message->getShipping()->getCarrier());
        } else {
            $message->setRecipient($message->getShipping()->getSender());
        }
        $this->em->flush($message);

        $this->notifier->notifyRecipient($message);

    }

}