<?php

namespace Buzzex\Services;

use Cache;
use Buzzex\Models\Parameter as SettingModel;
use Buzzex\Contracts\Setting\ManageParameter;

class SiteParameterService implements ManageParameter
{
    /**
     * Set value against a key.
     *
     * @param string $key
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function set($key, $value, $description = "")
    {
        if (Cache::has($key)) {
            Cache::forget($key);
        }

        $setting = SettingModel::create(
            [
                'name' => $key,
                'value' => $value,
                'description' => $description,
                'updated_by' => auth()->user()->id ?: 0
            ]
        );

        Cache::forever($key, $value);

        return $setting ? $value : false;
    }

    /**
     * Get value by a key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Cache::rememberForever($key, function () use ($key) {
            return SettingModel::where('name', $key)->pluck('value')->first();
        });
    }
}
