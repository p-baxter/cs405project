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

// Connect to the database server using credentials or halt with an error
// message to the browser.
// Arguments: "hostname", "username", "password", "database", "port".
$mysqli = new mysqli('', '', '', '', 3306);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
