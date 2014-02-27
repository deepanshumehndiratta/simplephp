<?php

    // The Primary Framework Class

    class Simple
    {
        
/*        
 *      Check Password to be 8-20 characters long,
 *      with at least one a-z/A-Z and containing
 *      at least one of 0-9 or special characters
 */
        
        function checkPassword ($passwd = null)
        {
            
            return preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[0-9]).*$#",
                              $passwd);
            
        }
        
        // Check a given email for validity
        
        final function checkEmail ($email = null)
        {
        
            return preg_match("/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $email);
            
        }
        
        // Check a given URL for validity
        
        final function checkURL ($url = null)
        {
            
            return preg_match("_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?_iuS", $url);
            
        }
        
        // Generate a random Number 
        
        final protected function randKey ()
        {
            
            return alphaId (md5 (uniqid (rand(), true)), true);
            
        }
        
        // Generate a random string
        
        final protected function randString ()
        {
            
            return alphaId (alphaId (md5 (uniqid (rand(), true)), true));
            
        }
        
        // Redirect a user to a location, inside or outside the app
        
        final protected function redirect ($target = null, $type = 0)
        {
            
            if (!$this->checkURL($target)) {
                
                global $config;
                $target = ($target == null) ? (((substr ($config['dir'], 0, 1)
                            != '/') ? '/' : null) . $config['dir'])
                    : ((substr ($target, 0, 1) != '/') ? $target
                        : ($config['dir']
                            . substr($target, 1, strlen($target) - 1)));
                
            }
            
            if ($type == 1)
                header ('HTTP/1.1 301 Moved Permanently');
                
            header ('Location: ' . $target);
            
            exit;
            
        }
        
/*        
 *      Redirect a user to a location, inside or outside
 *      the app after displaying a message
 */
        
        function flash ($target = null, $msg = null, $time = 3)
        {
            
            if (!$this->checkURL($target)) {
                
                global $config;
                $target = ($target == null) ? (((substr ($config['dir'], 0, 1)
                                != '/') ? '/' : null) . $config['dir'])
                    : ((substr ($target, 0, 1) != '/') ? $target
                        : ($config['dir']
                            . substr($target, 1, strlen($target) - 1)));
                
            }

            header("Refresh: " . $time . "; url=" . $target);
            print $msg;
            exit;
            
        }
        
        // Convert an array to an object
        
        final function to_obj ($array = array())
        {
            
            $object = new stdClass();
            
            foreach ($array as $key => $value)
            {
                
                if (!is_array ($value)) {
                    
                    $object->$key = $value;
                    
                } else {

                    $object->$key = call_user_func_array(
                                        array(
                                            $this,
                                            __FUNCTION__
                                        ),
                                        array ($value)
                                    );
                    
                }
            
            }
            
            return $object;
            
        }
        
        // Find table name for a Controller/Model
        
        final protected function getTable ($class)
        {
            
            $class = explode ('Controller', $class);
            $class = $class[0];
            
            $class = explode ('Model', $class);
            $class = $class[0];
            
            $class = strrev (lcfirst (strrev (lcfirst ($class))));
            
            $table = null;
            
            foreach (str_split ($class) as $c)
            {
                
                if ($c == strtoupper ($c)) {
                
                    $table .= '_';
                    
                }
                
                $table .= $c;
                
            }
            
            return strtolower ($table);
            
        }
        
        // Get Class Name from Table Name
        
        final protected function getClass ($table)
        {
            
            $class = implode (array_map ('ucfirst', explode ('_', $table)));
            
            return $class;
            
        }
        
    }
    
?>