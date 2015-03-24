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

use Binidini\CoreBundle\Model\UserAwareInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserAwareListener
{
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function setUser(GenericEvent $event)
    {

        $resource = $event->getSubject();

        if (!$resource instanceof UserAwareInterface) {
            throw new UnexpectedTypeException($resource, 'Binidini\CoreBundle\Model\UserAwareInterface');
        }

        if (null === $user = $this->getUser()) {
            return;
        }

        $resource->setUser($user);
    }

    protected function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }
}
