<?php

namespace Buzzex\Misc;


class Misc
{
    /**
     * @return array|mixed|string
     */
    function get_ip_address()
    {
        if (!isset($_SERVER["REMOTE_ADDR"])) $ip_address = "127.0.0.1"; // running in CLI mode
        else $ip_address = $_SERVER["REMOTE_ADDR"];

        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip_array = explode(",", getenv('HTTP_X_FORWARDED_FOR'));
            $ip_address = array_shift($ip_array);
        }

        return $ip_address;
    }
}
