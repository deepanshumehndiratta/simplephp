<?php

    class cache
    {
    
        private static $memObj;
        private $mem;
        private static $queries = array();
        private static $profiler = array();
        
        function __construct()
        {
        
            if ($this->check()) {
                
                if (class_exists ('Memcached')) {
                
                    global $config; // Make Configuration available
            
                    self::$memObj = new Memcached;
                    // Create object of Memcached class
            
                    $this->salt = isset ($config['cache']['salt'])
                                    ? $config['cache']['salt'] : null;
                    // Set salt for memcached
            
                    $this->mem = &self::$memObj;
                    // Point mem property to memcahed object
                    
                    foreach ($config['cache']['servers'] as $server)
                    {
                        
                        $this->connect ($server['host'], $server['port']);
                        // Establish a connection with the memcached server
                        
                    }
            
                } else {
                    
                    logger('Memcached extension not available');
                    trigger_error('Memcached extension not available');
                    
                }
                
            }
        
        }
        
        // Clear all cached queries for current request
        private function clearSession()
        {
            
            foreach (self::$queries as $query)
            {
            
                $this->mem->delete ($query);
                
            }
            
        }
        
        // Check if an object is cached in memory
        function check()
        {
        
            global $config;
            
            return (isset ($config['cache']['enabled'])
                    && $config['cache']['enabled']);
        
        }
        
        // Connect to Memcached Servers
        private function connect ($host = 'localhost', $port = 11211)
        {
        
            if ($this->check()) {
            
                if (!$this->mem->addServer($host, $port)) {
                    logger ('Unable to connect to Memcached host', 'Error');
                    trigger_error ('Unable to connect to Memcached host');
                
                } else {
                
                    logger ('Connection to Memcached Server successful.');
                
                }
            
            }
        
        }
        
        // Add an object to the Cache
        function put ($query = null, $result = array())
        {
            
            if ($this->check()) {
            
                if ($this->mem->get (md5($this->salt . $query))) {
                
                    $this->mem->delete (md5($this->salt . $query));
                
                }
            
                if (!$this->mem->set (md5($this->salt . $query), $result)) {
                    logger ('Unable to access Memcached Daemon', 'Error');
                    trigger_error ('Unable to access Memcached Daemon');
                    
                } else {
                    
                    array_push (self::$queries, md5($this->salt . $query));
                    mem_profile ($query, sizeofvar($result), 'PUT');
                    
                }
                
            }
            
        }
        
        // Retrieve an object from the Cache
        function get ($query = null)
        {
            
            if ($this->check()) {
                
                if (($result = $this->mem->get (md5($this->salt . $query)))) {
                    
                    mem_profile ($query, sizeofvar($result), 'FETCH');
                    return $result;
                    
                }
            
            } else {
                
                return false;
                
            }
    
        }
        
        function __destruct()
        {
            
            global $config;
            
            if (isset($config['cache']['exitFlush'])
                && $config['cache']['exitFlush']
            ) {
                
                $this->clearSession();
                
            }
            
        }
    
    }
    
?>