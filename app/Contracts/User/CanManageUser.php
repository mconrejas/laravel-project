<?php

namespace Buzzex\Contracts\User;

interface CanManageUser
{
    /**
     * @param array $data
     *
     * @return \Buzzex\Models\User
     */
    public function create(array $data);

    /**
     * @param array $filters
     * @param bool $raw
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Buzzex\Models\User
     */
    public function read(array $filters = [], $raw = false);

    /**
     * @param array $filters
     * @param array $data
     *
     * @return mixed
     */
    public function update(array $filters, array $data);

    /**
     * @param array $filters
     *
     * @return bool
     */
    public function delete(array $filters);
}