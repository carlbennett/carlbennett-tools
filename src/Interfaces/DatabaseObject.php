<?php /* vim: set colorcolumn=: */

namespace CarlBennett\Tools\Interfaces;

/**
 * This interface is implemented for managing the lifecycle of a database object.
 */
interface DatabaseObject
{
    public const DATE_SQL = 'Y-m-d H:i:s';
    public const DATE_TZ = 'Etc/UTC';
    public const UUID_REGEX = '/([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}/';

    /**
     * Allocates the properties of this object from the database. Used to reload an existing record.
     *
     * @return boolean Whether the operation was successful.
     */
    public function allocate() : bool;

    /**
     * Commits the properties of this object to the database. Used to create a new or update an existing record.
     *
     * @return boolean Whether the operation was successful.
     */
    public function commit() : bool;

    /**
     * Deallocates the properties of this object from the database. Used to delete a record.
     *
     * @return boolean Whether the operation was successful.
     */
    public function deallocate() : bool;
}
