<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Binidini\CoreBundle\Service;

use Guzzle\Service\ClientInterface;
use Psr\Log\LoggerInterface;

class SendSmsService implements SendSmsInterface
{
    private $logger;
    private $smsc;
    private $login;
    private $password;
    private $sender;

    public function __construct(LoggerInterface $logger, ClientInterface $smsc, $login, $password, $sender)
    {
        $this->logger = $logger;
        $this->smsc = $smsc;
        $this->login = $login;
        $this->password = $password;
        $this->sender = $sender;
    }

    /*
     * @param string $phone
     * @param string $msg
     *
     * @return integer
     */
    public function send ($mobile, $msg)
    {
        $res = $this->smsc->get("sys/send.php?login={$this->login}&psw={$this->password}&phones=8{$mobile}&mes={$msg}&sender={$this->sender}&charset=utf-8&fmt=3")->send();

        if ($res->getStatusCode() == 200) {

            $result = json_decode($res->getBody(true));

            if (isset($result->error)) {
                $this->logger->error("Mobile:{$mobile}, msg:{$msg}, response:{$res->getBody(true)}");
                return $result->error_code;
            } else {
                $this->logger->info("Mobile:{$mobile}, msg:{$msg}, response:{$res->getBody(true)}");
                return 0;
            }
        }

        $this->logger->critical("Mobile:{$mobile}, msg:{$msg}, status_code:{$res->getStatusCode()}");

        return $res->getStatusCode();
    }
}