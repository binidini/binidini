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

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use OldSound\RabbitMqBundle\RabbitMq\Producer ;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Listener responsible to setup password and email for new user
 * and send sms with password and create email
 */
class RegistrationListener implements EventSubscriberInterface
{
    private $rabbitMqProducer;
    private $pwd;

    public function __construct(Producer $rabbitMqProducer)
    {
        $this->rabbitMqProducer = $rabbitMqProducer;

    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted'
        );
    }

    /**
     * Setup email and password for new user
     */
    public function onRegistrationInitialize(UserEvent $event)
    {
        $phoneNumber = $event->getRequest()->request->get('fos_user_registration_form')['username'];
        $user = $event->getUser();
        $user->setEmail($phoneNumber . '@tytymyty.ru');
        $this->pwd = '' . rand(100000, 999999);
        $user->setPlainPassword($this->pwd);

   }

    /**
     * Sending sms with password and mailbox creation
     */
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $msg = array('mobile' => $user->getUsernameCanonical(), 'password' => $this->pwd);
        $this->rabbitMqProducer->publish(serialize($msg));

    }
}