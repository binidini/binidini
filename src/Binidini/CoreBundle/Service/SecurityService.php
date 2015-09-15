<?php

namespace Binidini\CoreBundle\Service;

use Binidini\CoreBundle\Entity\Message;
use Binidini\CoreBundle\Entity\User;
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

    public function checkRecipient(Message $message)
    {
        if ( is_null($message->getRecipient()) or  $message->getRecipient()->getId() != $this->getUser()->getId())
            throw new AccessDeniedHttpException("Вы не являетесь получателем сообщения. Данная операция запрещена.");
    }

    public function checkResolver()
    {
        if (!$this->getUser()->isAdmin()){
            throw new AccessDeniedHttpException("Данная операция не реализована.");
        }
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