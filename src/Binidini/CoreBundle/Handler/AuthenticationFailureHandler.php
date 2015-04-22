<?php

namespace Binidini\CoreBundle\Handler;

use Binidini\CoreBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\LoginManagerInterface;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    private $memcached;
    private $loginManager;
    private $firewallName;
    private $userManager;
    private $router;

    public function __construct(LoginManagerInterface $loginManager, $firewallName, MemcacheInterface $memcached, UserManagerInterface $userManager, RouterInterface $router, HttpKernelInterface $httpKernel, HttpUtils $httpUtils)
    {
        parent::__construct($httpKernel, $httpUtils, []);
        $this->memcached = $memcached;
        $this->userManager = $userManager;
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
        $this->router = $router;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $username = $request->get('_username');
        $plainPassword = $request->get('_password');
        $memcacheKey = User::PASSWORD_RECOVER_PREFIX.$username;
        if ($this->memcached->get($memcacheKey)) {
            /** @var User $user */
            $user = $this->userManager->findUserByUsername($username);
            if ($user) {
                $encoder = new Pbkdf2PasswordEncoder();
                if ($encoder->isPasswordValid($user->getRecoverPassword(), $plainPassword, $user->getRecoverSalt())) {
                    $user->setRecoverPassword('');
                    $user->setRecoverSalt('');
                    $user->setPlainPassword($plainPassword);
                    $this->userManager->updateUser($user);
                    $this->userManager->reloadUser($user);
                    $this->memcached->delete($memcacheKey);
                    $this->loginManager->loginUser($username, $user);
                    return new RedirectResponse($this->router->generate('binidini_home'));
                }
            }
        }
        return parent::onAuthenticationFailure($request, $exception);
    }
}