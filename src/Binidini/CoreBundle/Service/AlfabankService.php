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

use Guzzle\Service\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class AlfabankService
{
    private $router;
    private $logger;
    private $alfabank;
    private $login;
    private $password;

    public function __construct(Router $router, LoggerInterface $logger, ClientInterface $alfabank, $login, $password)
    {
        $this->router = $router;
        $this->logger = $logger;
        $this->alfabank = $alfabank;
        $this->login = $login;
        $this->password = $password;
    }

    public function registerOrder ($amount, $orderNumber, $desc = 'Пополнение баланса')
    {
        $returnUrl = urlencode($this->router->generate('binidini_core_payment_new_success', [], true));
        $failUrl = urlencode($this->router->generate('binidini_core_payment_new_fail', [], true));

        $res = $this->alfabank->get("register.do?userName={$this->login}&password={$this->password}&orderNumber={$orderNumber}&amount={$amount}&description={$desc}&returnUrl={$returnUrl}&failUrl={$failUrl}")->send();

        if ($res->getStatusCode() == 200) {

            $result = json_decode($res->getBody(true));

            if (isset($result->errorCode)) {
                $this->logger->error("Deposit: {$amount}, desc:{$desc}, response:{$res->getBody(true)}");
            } else {
                $this->logger->info("Deposit: {$amount}, desc:{$desc}, response:{$res->getBody(true)}");
            }

            return $result;
        }

        $this->logger->critical("Deposit: {$amount}, desc:{$desc}, status_code:{$res->getStatusCode()}");

        return (object) ['errorCode' => $res->getStatusCode(), 'errorMessage' => 'Внутренняя ошибка'];
    }

    public function getOrderStatus($orderId)
    {
        $res = $this->alfabank->get("getOrderStatus.do?userName={$this->login}&password={$this->password}&orderId={$orderId}")->send();

        if ($res->getStatusCode() == 200) {

            $result = json_decode($res->getBody(true));

            return $result;
        }

        $this->logger->critical("getOrderStatus: {$orderId}, status_code:{$res->getStatusCode()}");

        return (object) ['orderStatus' => $res->getStatusCode(), 'errorMessage' => 'Внутренняя ошибка'];
    }
}