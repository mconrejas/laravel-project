<?php

namespace Buzzex\Services;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseService constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function create(array $data)
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param array $filters
     * @param bool $raw
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null|object
     */
    public function read(array $filters = [], $raw = false)
    {
        $query = $this->model->newQuery();

        if (count($filters) === 0) {
            return $query->get();
        }

        foreach ($filters as $field => $conditions) {
            if (!is_array($conditions)) {
                $query = $query->where($field, $conditions);
                continue;
            }

            list($condition, $value) = $conditions;
            $query = $query->where($field, $condition, $value);
        }

        if ($raw) {
            return $query;
        }

        if ($query->count() === 1) {
            return $query->first();
        }

        return $query->get();
    }

    /**
     * @param array $filters
     * @param array $data
     *
     * @return bool
     */
    public function update(array $filters, array $data)
    {
        $query = $this->read($filters, true);

        return $query->update($data);
    }

    /**
     * @param array $filters
     *
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function delete(array $filters)
    {
        $query = $this->read($filters, true);

        return $query->delete();
    }
}