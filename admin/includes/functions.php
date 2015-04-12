<?php
/* 
 * functions.php
 * 
 * Useful functions for all files.
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

/**
 * Redirect the user to the specified page on this site.
 * The redirect URL becomes like: http://host/baseDir/$page.
 * 
 * @param string $page
 */
function http_redirect($page = '')
{
    header('Location: '.SITE_BASE_URL.$page);
    exit;
}
// end http_redirect().

/**
 * Returns a complete URL to the given page.
 * Optional arguments should be key, value pairs.
 * 
 * @param string $page
 * @param array $arguments
 * @return string
 */
function href_link($page, $arguments = null )
{
    $args = '';
    if(is_array($arguments) )
    {
        foreach( $arguments as $key => $val )
        {
            $args .= '&'.$key . '=' . $val;
        }
        
        // Remove the first '&' symbol and start with a '?'.
        $args = '?'. substr($args, 1);
    }
    
    return SITE_BASE_URL . $page . $args;
}
// end href_link().


/**
 * Log out the user.
 * 
 * Code from: http://php.net/manual/en/function.session-destroy.php
 * 
 */
function logout_user()
{
    // Unset all of the session variables.
    $_SESSION = array();
    
    if( ini_get('session.use_cookies'))
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly'] );
    }
    
    session_destroy();
}
// end logout_user().
