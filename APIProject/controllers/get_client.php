<?php

function get_client_ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Get the last IP address in the X-Forwarded-For header
        $ip_addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim(end($ip_addresses));
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        // In case there is no proxy, use the REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return 'unknown';
    }
}
