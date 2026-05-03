<?php

namespace App\Security;

use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator
    ) {
    }

    function onAuthenticationSuccess(\Symfony\Component\HttpFoundation\Request $request, TokenInterface $t): Response
    {
        $user = $t->getUser();
        $token = $this->jwtTokenManager->create($user);
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 9600);
        return new JsonResponse([
            'token' => $token,
            'refresh_token' => $refreshToken
        ]);
    }
}
