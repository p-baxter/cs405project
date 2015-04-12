<?php
/* 
 * File: class-dbentity.php
 * 
 * The MIT License
 *
 * Copyright 2015 matt.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

abstract class DBEntity
{
    /**
     * The mysqli connection object.
     *
     * @var mysqli
     */
    protected $mysqli;
    
    /**
     * The name of the table.
     *
     * @var string 
     */
    protected $tableName;
    
    /**
     * The string name of the key.
     *
     * @var string
     */
    protected $keyName;
    
    /**
     * The value of a key.
     *
     * @var int
     */
    protected $keyValue;
    
    /**
     * Array of column names.
     *
     * @var array 
     */
    protected $columnNames;
    
    
    /**
     * Array of chars; should be one of:
     * MYSQLI_BIND_TYPE_INT, _DOUBLE, _BLOB, or _STRING (i, d, b, s).
     *
     * @var array
     */
    protected $columnBindTypes;
    
    /**
     * Find a 
     */
    public abstract function init_by_key($keyValue);

    public abstract function db_update();
    public abstract function db_insert();
    
    /**
     * 
     * @global mysqli $mysqli
     */
    public function __construct()
    {
        global $mysqli;
        $this->mysqli = $mysqli;
        $this->keyValue = null;
    }
    // end constructor.
    
    /**
     * Returns a null value if the key was not set.
     * Should return an integer if the key was set.
     * 
     * @return mixed
     */
    public function getKeyValue()
    {
        return $this->keyValue;
    }
    // end getKeyValue().
}
// end class DBEntity
