<?php

declare(strict_types=1);

namespace Oro\Bundle\WsseAuthenticationBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

trait WsseRestrictAccessTrait
{
    private function checkRequest(Request $request, TokenInterface $token): void
    {
        $routes = $token->hasAttribute('allowed_routes') ? $token->getAttribute('allowed_routes') : null;
        $routesRegex = $token->hasAttribute('allowed_routes_regex') ? $token->getAttribute('allowed_routes_regex') : null;

        if ($routes === null && $routesRegex === null) {
            return;
        }

        $route = $request->attributes->get('_route');

        if (in_array($route, $routes ?: [], true)) {
            return;
        }

        foreach ($routesRegex ?? [] as $regex) {
            if (preg_match($regex, $route)) {
                return;
            }
        }

        throw new CustomUserMessageAuthenticationException('This route is not allowed for this api token');
    }
}
