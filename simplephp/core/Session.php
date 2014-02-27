<?php

    // Session handling class

    class Session
    {
        
        private $id = null;
        
        // Set Session storage Path and begin/resume Session
        final function __construct ()
        {
            
            // Set default session storage path
            session_save_path(BASE_PATH . APP_FOLDER . DS
                              . 'tmp' . DS . 'sessions');
            ini_set('session.save_path', BASE_PATH . APP_FOLDER . DS
                    . 'tmp' . DS . 'sessions');
            ini_set('session.gc_probability', 1);
            $this->begin();
            
        }    
        
        // Set or return Session ID
        final function id ($id = null)
        {
            
            if (!$id)
                return $this->id;
            else {
                
                $this->end();
                session_id($id);
                $this->begin();
                $this->id = $id;
                
            }
        
        }
        
        // Set Flash message for redirection
        final function setFlash ($msg)
        {
            
            $this->begin();
            $_SESSION['__flash'] = $msg;
            
        }
        
        // Get Session Flash message
        final function flash()
        {

            return $this->get('__flash');
            
        }
        
        // Begin session
        final function begin()
        {
            
            if (!isset ($_SESSION))
                session_start();
                
            $this->id = session_id();
            
        }
        
        // Check if session exists
        final function check()
        {
            
            return isset ($_SESSION);
            
        }
        
        // Close a session
        final function end()
        {
            $this->begin();
            session_unset();
            session_destroy();
            
        }
        
        // Set a Session Variable
        final function set ($var = 0, $val = null)
        {
            
            $this->begin();
            $_SESSION[$var] = $val;
            
        }
        
        // Unset a session variable
        final function remove ($var = 0)
        {
            
            $this->begin();
            $_SESSION[$var] = null;
            unset ($_SESSION[$var]);
            
        }
        
        // Fetch a session variable, return false if not set
        final function get ($var = 0)
        {
            
            $this->begin();
            return (isset ($_SESSION[$var]) ? $_SESSION[$var] : false);
            
        }
    
    }

?>