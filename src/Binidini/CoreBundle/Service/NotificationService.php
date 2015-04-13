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

namespace Binidini\CoreBundle\Service;


use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Model\SenderCarrierAwareInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class NotificationService
{
    private $smsRabbitMqProducer;
    private $emailRabbitMqProducer;
    private $twig;

    public function __construct(Producer $smsRabbitMqProducer, Producer $emailRabbitMqProducer, \Twig_Environment $twig)
    {
        $this->smsRabbitMqProducer = $smsRabbitMqProducer;
        $this->emailRabbitMqProducer = $emailRabbitMqProducer;
        $this->twig = $twig;
    }

    public function notifySender(SenderCarrierAwareInterface $resource, $event)
    {
        $user = $resource->getSender();
        $this->notify($user, $event, $resource);
    }

    public function notifyCarrier(SenderCarrierAwareInterface $resource, $event)
    {
        $user = $resource->getCarrier();
        $this->notify($user, $event, $resource);
    }

    private function notify (User $user, $event, $resource)
    {
        $bitN = constant('Binidini\CoreBundle\Entity\User::BIT_'.strtoupper($event));

        if ($user->getSmsN($bitN)) {
            $sms = $this->twig->render('BinidiniWebBundle::SmsTemplates/'.$event.'.html.twig', ['resource' => $resource]);
            $msg = array('mobile' => $user->getUsername(), 'sms' => $sms);
            $this->smsRabbitMqProducer->publish(serialize($msg));
        }

        if ($user->getEmailVerified() && $user->getEmailN($bitN))
        {
            $emailBody = $this->twig->render('BinidiniWebBundle::EmailTemplates/'.$event.'.html.twig', ['resource' => $resource]);
            $subject = 'Уведомление Титимити';

            $msg = array('to' => $user->getEmail(), 'from' =>'noreply@tytymyty.ru', 'subject' => $subject, 'body' => $emailBody);
            $this->emailRabbitMqProducer->publish(serialize($msg));
        }
    }
}