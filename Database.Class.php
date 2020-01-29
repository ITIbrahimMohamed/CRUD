<?php
/**
 * CRUD class by Ibrahim Mohamed AKA (Mangetsu)
 * Implemented the following:
 * ==========================
 * 1) Create database records.
 * 2) Read data from database
 * 3) Update records 
 * 4) Delete records from tables
 * 
 * Copyright (c) 2019 - 2020 Ibrahim Mohamed AKA Mangetsu at createivo labs.
 * 
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright   2020 Ibrahim Mohamed <ite.ibrahim.mohamed@gmail.com>
 * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 * @see         http://url.com
 */
class Database
{
    /**
     * Instance to mysqli connection
     *
     * @var mysqli
     */
    private $dbCon;

    // DB credentials
    private $host       = "localhost";
    private $username   = "root";
    private $password   = "";
    private $dbname     = "e_commerce";
    
    /**
     * Class constructor method
     * To initialize the db connection.
     */
    public function __construct()
    {
        try {
            //code...
            // mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);
            // $driver = new mysqli_driver();
            // $driver->report_mode = MYSQLI_REPORT_ALL;
            $this->dbCon = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        } catch (Exception $e) {
            //throw $th;
            die('<b>Connection failed:</b><i>' . $e->getMessage() . '</i>');
        }
    }

    /**
     * Class destructor method
     * To terminate the database connection.
     */
    public function __destruct()
    {
        # code...
        $this->dbCon->close();
    }
    
    /**
     * Create new records into the database via 'INSERT' statement
     *
     * @param string        $table      Table name that is subject for insertion operation
     * @param array         $records    Data that will be inserted to the table
     * @param array|string  $columns    Table's column/s that records will be inserted into
     * 
     * @return bool
     */
    public function create($table, $records = array(), $columns = null)
    {
        # Initiate SQL SELECT statement...
        $stmt = "INSERT INTO `" . $table . "`";

        // Checking if columns are provided!
        if ($columns != null) {
            // Check whether the provided column/s are in array or just a single string 
            $col = is_array($columns) ? implode(", ", $columns) : $columns;
            # append statement to include column/s name.
            $stmt .= " (" . $col . ")";
        }

        // Looping through each record to put inside quotes if it's a string
        for ($i = 0; $i < count($records); $i++) { 
            # Checks if each record is already a string type
            if (is_string($records[$i])) {
                # evaluate the record as a string inside quotes
                $records[$i] = '"' . $records[$i] . '"';
            }
        }

        // Assign the prepared record/s to the final statement
        $stmt .= " VALUES (" . implode(", ", $records) . ")";

        // return $stmt; // just for testing the entire query statement integrity
        return $this->dbCon->query($stmt);
    }

    /**
     * Read existing records from the database via 'SELECT' statement.
     * 
     * <code>
     *      $query = $db->read('user', ['name', 'email', 'password'], "name = {$name}", "{$id} ASC", 10, 15)
     * </code>
     *
     * @param string        $table      Table name that is subject for reading operation.
     * @param string|array  $column     Table's column/s name.
     * @param string        $where      Condition/s for fetching data.
     * @param string        $order      Sorting the fetched data.
     * @param int           $limit      Limiting the result for fetched data.
     * @param int           $offset     Return records starting from this record.
     * 
     * @return array
     */
    public function read($table, $column = "*", $where = null, $order = null, $limit = null, $offset = null)
    {
        # Initiate SQL SELECT statement...
        $stmt = "SELECT " . (is_array($column) ? implode(', ', $column) : $column) . " FROM `" . $table . "`";

        if ($where != null)
            $stmt .= " WHERE " . $where;
            
        if ($order != null)
            $stmt .= " ORDER BY " . $order;

        if ($limit != null)
            $stmt .= " LIMIT " . $limit;

        if ($offset != null)
            $stmt .= " OFFSET " . $offset;

        // Execute the final 'SELECT' query
        $result = $this->dbCon->query($stmt);

        if (!$result) {
            # Terminate if query wasn't successfully executed
            die("Database query failed");
        }            
        // $result->free(); // Clean the returned results (DB side)
        return $result;
    }

    /**
     * Update existing records in the database.
     * 
     * <code>
     *      $query = $db->update('users', ['name' => 'Adam', 'gender' => 'male', 'Age' => 30], "id = {$id}");
     * </code>
     *
     * @param string    $table      Table name that is subject for updating operation.
     * @param array     $records    Data that will be updated.
     * @param string    $where      Condition for the update operation.
     * 
     * @return bool
     */
    public function update($table, $records = array(), $where = null)
    {
        # Initiate SQL UPDATE statement...
        $stmt = "UPDATE `" . $table . "` SET ";

        $args = array();
        foreach ($records as $column => $value) {
            # code...
            $args[] = is_string($value) ? $column . ' = "' . $value . '"' : $column . ' = ' . $value;
        }
        $stmt .= implode(", ", $args);

        if ($where != null && is_string($where)) {
            # code...
            $stmt .= ' WHERE ' . $where;
        }
        $result = $this->dbCon->query($stmt);

        return $result;
    }

    /**
     * Delete existing records from the database.
     * 
     * <code>
     *      $query = $db->delete('user', "name LIKE '%{$username}%'");
     * </code>
     *
     * @param string    $table  Table name that is subject for deletion operation.
     * @param string    $where  Condition for deletion operation.
     * 
     * @return bool
     */
    public function delete($table, $where = null)
    {
        # Initiate SQL DELETE statement...
        $stmt = "";

        if ($where == null) {
            # code...
            $stmt = "DELETE " . $table;
        } else {
            # code...
            $stmt = "DELETE FROM `" . $table . "` WHERE " . $where;
        }
        $result = $this->dbCon->query($stmt);

        return $result;
    }

    /**
     * Filters out data comes from forms.
     * 
     * <code>
     *      $name = $db->sanitize($name);
     *      $query = $db->update('user', ['name' => $name], "id = {$id}");
     * </code>
     *
     * @param   string  $data   Inputs that will be prepared for sql query.
     * 
     * @return  string
     */
    public function sanitize($data)
    {
        # code...
        return $this->dbCon->real_escape_string($data);
    }
}
