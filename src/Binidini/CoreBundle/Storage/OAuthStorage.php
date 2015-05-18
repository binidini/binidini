<?php

namespace Binidini\CoreBundle\Storage;

use Binidini\CoreBundle\Service\AuthenticationService;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage as FosOAuthStorage;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorage extends FosOAuthStorage
{

    private $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService,
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        UserProviderInterface $userProvider = null,
        EncoderFactoryInterface $encoderFactory = null
    ) {
        parent::__construct(
            $clientManager,
            $accessTokenManager,
            $refreshTokenManager,
            $authCodeManager,
            $userProvider,
            $encoderFactory
        );
        $this->authenticationService = $authenticationService;
    }


    /**
     * @param IOAuth2Client $client
     * @param string $username
     * @param string $password
     * @return array|bool
     */
    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        $isAuth = parent::checkUserCredentials($client, $username, $password);
        if (!$isAuth) {
            $isAuth = $this->authenticationService->authByRecoverPassword($username, $password);
            if ($isAuth) {
                return array(
                    'data' => $this->authenticationService->user,
                );
            }
        }
        return $isAuth;
    }

}