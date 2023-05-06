<?php

namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * When menu item is forwarded in Controller, request doesn't contain "_route" attribute.
 *
 * This listener adds attribute "_master_request_route" to master request and all sub-requests.
 * Thus client code can safely use this attribute to get original route of master request.
 */
class AddMasterRequestRouteListener
{
    /** @var array */
    protected $masterRequestRoute;

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($event->isMainRequest()) {
            if ($request->attributes->has('_route')) {
                $this->masterRequestRoute = $request->attributes->get('_route');
                $request->attributes->set('_master_request_route', $this->masterRequestRoute);
            }
        } else {
            $request->attributes->set('_master_request_route', $this->masterRequestRoute);
        }
    }
}
