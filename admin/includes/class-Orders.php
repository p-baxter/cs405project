<?php
/* 
 * class-Orders.php
 * 
 * Defines the order db entity class.
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

class Orders extends DBEntity
{    
    public $custId = null;
    public $dateOrdered = null;
    public $statusId = null;
    public $shipTo = null;
    
    protected $items;
    
    const SQL_COLUMN_LIST = " orderId, custId, dateOrdered, statusId, shipTo ";
    
    public function __construct( $id = null )
    {
        parent::__construct($id);
        $this->tableName = '`Orders`';
        $this->keyName = 'orderId';
        
        $items = array();
    }
    
    /**
     * 
     * @param OrderItem $Item Add or replace an item to this order.
     */
    public function set_item( $Item )
    {
        if( $Item->itemId != null )
        {
            // Set orderId for the OrderItem to be same as our orderId.
            $Item->keyValue = $this->keyValue;
            
            // Add the item.
            $this->items[ $Item->itemId ] = $Item;
        }
    }
    
    /**
     * 
     * @param int $itemId
     * @return OrderItem
     */
    public function get_item($itemId)
    {
        if( isset($this->items[$itemId]))
        {
            return $this->items[$itemId];
        }
        return null;
    }
    
    public function fetch_all_items()
    {
        $retval = false;
        
        if( $this->keyValue != null )
        {
            $stmt = $this->mysqli->prepare("SELECT itemId, price, qty FROM OrderItem WHERE orderId = ? ");
            if( $stmt )
            {
                if( $stmt->bind_param(MYSQLI_BIND_TYPE_INT, $this->keyValue))
                {
                    if( $stmt->execute())
                    {
                        $stmt->bind_result($itemId, $price, $qty);
                        
                        while( $stmt->fetch())
                        {
                            $OItem = new OrderItem($this->keyValue);
                            $OItem->itemId = $itemId;
                            $OItem->price = $price;
                            $OItem->qty = $qty;
                            
                            $this->items[ $itemId ] = $OItem;
                        }
                        $retval = true;
                    }
                }
                $stmt->close();
            }
        }
        
        return $retval;
    }
  
    /**
     * Set the class member values by fetching values from the database.
     * If the key was not found, then the class member fields remain null.
     * 
     * @param string $value
     * The table key to search the database for.
     * 
     * @return mixed
     * Returns true if data was fetched.
     * Returns false if there was an error.
     * Returns null if no data was found.
     */
    public function init_by_key($value)
    {
        $retval = false;
        $stmt = $this->mysqli->prepare("SELECT " . self::SQL_COLUMN_LIST . "FROM ".$this->tableName." WHERE ".$this->keyName." = ? ");
        
        if( $stmt )
        {
            if( $stmt->bind_param(MYSQLI_BIND_TYPE_INT, $value))
            {
                if( $stmt->execute() )
                {
                    $stmt->bind_result($this->keyValue, 
                            $this->custId,
                            $this->dateOrdered,
                            $this->statusId,
                            $this->shipTo  );
                    $retval = $stmt->fetch();
                }
                // end if execute was good.
            }
            // end if bind succeeded.
            $stmt->close();
        }
        // end if stmt good.
        
        return $retval;
    }
    // end find_by_key().
    
    /**
     * Update a record matching this record's key with this class member's values.
     * 
     * @return boolean
     */
    public function db_update()
    {
        $retval = false;
        
        if( $this->getKeyValue() != null )
        {
            $stmt = $this->mysqli->prepare("UPDATE ". $this->tableName." "
                . "SET custId=?, dateOrdered=?, statusId=?, shipTo=? "
                . "WHERE ".$this->keyName."=?");
            
            if($stmt)
            {
                if( $stmt->bind_param("isisi",
                        $this->custId,
                        $this->dateOrdered,
                        $this->statusId,
                        $this->shipTo,
                        $this->keyValue))
                {
                    $retval = $stmt->execute();
                }
                $stmt->close();
            }
            // end if stmt good.
            
            // Update each of the order item values.
            // @TODO: what if the orderItem didn't already exist?
            foreach($this->items as $itemId => $OItem )
            {
                $OItem->db_update();
            }
            
        }
        // end if not null.
        
        return $retval;
    }
    // end update_db().
    
    /**
     * Inserts a new record into the database with the given value of name.
     * The auto-incremented id is set to this->keyValue.
     * 
     * @return boolean
     */
    public function db_insert()
    {
        $retval = false;
        
        $stmt = $this->mysqli->prepare("INSERT INTO ".$this->tableName
            ." ( custId, dateOrdered, statusId, shipTo ) VALUES (?,?,?,? )");

        if($stmt)
        {
            if( $stmt->bind_param("isis",
                        $this->custId,
                        $this->dateOrdered,
                        $this->statusId,
                        $this->shipTo ))
            {
                if($stmt->execute())
                {
                    $this->keyValue = $this->mysqli->insert_id;
                    $retval = true;
                }
            }
            $stmt->close();
        }
        // end if stmt good.
        
        return $retval;
    }
    // end db_insert().
    
    /**
     * Return all Orders optionally having the specified statusId(s).
     * 
     * @param mixed $status
     * Default of null does not filter by statusId.
     * Single integer value searches records with matching statusId.
     * Array of integers searches records with any of the given statusIds.
     * 
     * @return \Order
     * Returns an array containing Order objects or an empty array if none
     * were found.
     */
    public static function fetch_all($status = null)
    {
        $statusstr = "";
        if(is_array($status))
        {
            $statusstr = "WHERE statusId IN (".implode(',', $status).") ";
        }
        else if($status !== null )
        {
            $statusstr = " WHERE statusId=".(int)$status;
        }
        
        $retval = array();
        $stmt = $this->mysqli->prepare("SELECT orderId, custId, dateOrdered, "
                . "statusId, shipTo FROM Order " . $statusstr );
        
        if( $stmt )
        {
            if( $stmt->execute() )
            {
                $stmt->bind_result($id, $custid, $date, $statusid, $shipto );
                
                while( $stmt->fetch() )
                {
                    $Object = new Orders($id);

                    $Object->custId = $custid;
                    $Object->dateOrdered = $date;
                    $Object->statusId = $statusid;
                    $Object->shipTo = $shipto;

                    $retval[] = $Object;
                }
                // done fetching rows.
            }
            // end if execute was good.
                
            // end if bind succeeded.
            $stmt->close();
        }
        // end if stmt good.
        
        return $retval;
    }
    // end fetch_all().
}
// end class Staff.
