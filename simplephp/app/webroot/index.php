<?php

    !defined ('DS') ? define ('DS', DIRECTORY_SEPARATOR) : null;
    !defined ('CORE_PATH') ? define ('CORE_PATH', rtrim (dirname (dirname (dirname (__FILE__))), '/\\') . DS . 'core' . DS) : null;
    !defined ('BASE_PATH') ? define ('BASE_PATH', rtrim (dirname (dirname (dirname (__FILE__))), '/\\') . DS) : null;
    !defined ('APP_FOLDER') ? define ('APP_FOLDER', 'app') : null;

    if (!defined('RW'))
    {
        $request = strtok(urldecode ($_SERVER['REQUEST_URI']), '?');
        
        if ($request == str_replace('\\', '/', substr (__FILE__, strlen(BASE_PATH) - 1)))
        {
            print 'Direct Script Access DENIED.';
            die();
        }
    }

    /**
     * Request Handler
    **/
    
    global $t1; // Set $t1 to global
    
    $t1 = microtime (true);
    
    // Check if Core exists
    if (file_exists ( CORE_PATH . 'base.php')) {
        
        require_once (CORE_PATH . 'base.php'); // Load Core
        
    }
    else
    {
        
        trigger_error ('Unable to load core'); // Exit in case Core is absent
        exit;
        
    }
    
    /**
     * All Files loaded, time to fire-up the app
    */
    
    requires (CORE_PATH . 'run.php', true);

?>