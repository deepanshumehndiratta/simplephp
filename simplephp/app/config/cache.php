<?php

    // Set caching duration
    FileCache::set ('time', 3600);
    
    // Cache all output
    FileCache::all();
    
    // Set Cached Controller-method outputs
    /*
    FileCache::set ('methods',
                    array(
                        'users' => array ('view', 'render', 'index')
                    )
    );
    */

?>