<?php

    // Set default timezone
    date_default_timezone_set ('UTC');
    
    // Get temp file for request
    
    global $tmp, $error_caught;
    
    $error_caught = false;
    
    /**
     * Run Shutdown Functions
    **/
    
    register_shutdown_function ('print_profile');

    // Global Variable Declaration
    
    global $database, $config, $isAjax, $tables;
    
    $tables = array();
    
    $profile = array();
    
    // Create temp files if in testing or development mode
    
    /**
     * Check if request is made with Ajax
    **/
    
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    /**
     * Array of loaded files
    **/
    
    $loaded_files = array();
    
    /**
     * Database Interaction Functions
    **/
    
    function db_close()
    {
        
        global $database, $config;
        
        if (isset ($database[$config['mode']]['persistent'])
            && !$database[$config['mode']]['persistent']
        ) {
            
            global $timer;
            
            $timer = setTimeout ('db_exit', 1000000);
            
        }
        
    }
    
    function db_exit()
    {
        
        global $db;
        
        mysql_close();
        
        $db = false;
        
    }
    
    function db_reconn()
    {
        
        global $database, $config;
        
        if (isset ($database[$config['mode']]['persistent'])
            && !$database[$config['mode']]['persistent']
        ) {
            
            global $timer, $db;
            
            if (isset ($timer) && $timer != null) {
                
                clearTimeout ($timer);
                
            }
            
            if (!$db) {
                
                Model::$con = mysqli_connect($database[$config['mode']]['host'],
                          $database[$config['mode']]['user'],
                          $database[$config['mode']]['passwd'],
                          $database[$config['mode']]['database']
                          );
                
            }
            
        }
        
    }
    
    /**
     * Error Handling Function
    **/
    
    function handleError ($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        
        // error was suppressed with the @-operator
        if (0 === error_reporting() && $level != E_USER_ERROR) {

            return false;
        
        }
        
        global $exception, $message, $error_caught;
        
        $error_caught = true;
        
        $exception =  new ErrorException ($errstr, 0, $errno, $errfile, $errline);
        
        $message = $exception->getMessage() . ' in ' . $errfile
                   . ' on line ' . $errline;
        
        requires (CORE_PATH . 'exception/index.php', true);
        
        exit;
        
    }
    
    /**
     * Database Profiler
    **/
    
    function profile ($query = null, $status = false, $rows = 0, $cached = false)
    {
        
        global $tmp, $config;
        
        if ($config['mode'] < 2) {

            $profile = profiler_get();
        
            array_push ($profile,
                            array ('query' => $query,
                                   'status' => ($status) ? 1 : 0,
                                   'rows' => $rows,
                                   'cached' => ($cached) ? 1 : 0
                            )
                        );
        
            file_put_contents ($tmp, serialize ($profile));
        
            unset ($profile);
            
        }
                
    }
    
    /**
     * Get variable Size
    **/    
    
    function sizeofvar ($var)
    {
        
        if (is_array ($var)) {
            
            return call_user_func (__FUNCTION__, serialize ($var));
            
        } else if (is_object ($var)) {
            
            return call_user_func (__FUNCTION__, serialize ((array) $var));
            
        }
        
        return strlen ($var);
        
    }

    
    /**
     * Memcached Profiler
    **/
    
    function mem_profile ($query = null, $size = 0, $action = null)
    {
        
        global $tmp, $config;
        
        if ($config['mode'] < 2) {
        
            $profile = mem_profiler_get();
        
            array_push ($profile, array('query' => $query,
                                        'size' => $size,
                                        'action' => $action));
        
            file_put_contents ($tmp . '_mem', serialize ($profile));
        
            unset ($profile);
            
        }
                
    }
    
    /**
     * Fetch Mem Profile
    */
    
    function mem_profiler_get()
    {
        
        global $tmp, $config;
        
        if ($config['mode'] < 2) {
        
            if (! empty($tmp) && file_exists ($tmp . '_mem')) {
                
                $profile = unserialize (file_get_contents ($tmp . '_mem'));
                
            } else {
                
                $profile = array();
                
            }
        
            return is_array ($profile) ? $profile : array();
            
        }
        
    }
    
    /**
     * Fetch Profile
    */
    
    function profiler_get()
    {
        
        global $tmp, $config;
        
        if ($config['mode'] < 2) {
            
            if (! empty($tmp) && file_exists ($tmp)) {
                
                $profile = unserialize (file_get_contents ($tmp));
                
            }
            else {
                
                $profile = array();
                
            }
        
            return is_array ($profile) ? $profile : array();
            
        }
        
    }
    
    /**
     * Print Profile
    **/
    
    function print_profile()
    {
        
        // Run all router-registered shutdown functions 
        Controller::runShutdown();
        
        /**
         * Print Maximum Memmory Usage[In testing and Development Phase]
        */
        
        global $config, $t1, $profile, $mem_profile, $tmp, $isAjax, $error_caught;
    
        if ($config['mode'] < 2) {
            
            $profile = profiler_get();
            $mem_profile = mem_profiler_get();
            
            if (!$isAjax) {
    
                print "<hr>\n<pre>Maximum Memmory Usage: "
                        . memory_get_peak_usage() / (1024 * 1024)
                        . ' MB | Execution time: '
                        . (microtime (true) - $t1) . " s</pre>\n";
        
                requires (CORE_PATH . 'profiler/index.php', true);
            
            } else {
                
                if ($config['ajaxProfiling']) {
                    
                    if (!$error_caught) {
                
                        $arr = (array) json_decode (ob_get_contents());
                    
                        $arr['SimplePHP'] = array (
                            'max_mem' => memory_get_peak_usage() / (1024 * 1024)
                                            . ' MB',
                            'exec_time' => (microtime (true) - $t1),
                            'profiler' => $profile,
                            'mem_profiler' => $mem_profile
                        );
                
                        ob_clean();
                
                        print (json_encode ($arr));
                        
                    } else {
                        
                        print "<hr>\n<pre>Maximum Memmory Usage: "
                                . memory_get_peak_usage() / (1024 * 1024)
                                . ' MB | Execution time: '
                                . (microtime (true) - $t1) . " s</pre>\n";
        
                        requires ('core/profiler/index.php');
                        
                    }
                    
                }
                
            }
            
            if (file_exists ($tmp))
                unlink ($tmp);
    
            if (file_exists ($tmp . '_mem'))
                unlink ($tmp . '_mem');
            
            exit;
        
        }
        
    }

    /**
     * Logging Function
    **/
    
    function logger ($message, $type = null)
    {
        
        $message = (($type) ? $type : 'Notice') . ' :: ' . $message;
        
        if (file_exists (BASE_PATH . APP_FOLDER . DS . 'log.txt')
            && @filesize (rtrim(dirname(dirname(__FILE__)), '/\\') . DS
                . 'log.txt') * .0000009765625 >= 2.048
        )
            unlink (BASE_PATH . APP_FOLDER . DS . 'log.txt');
            
        if (!file_exists (BASE_PATH . APP_FOLDER . DS . 'log.txt')) {
            //Create Log file if does not exist
        
            $logFile = fopen (BASE_PATH . APP_FOLDER . DS . 'log.txt', 'w')
                or die ('Unexpected Server Error');
            fclose ($logFile);
            
        }
        
        $logFile = fopen (BASE_PATH . APP_FOLDER . DS . 'log.txt', 'a')
            or die ('Unexpected Server Error'); //Log Event to File
        fwrite ($logFile, "\n"  . $message);
        fclose ($logFile);

    }
    
    /**
     * Includes required class
    **/

    function requires ($files, $abs = false)
    {
        
        if (is_array($files)) {
            
            foreach ($files as $file)
            {
                
                call_user_func_array (__FUNCTION__, array($file, $abs));
                
            }
            
        } else {
            
            $filepath = ($abs) ? $files : BASE_PATH . $files;
            
            if (file_exists($filepath)) {
                //Check against base path if file exists
                
                if (!in_array($filepath, $GLOBALS['loaded_files'])) {
                    
                    require_once ($filepath); //Loads File
                    logger('Loaded file: ' . $filepath . ' | Requested on '
                        . date (DATE_RFC822) . ' via ' . $_SERVER['HTTP_USER_AGENT']
                        . ' | Request IP: ' . $_SERVER['REMOTE_ADDR']);
                    // Logs event
                    
                    array_push ($GLOBALS['loaded_files'], $filepath);
                
                }
                
            } else {

                logger('Unable to load file: ' . $filepath . ' | Requested on '
                    . date (DATE_RFC822) . ' via ' . $_SERVER['HTTP_USER_AGENT']
                    . ' | Request IP: ' . $_SERVER['REMOTE_ADDR'], 'Error');
                //Logs event
                trigger_error ('Unable to load file: ' . $filepath);
                // Generate Error Message

            }
        
        }
        
    }
    
    /**
     * Get the calling class for a method
    **/
    
    function get_calling_class()
    {

       //get the trace
       $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++)
        {
            
            if (isset($trace[$i])) { // is it set?
             
             if ($class != $trace[$i]['class']) { // is it a different class
                 
                 return $trace[$i]['class'];
                 
             }
             
         }
         
        }
        
    }
    
    /**
     * Get Application Base Path
    **/
    
    function get_path()
    {
        
        if (!defined('RW')) {
            
            $path = str_replace ('\\', '/', 
                        substr ($_SERVER['SCRIPT_NAME'], 0,
                            strlen ($_SERVER['SCRIPT_NAME'])
                            - strlen ('/' . APP_FOLDER . '/webroot/index.php')
                        )
                    );
                                        
        } else {

            $path = str_replace ('\\', '/', $_SERVER['SCRIPT_NAME']);
            
        }
        
        return ((substr ($path, 0, 1) != '/' ? '/' : null) . $path
                . ((strlen ($path) > 0) ? 
                (substr ($path, strlen ($path), 1) != '/' ? '/' : null) : null));

    }
    
    /**
     * Auto-Load all PHP files in the directory
    **/
    
    function include_all ($folder, $abs = false)
    {
        
        $folder = ($abs) ? $folder : (BASE_PATH . $folder);
        $requires = array();
        
          if ($handle = opendir ($folder)) {

              /* This is the correct way to loop over the directory. */
           while (false !== ($entry = readdir ($handle)))
           {
                
                if (!is_dir ($folder . DS . $entry)) {
           
                    $ext = pathinfo ($entry, PATHINFO_EXTENSION);

                    if ($ext == 'php')
                        array_push ($requires, $folder . DS . $entry);
                        
                } else {
                    
                    if (!in_array ($entry, array ('.', '..'))) {
                    
                        call_user_func_array(__FUNCTION__,
                                            array($folder . DS . $entry, true)
                                            );
                        
                    }
                    
                }
                            
            }
            
            requires ($requires, true);
       
        } else {

            logger ('Unable to access Folder', 'Error');
            trigger_error ('Unable to access Folder');
            
        }
        
    }
    
    /**
     * Get Browser name and version from HTTP_USER_AGENT
    **/
    
    function browser()
    {
       
       $ua = strtolower ($_SERVER['HTTP_USER_AGENT']);
       
       if (preg_match('/(chromium)[\/]([\w.]+)/', $ua))
            $browser = 'chromium';
        else if (preg_match ('/(chrome)[\/]([\w.]+)/', $ua))
            $browser = 'chrome';
        else if (preg_match ('/(safari)[\/]([\w.]+)/', $ua))
             $browser = 'safari';
        else if (preg_match ('/(opera)[\/]([\w.]+)/', $ua))
            $browser = 'opera';
        else if (preg_match ('/(msie)[\/]([\w.]+)/', $ua))
            $browser = 'msie';
        else if (preg_match ('/(mozilla)[\/]([\w.]+)/', $ua))
            $browser = 'mozilla';

       preg_match ('/(' . $browser . ')[\/]([\w]+)/', $ua, $version);

        return array ('name' => $browser, 'version'=> $version[2]);
    }
    
    /**
     * Check for HTML5 Compatibility for different browser versions
    **/
    
    function doHTML5()
    {
        
        $browser = browser();
        $do = false;
        
        switch ($browser['name']) {
            
            case 'mozilla':
                if ($browser['version'] > 3.6)
                    $do = true;
                    break;
            case 'chrome':
                if ($browser['version'] > 4)
                    $do = true;
                    break;
            case 'chromium':
                if ($browser['version'] > 18)
                    $do = true;
                    break;
            case 'msie':
                if ($browser['version'] > 9)
                    $do = true;
                    break;
            case 'safari':
                if ($browser['version'] > 4)
                    $do = true;
                    break;
            case 'opera':
                if ($browser['version'] >= 11.5)
                    $do = true;
                    break;
            default:
                $do = false;
                break;
        }
        
        return $do;
        
    }
    
    /**
     * Get Arguments from URL in an array
    **/
    
    function getArgs ($request, $root = false)
    {
        
        if ($root) {
            
            global $config;
            
            $dir = (((substr ($config['dir'], 0, 1) != '/') ? '/' : null)
                         . ((substr ($config['dir'],
                            strlen ($config['dir']) - 1, 1) == '/')
                         ? substr ($config['dir'], 0,
                            strlen ($config['dir']) - 1) : $config['dir'])
                      );
            
            $request =  (substr ($request, 0, strlen ($dir)) == $dir)
                          ? substr ($request, strlen ($dir) , strlen ($request))
                          : $request;
        
        }
    
        $request = strtok ($request, '?'); // Remove the GET Part of URL
        $args = str_split ($request); // Split the URL into array of characters
        
        if ($args[0] == '/') {

            unset ($args[0]);
            
        }
        
        $args = str_split (implode ($args));
        
        if ($args[sizeof ($args) - 1] == '/') {

            unset ($args[sizeof ($args) - 1]);
            
        }
            
        $args = explode ('/', implode ($args));
        
        /*
        for ($i = 0; $i < sizeof ($args) ; $i++)
        {
            
            if ($args[$i] == 'index.php' || $args[$i]=='index.htm'
                || $args[$i]=='index.html' || $args[$i]=='index.py'
            ) {
                //index.{php,html,htm,py} are removed
                
                unset ($args[$i]);
                $args = implode ('/', $args);
                $args = explode ('/', $args);
            
            }
        
        }
        */
        
        if (sizeof ($args) == 1 && empty ($args[0])) {
            
            unset ($args[0]);
            
        }
        
        return $args; //Return array of arguments
        
    }
    
    /**
     * Sanatizer Function
    **/
    
    function secure ($element)
    {
        
        if (is_array ($element)):
        
            $element = array_map ('secure', $element);
            
        else:
        
                $element = strip_tags ($element); 
                $element = htmlspecialchars ($element);
                $element = trim ($element); 
                $element = stripslashes ($element); 
                $element = mysql_real_escape_string ($element);

        endif;

        return $element; 

    }
    
    function is_assoc ($arr)
    {
            
        return (is_array ($arr) &&
                   count (array_filter (array_keys ($arr), 'is_string'))
                   == count ($arr)
               );
        
    }
    
    /**
     * Return a key from an array
    **/
    
    function get_key ($arr = array(), $key = null)
    { // Return a Key From an Array
        
        $arr = (array) $arr;
        
        if (isset ($arr[$key])):
        
            return $arr[$key];
            
        else:
        
            return null;
            
        endif;
        
    }
    
    /**
     * Link a file in view from webroot directory
    **/
    
    function load ($type = null, $file)
    {
        
        global $config;
        
        if (!defined('RW')) {
        
            return $config['dir'] . $type . 
                ((substr ($type, strlen ($type) - 1, 1) != '/') ? '/' : null)
                . $file;
            
        }
        
        return substr($config['dir'], 0,
                strlen($config['dir']) - strlen('index.php') - 1)
                . 'app/webroot/' . $type
                . ((substr ($type, strlen ($type) - 1, 1) != '/') ? '/' : null)
                . $file;
        
    }
    
    // Load Framework Base
    
    requires (CORE_PATH . 'Simple.php', true);
    
    // Load Controller
    
    requires (CORE_PATH . 'Controller.php', true);
    
    // Load Model
    
    requires (CORE_PATH . 'Model.php', true);
    
    // Load Router
    
    requires (CORE_PATH . 'Router.php', true);
    
    // Load Scribe
    
    requires (CORE_PATH . 'Scribe.php', true);
    
    // Load Session
    
    requires (CORE_PATH . 'Session.php', true);
    
    // Load Cache
    
    requires (CORE_PATH . 'Cache.php', true);
    
    // Load FileCache
    
    requires (CORE_PATH . 'FileCache.php', true);
    FileCache::$dir = BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'cache';

?>