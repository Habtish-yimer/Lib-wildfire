<?php

namespace Rougin\Wildfire\Traits;

/**
 * Result Trait
 * 
 * @package Wildfire
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
trait ResultTrait
{
    /**
     * @var \CI_DB_result
     */
    protected $query;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * Creates an object from the specified table and row.
     *
     * @param  string $table
     * @param  object $row
     * @return array
     */
    abstract protected function createObject($table, $row);

    /**
     * Returns all rows from the specified table.
     * 
     * @param  string $table
     * @return self
     */
    abstract public function get($table = '');

    /**
     * Returns the result.
     * 
     * @return object
     */
    public function result()
    {
        $data = $this->getQueryResult();
        $result = [];

        if (empty($this->table)) {
            $this->get();
        }

        foreach ($data as $row)
        {
            $object = $this->createObject($this->table, $row);

            array_push($result, $object);
        }

        return $result;
    }

    /**
     * Gets the data result from the specified query.
     * 
     * @return array|object
     */
    protected function getQueryResult()
    {
        $result = $this->query;

        if (method_exists($this->query, 'result')) {
            $result = $this->query->result();
        }

        return $result;
    }
}
