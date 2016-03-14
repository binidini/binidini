<?php

namespace Binidini\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Stof\DoctrineExtensionsBundle\EventListener\LoggerListener as StLoggerListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

class LoggerListener extends StLoggerListener
{

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var LoggableListener
     */
    private $loggableListener;

    public function __construct(LoggableListener $loggableListener, SecurityContextInterface $securityContext = null)
    {
        $this->loggableListener = $loggableListener;
        $this->securityContext = $securityContext;
    }


    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->securityContext) {
            return;
        }

        $token = $this->securityContext->getToken();
        if (null !== $token && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') && $token->getUser()) {
            $this->loggableListener->setUsername((string)$token->getUser()->getId());
        }
    }

}
