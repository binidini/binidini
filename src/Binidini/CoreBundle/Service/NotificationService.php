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


use Binidini\CoreBundle\Entity\Bid;
use Binidini\CoreBundle\Entity\Message;
use Binidini\CoreBundle\Entity\Shipping;
use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Model\SenderCarrierAwareInterface;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class NotificationService
{
    private $smsRabbitMqProducer;
    private $emailRabbitMqProducer;
    private $gcmRabbitMqProducer;
    private $twig;
    private $em;

    private $insiders;

    public function __construct(Producer $smsRabbitMqProducer, Producer $emailRabbitMqProducer, Producer $gcmRabbitMqProducer, \Twig_Environment $twig, EntityManager $em)
    {
        $this->smsRabbitMqProducer = $smsRabbitMqProducer;
        $this->emailRabbitMqProducer = $emailRabbitMqProducer;
        $this->gcmRabbitMqProducer = $gcmRabbitMqProducer;
        $this->twig = $twig;
        $this->em = $em;

        $this->insiders = [0, 2, 123792, 124290, 124292, 124343, 124344, 123642];
    }

    public function notifySender(SenderCarrierAwareInterface $resource, $event)
    {
        $user = $resource->getSender();
        if ($user->isSender()) {
            $this->notify($user, $event, $resource);
        }
    }

    public function notifyCarrier(SenderCarrierAwareInterface $resource, $event)
    {
        $user = $resource->getCarrier();
        if ($user->isCarrier()) {
            $this->notify($user, $event, $resource);
        }
    }

    public function notifyRecipient(Message $message)
    {
        $user = $message->getRecipient();
        if (!is_null($user)) {
            $this->notify($user, 'message_shipping', $message);
        }
    }

    public function notifyAboutNewShipping(Shipping $shipping) {

        if (is_null($shipping->getPickupLongitude()) or is_null($shipping->getPickupLatitude())) {
            return;
        }

        $uids = $this->em->getRepository('BinidiniCoreBundle:User')->findByCoordinates($shipping->getPickupLongitude(), $shipping->getPickupLatitude());
        foreach ($uids as $uid) {

            //инсайдерам не надо
            if (array_search($uid['id'], $this->insiders)) {
                return;
            }

            $user = $this->em->getRepository('BinidiniCoreBundle:User')->find($uid['id']);
            if ($shipping->getSender()->getId() != $uid['id'] && $user->isCarrier()) {
                $this->notify($user, 'create_shipping', $shipping);
            }
        }

    }

    public function notifyInsidersAboutNewDffShipping(Shipping $shipping) {

        if ($shipping->getSender()->getId() != 124281) {
            //если не dff возврат
            return;
        }

        foreach ($this->insiders as $uid) {
            $user = $this->em->getRepository('BinidiniCoreBundle:User')->find($uid);

            $this->notify($user, 'create_shipping', $shipping);
        }

    }

    private function notify(User $user, $event, $resource)
    {
        $bitN = constant('Binidini\CoreBundle\Entity\User::BIT_' . strtoupper($event));

        if ($user->getSmsN($bitN)) {
            $sms = $this->twig->render('BinidiniWebBundle::Template/Sms/' . $event . '.txt.twig', ['resource' => $resource]);
            $msg = ['mobile' => $user->getUsername(), 'sms' => $sms];
            $this->smsRabbitMqProducer->publish(serialize($msg));
        }

        if ($user->getEmailVerified() && $user->getEmailN($bitN)) {

            $emailBody = $this->twig->render('BinidiniWebBundle::Template/Email/' . $event . '.txt.twig', ['resource' => $resource]);
            $subject = $this->twig->render('BinidiniWebBundle::Template/Sms/' . $event . '.txt.twig', ['resource' => $resource]);
            $from = ['info@tytymyty.ru' => 'Титимити'];


            $msg = ['to' => $user->getEmailCanonical(), 'from' => $from, 'subject' => $subject, 'body' => $emailBody];
            $this->emailRabbitMqProducer->publish(serialize($msg));
        }

        if (!($user->getGcmTokens()->isEmpty()) && $user->getGcmN($bitN)) {
            foreach ($user->getGcmTokens() as $gcmToken) {
                $ids[] = ['token' => $gcmToken->getToken(), 'type' => $gcmToken->getType()];
            }
            if ($resource instanceof Shipping) {
                $shippingId = $resource->getId();
            } elseif ($resource instanceof Bid or $resource instanceof Message) {
                $shippingId = $resource->getShipping()->getId();
            } else {
                $shippingId = 0;
            }

            $data = [
                'event' => $event,
                'shipping_id' => $shippingId,
                'message' => $this->twig->render('BinidiniWebBundle::Template/Gcm/' . $event . '.txt.twig',
                    ['resource' => $resource])
            ];
            $msg = ['registration_ids' => $ids, 'data' => $data, ];
            $this->gcmRabbitMqProducer->publish(serialize($msg));
        }
    }

    public function sendConfirmationEmail(User $user)
    {
        $emailBody = $this->twig->render(
            'BinidiniWebBundle:Template:Email/confirmation.html.twig',
            ['user' => $user]
        );
        $subject = 'Титимити: подтверждение почты';
        $from = ['info@tytymyty.ru' => 'Титимити'];
        $msg = [
            'to' => $user->getEmailCanonical(),
            'from' => $from,
            'subject' => $subject,
            'body' => $emailBody,
            'mime' => 'text/html'
        ];
        $this->emailRabbitMqProducer->publish(serialize($msg));
    }

    public function setRecoverPasswordSms($phone, $password)
    {
        $sms = $this->twig->render(
            'BinidiniWebBundle:Template:Sms/recover_password.txt.twig',
            ['password' => $password]
        );
        $msg = array('mobile' => $phone, 'sms' => $sms);
        $this->smsRabbitMqProducer->publish(serialize($msg));
    }

}