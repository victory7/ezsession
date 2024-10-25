<?php

if (!function_exists("getUserIp")) {
    function getUserIp() {
        // Check for shared internet/ISP IP address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
    
        // Check if the user is behind a proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // If there are multiple IPs, take the first one (the client’s real IP)
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipList[0]);
        }
    
        // Otherwise, use REMOTE_ADDR (most reliable fallback)
        return $_SERVER['REMOTE_ADDR'];
    }
}