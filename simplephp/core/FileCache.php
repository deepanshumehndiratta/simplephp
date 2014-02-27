<?php

    // Class for output caching

    class FileCache
    {
    
        private static $time;
        private static $methods;
        public static $dir;
        private static $all = false;
        private static $shutdown;
        
        // Set a private-static class Variable        
        public static function set ($what = null, $value = null)
        {
            
            self::$$what = $value;
            
        }
        
        // Cache all Output
        public static function all ()
        {
        
            self::$all = true;
            
        }
        
        // Check if cache for current request is enabled
        public static function check ($pair = array(), $test = false)
        {
            
            global $config;
            
            if ($config['mode'] == 2) {
                
                if (self::$all) {
                    
                    return true;
                    
                }
            
                if (isset(self::$methods[$pair['class']])) {
                
                    if (in_array($pair['func'],
                            self::$methods[$pair['class']])
                        ) {
                    
                        return true;
                    
                    }
                
                }
                
            }
            
            return false;
            
        }
        
        // Check if cache for current request is available
        private static function isCached ($arr = array())
        {
            
            ksort ($arr);
            $arr = md5 (serialize ($arr));
            
            if (file_exists (self::$dir . DS . $arr)) {
                if ((time() - filemtime(self::$dir . DS . $arr))
                    < self::$time
                ) {
                    
                    return true;
                    
                } else {
                    
                    unlink (self::$dir . DS . $arr);
                    
                }
                
            }
            return false;
            
        }
        
        // Cache output of current request
        public static function push ()
        {
            
            global $_parms, $isAjax;
            $arr = array (
                        'post' => $_parms['post'],
                        'get' => $_parms['get'],
                        'req_args' => $_parms['req_args'],
                        'ajax' => ($isAjax) ? 1 : 0
            );
            
            ksort ($arr);
            
            if (!self::isCached ($arr)) {
            
                $arr = md5 (serialize ($arr));
                
                $file = fopen (self::$dir . DS . $arr, 'w');
                fwrite ($file, ob_get_contents ());
                fclose ($file);
                
            }
            
        }
        
        // Pop Data from Cache for a request
        public static function pop ()
        {
            
            global $_parms, $isAjax;
            $arr = array (
                        'post' => $_parms['post'],
                        'get' => $_parms['get'],
                        'req_args' => $_parms['req_args'],
                        'ajax' => ($isAjax) ? 1 : 0
            );
            
            ksort ($arr);
            
            if (self::isCached ($arr)) {
            
                $arr = md5 (serialize ($arr));
                
                readfile (self::$dir . DS . $arr);
                exit;
                
            }
            
        }
    
    }
    
?>