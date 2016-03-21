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

use Binidini\CoreBundle\Service\GcmService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class GcmWorker implements ConsumerInterface
{
    private $sender;

    public function __construct(GcmService $sender)
    {
        $this->sender = $sender;
    }

    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        $this->sender->send($body['data'], $body['registration_ids']);
    }
}