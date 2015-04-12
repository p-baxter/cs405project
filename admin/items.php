<?php

/* 
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

require './header.php';

//$mysqli = new mysqli();

$stmt = $mysqli->prepare("SELECT itemId, enabled, itemType, qty_available, name, promoRate, price, imageName from Item ");
        
$stmt->execute();

$stmt->bind_result($itemid, $enab, $itype, $qty, $name, $promo, $price, $image );

$data = array();

$colnames = array();
$result = $stmt->result_metadata();

$flds = $result->fetch_fields();

//echo '<pre>'. print_r($flds,true).'</pre>';

foreach( $flds as $val )
{
    $colnames[] = $val->name;
}

while( $stmt->fetch())
{    
    $col = array( $itemid, $enab, $itype, $qty, $name, $promo, $price, $image);
    $data[] = $col;
}

$stmt->close();


//include('./includes/class-TableSet.php');

$ts = new TableSet();
$ts->set_data($data);

//die( var_dump($ts->set_column_names($colnames)));
$ts->set_column_names($colnames);

$ts->print_table_html();

//echo '<pre>'. print_r($colnames,true).'</pre>';

require './footer.php';

