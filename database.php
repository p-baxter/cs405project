<?php
/* 
 * database.php
 * 
 * This file connects to the database or outputs an error message.
 * 
 * Using this file allows the mysql login credentials to change without
 * needing to change dozens of files.
 * 
 * Pre-Conditions: 
 * This should only be included by other PHP scripts, which are executed
 * by user navigating to them.
 * 
 * Post-Conditions:
 * The connection to the database is successful and subsequent queries
 * use this connection. Upon error, a message is output.
 * 
 * Author: Matthew Denninghoff
 * Date: 4/11/2015
 * 
 */
// Connect to the database server using credentials or halt with an error
// message to the browser.
// Arguments: "hostname:tcpport", "username", "password".
$msdatalink = mysql_connect ("mysql.cs.uky.edu", "", "");

if ( ! $msdatalink) die ( "Couldn't connect to database" );

// Select the database to use, or halt with an error message to the browser.
if( ! mysql_select_db( "" ))
  die ( "Couldn't connect to database: ".mysql_error() );
