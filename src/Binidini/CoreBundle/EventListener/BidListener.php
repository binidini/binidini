<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
use Sylius\Component\Resource\Event\ResourceEvent;

class BidListener
{
    private $ns;

    public function __construct(NotificationService $notificationService)
    {
        $this->ns = $notificationService;
    }

    public function onBidPostCreate(ResourceEvent $event)
    {
        $this->ns->notifySender($event->getSubject(), 'create_bid');
    }
}