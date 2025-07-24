<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $roles = $token->getRoleNames();

        $targetPath = match (true) {
            in_array('ROLE_ADMIN', $roles) => 'app_administration',
            in_array('ROLE_PARENT', $roles) => 'app_user_index',
            in_array('ROLE_STAFF', $roles) => 'app_planning_day',
            default => 'app_user_index',
        };

        return new RedirectResponse($this->urlGenerator->generate($targetPath));
    }
}