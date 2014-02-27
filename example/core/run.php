<?php
    
/*
 *  Set Error Handling Function
 */
                                    
    set_error_handler ('handleError');
    
/*
 *  Load Controllers, Models, Configuration and other files
 */
    
    include_all (CORE_PATH . 'vendors', true);
    // Include All core resources from Vendors
    
    requires (APP_FOLDER . DS . 'controllers' . DS . 'AppController.php');
    // Load Application Controller
    
    requires (APP_FOLDER . DS . 'models' . DS . 'AppModel.php');
    // Load Model Class
    
    
    include_all (APP_FOLDER . DS . 'config');
    // Include Basic Application Configuration and Database
    
    include_all (APP_FOLDER . DS . 'controllers');
    // Include all controllers
    
    include_all (APP_FOLDER . DS . 'models');
    // Include all models
    
    include_all ('plugins');
    // Include all plugins

/*
 *  Make global variables accessible
 */
    
    global $config, $database, $proxy, $tables, $isAjax, $db;
    
    !defined ('APP_NAME') ? define ('APP_NAME', $config['name']) : null;
    // Define Name of Application
    
/*
 *  Configure Directory in case application is not located in Webroot
 *  Might be Broken in shared hosting, in that case set 'dir' key in $config
 *  array directly, in app/config/basic.php
 */
    
    $config['dir'] = isset ($config['dir'])
                          ? (
                                ((substr ($config['dir'], 0, 1) != '/') ? '/' : null)
                                . (
                                    (substr ($config['dir'], strlen ($config['dir']) - 1, 1) == '/')
                                    ? (substr ($config['dir'], 0, strlen ($config['dir']) - 1)) : $config['dir']
                                  )
                            ) : get_path();
                            
    (!defined ('BASE_URL') ? define ('BASE_URL', $config['dir']) : null);
    
/*
 *  Check PHP version
 */
    
    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
        
        trigger_error('This Application only runs on PHP >= 5.3.0'
                      , E_USER_ERROR);
        
    }
    
    $db = false;
    
    $request = urldecode ($_SERVER['REQUEST_URI']);
    // Get requested URL
    $referrer = isset ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    // Get referring URL
    
/* 
 *   Print Error message and exit application in case connection to MySQL Host is unsuccessful.
 *            
 *  
 *  function write_mysql_error()
 *  {
 *      
 *      logger ('Could not connect to MySQL Host', 'Error'); // Log error
 *      trigger_error ('Unable to connect to MySQL Host, application will now exit');
 *      // Generate Error Message
 *      
 *  }
 *
 */ 
    // Connect to MySQL Host
    
    Model::$con = mysqli_connect($database[$config['mode']]['host'],
                          $database[$config['mode']]['user'],
                          $database[$config['mode']]['passwd'],
                          $database[$config['mode']]['database']
                          );
    
    logger ('Connection to MySQL Host successful.');
        
    // Connect to Database
    
    if (!Model::$con) {
        
        logger ('Could not connect to Database', 'Error'); // Log error
        trigger_error ('Could not connect to Database', E_USER_ERROR);
        // Generate Error Message
        
    } else {
        
        logger ('Database Connection successful.'); // Log Event
        $db = true; // Set Global Database connection notifier variable = true
        
/*
 *      Fetch Table names from database
 */
        
        $sql = 'SHOW TABLES FROM `' . $database[$config['mode']]['database']
                . '`';
        $query = mysqli_query (Model::$con, $sql);
        
        while ((list ($table) = mysqli_fetch_row ($query)))
        {
            
            array_push ($tables, $table);
            
        }
        
        mysqli_free_result($query);
        
    }
    
    global $_parms; // Globalize the Parameters for the request
    
    $_parms = array(
                        'req' => $request,
                        'ref' => $referrer,
                        'req_args' => getArgs ($request, true),
                        'ref_args' => getArgs ($referrer),
                        'post' => array_map ('secure', $_POST),
                        'get' => array_map ('secure', $_GET),
                        'files' => $_FILES,
                        'html5' => ((doHTML5()) ? doHTML5() : 0)
                   );
    
    // Set Error Reporting
    
    error_reporting ($config['error_reporting'][$config['mode']]);
    
    // Create temp Directory if not exists
    
    if (!is_dir (BASE_PATH . APP_FOLDER . DS . 'tmp')) {
        
        mkdir (BASE_PATH . APP_FOLDER . DS . 'tmp');
    
    }
    
    // Create Cache Directory if not exists
    
    if (!is_dir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'cache')) {
        
        mkdir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'cache');
    
    }
    
    // Create Sessions Directory if not exists
    
    if (!is_dir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'sessions')) {
        
        mkdir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'sessions');
    
    }
    
    // Create Schema Directory if not exists
    
    if (!is_dir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'schema')) {
        
        mkdir (BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'schema');
    
    }
    
    global $tmp;
    
    $tmp = BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . md5 (time() . mt_rand());
    
    if ($config['mode'] < 2) {
        
        fclose (fopen ($tmp, 'w'));
        fclose (fopen ($tmp . '_mem', 'w'));
        
    }
    
    global $proxy;
    
/*
 *  Set Proxy for connection
 */
    
    if ($proxy['type']) {

        $auth = base64_encode (!empty ($proxy['username'])
                ? ($proxy['username'] . ':' . $proxy['password']) : null);

        $aContext = array(
                        'http' => array(
                            'proxy' => 'tcp://' . $proxy['host']
                                        . ':' . $proxy['port'],
                            'request_fulluri' => true,
                            'header' => "Proxy-Authorization: Basic $auth",
                       ),
                   );

        stream_context_set_default ($aContext);
        
    }
    
/*
 *  Check if cURL extension has been loaded
 */
    
    if ($config['curl'] && !in_array  ('curl', get_loaded_extensions())) {

        trigger_error ('cURL extension could not be loaded');
		
    }
	
    // Turn on Output Buffering
	
    ob_start();
    
/*
 *  Check if request is made via Ajax
 */
    
    if ($isAjax && $config['scribe']) { // Send Request to Ajax handler (Scribe)
                                        
        requires (APP_FOLDER . DS . 'scribe.php');
        
        $routes = new scribe;
        
    } else {
    
        // Forward the request to the router
                                        
        requires (APP_FOLDER . DS . 'router.php');
    
        $routes = new router;
        
    }

/*
 *  Fire-up the App here
 */
    
    $routes->init(); // Handle Request

?>