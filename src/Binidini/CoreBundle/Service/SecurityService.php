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

use Binidini\CoreBundle\Model\SenderCarrierAwareInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityService
{

    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function checkSender(SenderCarrierAwareInterface $resource)
    {
        if ($resource->getSender()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь отправителем. Данная операция запрещена.");
    }

    public function checkCarrier(SenderCarrierAwareInterface $resource)
    {
        if ( is_null($resource->getCarrier()) or  $resource->getCarrier()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь перевозчиком. Данная операция запрещена.");
    }

    public function checkResolver()
    {
        throw new AccessDeniedHttpException("Данная операция не реализована.");
    }

    private function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }

    public static function generatePassword()
    {
        return sprintf('%d', rand(100000, 999999));
    }
}