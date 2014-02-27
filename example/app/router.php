<?php
    
    /**
     * Bind Routes
    **/
    
    global $_parms;

    # Routing Examples
    
    Router::all (array ('/'), array ('class' => 'users'));
    
    Router::get (array ('/add'), array ('class' => 'users', 'func' => '_add'));
    Router::post (array ('/add'), array ('class' => 'users', 'func' => 'add'));
    
    # Router::all (array('/users/add'), array('class' => 'users', 'func' => 'render', 'params' => array('404')));
    
    # Router::all (array ('/users'), array ('class' => 'users', 'func' => 'render', 'params' => array('404')));
    
    # Router::all (array ('*'), array ('class' => 'users', 'func' => 'view'));

?>