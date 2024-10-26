<?php
namespace EzSession\Integrations\Laravel;

use EzSession\Integrations\Laravel\EzSessionStore;
use Illuminate\Session\SessionManager;

class EzSessionManager extends SessionManager
{
    protected function buildSession($handler): EzSessionStore
    {
        $sessionName = $this->config->get('session.cookie');

        return new EzSessionStore($sessionName, $handler);
    }

    public function debug() {
        echo "hi";
    }
}