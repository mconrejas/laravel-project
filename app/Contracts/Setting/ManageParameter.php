<?php

namespace Buzzex\Contracts\Setting;

interface ManageParameter
{
    /**
     * @param string $key
     * @param mixed $value
     * @param string $description|optional
     *
     * @return void
     */
    public function set($key, $value, $description);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);
}
