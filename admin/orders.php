<?php
/* 
 * File: orders.php
 * 
 * @TODO: check if we need a foreign key constraint between OrderItem
 * and Item.
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

const IDSTR = 'oId';

require './includes/application_top.php';
require './includes/class-ItemType.php';
require './includes/class-Item.php';
require './includes/class-Customers.php';
require './includes/class-OrderItem.php';
require './includes/class-Orders.php';
require './includes/class-HtmlWrap.php';

// @TODO: verify manager  type.

if( isset($_GET['action']) )
{
    if( $_GET['action'] == 'shipit' && isset($_GET[IDSTR]) )
    {
        /*
         * If all the components are available, the status of the order changes
         *  from "Pending" to "Shipped" and the quantities in the inventory are
         *  decreased. If the components are not available, some error page
         *  listing the missing components is generated and the order remains 
         * "Pending".
         */
        
        // Verify that all the items are available.
        $Order = new Orders();
        $Order->init_by_key($_GET[IDSTR]);
     
        echo '<pre>';
        $Order->shipIt();

        // @TODO: show any errors.
        
        

        // Redirct back to items.php.
//        http_redirect(FILENAME_ORDERS);
        
        exit;
    }
}

$headerAdditionalCss = <<<ENDCSS
 p.shipTo { background-color: white; } 
        
 table.tableset.items th { background-color: yellowgreen;}
 table.tableset.items { background-color: #ddd;}
ENDCSS;
require './header.php';

//$mysqli = new mysqli();

$editEntity = false;
if( isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET[IDSTR]))
{
    $editEntity = true;
}

$stmt = $mysqli->prepare("SELECT o.orderId, c.name as custname, o.dateOrdered,"
        . " os.name as ostatus, o.shipTo "
        . "FROM Orders o JOIN OrderStatus os ON os.statusId = o.statusId "
        . "JOIN Customer c ON c.custId = o.custId "
        . ($editEntity ? " WHERE o.orderId = ".(int)$_GET[IDSTR] : "")
        . " ORDER BY o.orderId" );

$stmt->execute();

$stmt->bind_result($orderId, $custId, $dateOrdered, $statusId, $shipTo );

$data = array();

$colnames = array();
$coltypes = array();
$result = $stmt->result_metadata();

$flds = $result->fetch_fields();

//echo '<pre>'. print_r($flds,true).'</pre>';

foreach( $flds as $val )
{
    $colnames[] = $val->name;
    
    // Detect the field type for CSS formatting.
    $type = TableSet::TYPE_STRING;
    switch( $val->type)
    {
        case MYSQLI_TYPE_BIT:
        case MYSQLI_TYPE_LONG:
            $type = TableSet::TYPE_INT;
            break;
        
        case MYSQLI_TYPE_STRING:
            $type = TableSet::TYPE_STRING;
            break;
        
        case MYSQLI_TYPE_NEWDECIMAL:
            $type = TableSet::TYPE_REAL;
            break;
    }
    $coltypes[] = $type;
}

while( $stmt->fetch())
{    
    $col = array( $orderId, $custId, $dateOrdered, $statusId, $shipTo );
    $data[] = $col;
}

$stmt->close();


//include('./includes/class-TableSet.php');

$ts = new TableSet();
$ts->set_data($data);

$ts->show_row_numbers(false);
//$ts->replace_column_values(1, array(0 => 'no', 1 => 'yes'));


$ts->set_column_names($colnames);

$ts->set_column_name(0, 'Order #');
$ts->set_column_name(1, 'Customer');
$ts->set_column_name(2, 'Date');
$ts->set_column_name(3, 'Status');
//
//$ts->set_column_types($coltypes);
//$ts->set_column_type(1, TableSet::TYPE_STRING);
//
//$ts->add_column('Special Price');
//$ts->set_column_type(7, TableSet::TYPE_REAL);
//$ts->set_column_width(7, '60px');

if( ! $editEntity)
    $ts->add_column('&nbsp;');

// Add the links to edit.
for($row=0,$n=$ts->get_num_rows(); $row < $n; $row++)
{
    if( ! $editEntity)
    {
        $itemId = $ts->get_value_at($row, 0);
        $href = '<a href="'.href_link(FILENAME_ORDERS, array('action' => 'edit', IDSTR => $itemId))
                .'">edit</a>';
        $ts->set_value_at($row, 5, $href);
    }
    // end $editItem.
}
// done iterating over rows.

$ts->print_table_html();

if( ! $editEntity )
    echo '<a href="'.  href_link(FILENAME_ORDERS, array('action'=>'create')).'">Insert</a><br/>'."\n";

if( isset($_GET['action']) && $_GET['action']=='create')
{
    // @TODO: combine insert and edit code.
    $HW = new HTMLWrap();
    
    echo '<form action="'.  href_link(FILENAME_ORDERS, array('action' => 'insert' )).'" method="POST">'."\n"
            ."<fieldset>\n";
    
    $itypes = ItemType::fetch_all($mysqli, ItemType::RESULT_ASSOC_ARRAY);
    
    $Item = new Item();
        
    $HW->print_checkbox('enabled', 1, 'Enabled', $Item->enabled);
    echo "<br/>";
    
    $HW->print_select('itype', $itypes, 'Item Type', $Item->itemType );
    echo "<br/>";
    
    $HW->print_textbox('qty', $Item->qty_available, 'Qty Avail');
    echo "<br/>";
    
    $HW->print_textbox('name', $Item->name, 'Name');
    echo "<br/>";
    
    $HW->print_textbox('promo', $Item->promoRate, 'Promo Rate');
    echo "<br/>";
    
    $HW->print_textbox('price', $Item->price, 'Price');
    echo "<br/>";
    
    $HW->print_textbox('image', $Item->imageName, 'Image');
    echo "<br/>";
    
    echo '<br><input type="submit" value="Insert" /> <a href="'.  href_link(FILENAME_ORDERS).'">Cancel</a>';
    echo "</fieldset>\n</form>\n";
}
// done insert form.

//
// Print a form to edit an individual item.
//
if(  $editEntity )
{
    $HW = new HTMLWrap();
    
    echo '<form action="'.  href_link(FILENAME_ORDERS, array('action' => 'shipit', IDSTR => $_GET[IDSTR])).'" method="POST">'."\n"
            ."<fieldset>\n";
    
//    $itypes = ItemType::fetch_all($mysqli, ItemType::RESULT_ASSOC_ARRAY);
    
    $Orders = new Orders();
    $Orders->init_by_key($_GET[IDSTR]);
    
    echo '<p>Order#: ' . $Orders->getKeyValue() . "</p>\n";
    
    $Date = $Orders->get_dateOrdered();
    
    echo '<p>Customer Name: ' . $Orders->get_customer()->name . "<br/>\n"
            .'Date: ' . $Date->format(DATE_ADMIN_ORDER_DETAIL) . "</p>\n";
    
    echo '<p class="shipTo">ShipTo: <br/>' . $Orders->shipTo. "</p>\n";
    
    
    $ItemTS = new TableSet();
    $ItemTS->css_extra_class = 'items';
    $ItemTS->show_row_numbers(false);
  
    
    $columnNames = array('Item ID','Name','Price','Qty Ordered','SubTotal','Qty Available');
    $columnTypes = array(TableSet::TYPE_INT, TableSet::TYPE_STRING,
        TableSet::TYPE_REAL, TableSet::TYPE_INT, TableSet::TYPE_REAL,
        TableSet::TYPE_INT);
    
    $data = array();
    
    $itemList = $Orders->get_item_list();
    foreach( $itemList as $id => $OrderItem )
    {
        $Item = new Item();
        $Item->init_by_key($id);
        
        $row = array();
        $row[0] = $id;
        $row[1] = $Item->name;
        $row[2] = $OrderItem->price;
        $row[3] = $OrderItem->qty;
        
        $row[4] = $OrderItem->price * $OrderItem->qty;
        
        
        
        $row[5] = $Item->qty_available;
        
        $data[] = $row;
    }
    // done creating item table data.
    
    $ItemTS->set_data($data);
    
    $ItemTS->set_column_names($columnNames);
    $ItemTS->set_column_types($columnTypes);
    $ItemTS->set_column_format(2, '$%0.2f');
    $ItemTS->set_column_format(4, '$%0.2f');
    $ItemTS->footer = null;
    
    $ItemTS->print_table_html();
    
//    echo '<pre>' . print_r($Orders,true) . "</pre>\n";
      
    echo '<br><input type="submit" value="SHIP IT" /> <a href="'.  href_link(FILENAME_ORDERS).'">Cancel</a>';
    echo "</fieldset>\n</form>\n";
}
// done printing edit form.

require './footer.php';

