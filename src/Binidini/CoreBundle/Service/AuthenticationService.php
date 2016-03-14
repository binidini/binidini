<?php

namespace Binidini\CoreBundle\Service;

use Binidini\CoreBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AuthenticationService
{
    /** @var User */
    public $user;

    private $memcached;

    private $userManager;
    private $encoder;

    public function __construct(UserManagerInterface $userManager, EncoderFactoryInterface $encoder, MemcacheInterface $memcached)
    {
        $this->memcached = $memcached;
        $this->userManager = $userManager;
        $this->encoder = $encoder;
    }

    public function authByRecoverPassword($username, $plainPassword)
    {
        $memcacheKey = User::PASSWORD_RECOVER_PREFIX.$username;
        if ($this->memcached->get($memcacheKey)) {
            $this->user = $this->userManager->findUserByUsername($username);
            if ($this->user) {
                $encoder = $this->encoder->getEncoder($this->user);
                if ($encoder->isPasswordValid($this->user->getRecoverPassword(), $plainPassword, $this->user->getRecoverSalt())) {
                    $this->user->setRecoverPassword('');
                    $this->user->setRecoverSalt('');
                    $this->user->setPlainPassword($plainPassword);
                    $this->userManager->updateUser($this->user);
                    $this->userManager->reloadUser($this->user);
                    $this->memcached->delete($memcacheKey);
                    return true;
                }
            }
        }
        return false;
    }
}