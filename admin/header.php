<?php
/* 
 * header.php
 * 
 * Print the HTML page header used in all admin pages.
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

include './includes/class-TableSet.php';

//
// Make a page header to be shown in all admin pages. Includes the navigation
// box.
//

TableSet::set_default_css();

?>
<!DOCTYPE html>
<html>
 <head>
  <title><?php echo STORE_NAME; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <style type="text/css">
     body{
         margin: 10px;
         background: none repeat scroll 0 0 #ccc;
     }
     #header {
/*         position: absolute;
         top:0px;
         left: 0px;
         width: 100%;
         height: 100px;*/
     } 
     #navBox {
         width: 130px;
         padding: 10px;
         background: none repeat scroll 0 0 #aaa;
     }
     #mainContent { 
         position: relative;
         left: 173px;
         top: -135px;
         width: 85%;
     }
     
     <?php TableSet::print_css(); ?>
     
    </style>
    <script type="text/javascript"></script>
 </head>
 <body>
  <div id="header">
   <img class="logo" width="100px" height="100px" src="<?php echo STORE_LOGO_IMG; ?>" />
   <?php echo STORE_NAME; ?>
  </div>
  <div id="navBox">
   <div class="boxlabel">Links</div>
   <ul>
    <li><a href="<?php echo href_link(FILENAME_ORDERS); ?>">Orders</a></li>
    <li><a href="<?php echo href_link(FILENAME_ITEMS); ?>">Items</a></li>
    <li><a href="<?php echo href_link(FILENAME_SPECIALS); ?>">Specials</a></li>
    <li><a href="<?php echo href_link(FILENAME_REPORTS); ?>">Reports</a></li>
    <li><a href="<?php echo href_link(FILENAME_LOGIN, array('action' => 'logout') ); ?>">Log Out</a></li>
   </ul>
  </div>
  <div id="mainContent">
<?php
