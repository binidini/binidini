<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Worker;

use Binidini\CoreBundle\Service\SendSmsInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class SendRegistrationSmsWorker implements ConsumerInterface
{
    private $sender;

    public function __construct(SendSmsInterface $sender)
    {
        $this->sender = $sender;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = unserialize($msg->body);
        $this->sender->send($data['mobile'], "Ваш пароль: {$data['password']}");
    }
}