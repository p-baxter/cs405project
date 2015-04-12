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

require('./includes/constants.php');
require('./includes/functions.php');
require('./includes/database.php');

require('./includes/class-dbentity.php');
require('./includes/class-staff.php');

// (Certain installations of PHP print warnings if default timezone is not set.)
date_default_timezone_set(TIMEZONE_DEFAULT);

session_start();

//
// Detect if the user is already logged in.
// If so, then redirect them to the index.
//
if(isset($_SESSION[SESSION_ID_KEY]))
{
    $staff = new Staff();
    if( $staff->init_by_sessionId($_SESSION[SESSION_ID_KEY]) )
    {
//        http_redirect(FILE_INDEX);
    }
}
// done redirecting already logged-in user.

$errors = array();

if( isset($_GET['action']))
{
    switch($_GET['action'])
    {
        case 'login':
            // Verify the user's credentials.
            
            if(! isset($_POST['staffId']) )
            {
                $errors[] = 'Staff ID missing';
                break;
            }
            
            if( ! isset($_POST['pass']))
            {
                $errors[] = 'Staff ID missing';
                break;
            }

            $staff = new Staff();
            
            if( ! $staff->init_by_key($_POST['staffId']))
            {
                $errors[] = 'Staff ID not found';
                break;
            }

//            if( $staff->getKeyValue() == null)
//            {
//                $errors[] = 'Staff ID not found';
//                break;
//            }
            
//            die(password_hash($_POST['pass']));
            
            if( ! password_verify($_POST['pass'], $staff->password))
            {
                $errors[] = 'Invalid Password';
                break;
            }
            
            $_SESSION[SESSION_ID_KEY] = session_id();
            
            $staff->sessionId = $_SESSION[SESSION_ID_KEY];
            if( ! $staff->db_update() )
            {
                $errors[] = 'Failed to update database: '. $mysqli->error;
                break;
            }
            
//            die(print_r($_SESSION,true));
            
            // The credentials were good, so send them to the index page.
            http_redirect(FILE_INDEX);
            
            break;
        
        case 'logout':
            logout_user();
            break;
    }
    // end switch _GET[action]
}
// end if( isset($_GET['action'])).

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
     #mainContent { position: absolute;
     top: 120px;
     left: 0px;
     }
    </style>
 </head>
 <body>
  <div id="header">
   <img class="logo" width="100px" height="100px" src="<?php echo STORE_LOGO_IMG; ?>" />
   <?php echo STORE_NAME; ?>
  </div>
  <div id="mainContent">
<?php

if( isset($_GET['action']) && $_GET['action'] == 'logout')
{
    echo 'Logged out';
}

if( count($errors) > 0 )
{
    echo '<pre>';
    
    foreach( $errors as $msg )
    {
        echo $msg . "\n";
    }
    
    echo '</pre>';
}

?>
   <form action="login.php?action=login" method="POST">
   Staff ID <input type="text" name="staffId" value="<?php echo isset($_POST['staffId']) ? $_POST['staffId'] : ''; ?>" /><br/>
   Password <input type="password" name="pass" value="" /><br/>
   <input type="submit" value="Submit" />
   </form>
   
<?php

if( isset($_GET['pass']))
    echo password_hash($_GET['pass']);


include './footer.php';
