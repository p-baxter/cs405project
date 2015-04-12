<?php
/* 
 * header.php
 * 
 * Include files that will be used with all admin pages.
 * database.php establishes a mysql connection or dies.
 * 
 * Verify that a user is logged in, or redirect them to the login page.
 * If the user is logged in, then show the page header.
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

require('./includes/constants.php');
require('./includes/functions.php');
require('./includes/database.php');

require('./includes/class-dbentity.php');
require('./includes/class-staff.php');

// (Certain installations of PHP print warnings if default timezone is not set.)
date_default_timezone_set(TIMEZONE_DEFAULT);

session_start();

//
// Detect if the user is logged in.
//

// Redirect if session ID is not registered.
// Note: the script stops running upon running http_redirect().
if( ! session_is_registered(SESSION_ID_KEY) )
{
    http_redirect(FILE_LOGIN);
}

$staff = new Staff();
if( ! $staff->init_by_sessionId($_SESSION[SESSION_ID_KEY]) )
{
    echo $mysqli->error;
    exit();
}

// If the query failed to find a value, then unset the $_SESSION value and
// redirect the user to the login page.
if( $staff->getKeyValue() === null )
{
    unset($_SESSION[SESSION_ID_KEY]);
    http_redirect(FILE_LOGIN);
}
//
// done verifying that user is logged in.
//

//
// Make a page header to be shown in all admin pages. Includes the navigation
// box.
//
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
     }
     #header {
         position: absolute;
         top:0px;
         left: 0px;
         width: 100%;
         height: 100px;
     } 
     #navBox {
         position: absolute;
         top:100px;
         left: 0px;
         width: 250px;
         height: 500px;
     }
    </style>
    <script type="text/javascript"></script>
 </head>
 <body>
  <div id="header">
   <img class="logo" width="100px" height="100px" src="<?php echo STORE_LOGO_IMG; ?>" />
   <h1><?php echo STORE_NAME; ?></h1>
  </div>
  <div id="navBox">
   <div class="boxlabel">Links</div>
   <ul>
    <li><a href="<?php echo href_link(FILE_ORDERS); ?>">Orders</a></li>
    <li><a href="<?php echo href_link(FILE_ITEMS); ?>">Items</a></li>
    <li><a href="<?php echo href_link(FILE_SPECIALS); ?>">Specials</a></li>
    <li><a href="<?php echo href_link(FILE_REPORTS); ?>">Reports</a></li>
    <li><a href="<?php echo href_link(FILE_LOGIN, array('action','logout') ); ?>">Log Out</a></li>
   </ul>
  </div>
  <div id="mainContent">
<?php
