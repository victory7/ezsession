<?php
namespace EzSession\Integrations\Laravel;

use Closure;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Session\Session;

class EzSessionStartMiddleware extends StartSession
{
    /**
     * Get the session implementation from the manager.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSession(Request $request): Session
    {
        return tap($this->manager->driver(), function ($session) use ($request) {
            $session->setId(session_id());
        });
    }

    protected function addCookieToResponse(Response $response, Session $session)
    {
        // Just bypass this function for now!
        return;
    }
}