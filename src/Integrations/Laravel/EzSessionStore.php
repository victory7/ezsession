<?php
namespace EzSession\Integrations\Laravel;

use Illuminate\Session\Store;
use SessionHandlerInterface;

class EzSessionStore extends Store
{
    public function __construct($name, SessionHandlerInterface $handler, $id = null)
    {
        parent::__construct($name, $handler, $id);
    }
    
    /**
     * Override the isValidId method to match your custom session ID format.
     */
    public function isValidId($id)
    {
        // Implement your custom validation logic for session IDs
        return is_string($id);
    }
}