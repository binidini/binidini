<?php

namespace Binidini\CoreBundle\Handler;

use Binidini\CoreBundle\Service\AuthenticationService;
use FOS\UserBundle\Security\LoginManagerInterface;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    private $authenticationService;
    private $routerInterface;
    private $loginManager;

    public function __construct(AuthenticationService $authenticationService, LoginManagerInterface $loginManager,  RouterInterface $routerInterface, HttpKernelInterface $httpKernel, HttpUtils $httpUtils)
    {
        parent::__construct($httpKernel, $httpUtils, []);
        $this->authenticationService = $authenticationService;
        $this->loginManager = $loginManager;
        $this->routerInterface = $routerInterface;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $username = $request->get('_username');
        $plainPassword = $request->get('_password');
        $isAuth = $this->authenticationService->authByRecoverPassword($username, $plainPassword);
        if ($isAuth){
            $this->loginManager->loginUser($username, $this->authenticationService->user);
            return new RedirectResponse($this->routerInterface->generate('binidini_home'));
        }
        return parent::onAuthenticationFailure($request, $exception);
    }
}