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

namespace Binidini\CoreBundle\Worker;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class EmailWorker implements ConsumerInterface
{
    private $mailer;
    private $logger;

    public function __construct(\Swift_Mailer $mailer, LoggerInterface $loggerInterface)
    {
        $this->mailer = $mailer;
        $this->logger = $loggerInterface;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = unserialize($msg->body);
        $mime = isset($data['mime']) ? $data['mime'] : 'text/plain';
        $message = $this->mailer->createMessage()
            ->setSubject($data['subject'])
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setBody($data['body'], $mime);

        try {
            $result = $this->mailer->send($message);
            $this->mailer->getTransport()->stop();

            $this->logger->info("result: {$result}, from: " . implode(" ", $data['from']). ", to: {$data['to']}, subject: {$data['subject']}");
        } catch ( \Exception $ex) {
            $this->logger->error("error: {$ex->getMessage()}, from: " . implode(" ", $data['from']). ", to: {$data['to']}, subject: {$data['subject']}");
        }

    }
}
