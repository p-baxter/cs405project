<?php
/* 
 * Filename: application_top.php
 * 
 * 
 * Include files that will be used with all admin pages.
 * database.php establishes a mysql connection or dies.
 * 
 * Verify that a user is logged in, or redirect them to the login page.
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

// Note: when application_top.php is included from a script in /admin/,
// then the path is /admin/, and not /admin/includes/, even though application_top
// resides in includes/.

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

// Fetch the desired URL before the redirect.
$redirect_nice = '?page='.basename($_SERVER['SCRIPT_NAME']);

// Redirect if session ID is not registered.
// Note: the script stops running upon running http_redirect().
if( ! isset($_SESSION[SESSION_ID_KEY]) )
{
    http_redirect(FILENAME_LOGIN . $redirect_nice);
}

// Search for a staff with the session ID.
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
    http_redirect(FILENAME_LOGIN . $redirect_nice );
}
//
// done verifying that user is logged in.
//
