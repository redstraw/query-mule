<?php namespace freidcreations\QueryMule\Query\Sql\Common;
use freidcreations\QueryMule\Query\Sql\Common\TableColumnDefinitionInterface;

/**
 * Interface TableColumnInterface
 * @package freidcreations\QueryMule\Query\Sql\Common
 */
interface TableColumnHandlerInterface
{
    /**
     * Handle Modify
     * @param $column
     */
    public function handleModify($column);

    /**
     * Handle Column
     * @param TableColumnDefinitionInterface $column
     * @param null|string $type
     */
    public function handleColumn(TableColumnDefinitionInterface $column, $type = null);

    /**
     * Handle Primary Key
     * @param array $columns
     * @return void
     */
    public function handlePrimaryKey(array $columns);

    /**
     * Handle Unique Key
     * @param array $columns
     * @return void
     */
    public function handleUniqueKey(array $columns);

    /**
     * Handle Index
     * @param array $columns
     * @return void
     */
    public function handleIndex(array $columns);

}