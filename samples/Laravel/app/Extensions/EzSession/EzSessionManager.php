<?php
namespace App\Extensions\EzSession;

use App\Extensions\EzSession\EzSessionStore;
use Illuminate\Session\SessionManager;

class EzSessionManager extends SessionManager
{
    protected function buildSession($handler): EzSessionStore
    {
        $sessionName = $this->config->get('session.cookie');

        return new EzSessionStore($sessionName, $handler);
    }
}