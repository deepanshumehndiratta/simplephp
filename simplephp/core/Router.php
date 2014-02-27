<?php

    // Router Class to handle requests
    
    class router extends Controller
    {
        
        private static $get = array();
        private static $post = array();
        private static $put = array();
        private static $delete = array();
        
        function init()
        {
            
            global $var4View;
            $var4View['req'] = strtolower ($_SERVER['REQUEST_METHOD']);
    
            global $config;
            
/*            
 *          Access static pages in views/pages
 *          via http://$_SERVER['HTTP_HOST']/?route=filename
 */
            
            if (isset ($this->args['get']['route'])
                && !empty ($this->args['get']['route'])
                && !count ($this->args['req_args'])
            ) {
                
                // Make absolute file path
                $filename = rtrim (dirname (dirname (__FILE__)), '/\\')
                            . DS . 'app' . DS . 'views' . DS . '_pages'
                            . DS . $this->args['get']['route'] . '.php';
                
                if (file_exists ($filename)) // Check if file exists
                {
                    
                    $GLOBALS['var4View']['config'] = $GLOBALS['config'];
                    $GLOBALS['var4View']['db'] = $GLOBALS['db'];
                    
                    $this->requires ('app' . DS . 'views' . DS . '_pages' . DS
                                    . $this->args['get']['route'] . '.php', true
                                    );
                    
                } else {
                    
                    $this->render ('404');  // Renders 404 Error Page
                    
                }
                
            } else {
                
                if (in_array (strtolower ($_SERVER['REQUEST_METHOD']),
                        array ('get', 'put', 'delete', 'post'))
                ) {
                    
                    $method = strtolower ($_SERVER['REQUEST_METHOD']);
                    
                    $count = count ($this->args['req_args']);
                    
                    $runn = false;
                    
                    $arr = null;
                    
                    $backUp = null;
                    
                    foreach (self::${$method} as $get)
                    {
                        
                        if ($get['args'] == -1) {
                        
                            $backUp = $get['func'];
                            
                        }
                        
                        if ($get['args'] == $count) {
                            
                            $i = 0;
                            
                            $yes = true;
                            
                            foreach ($get['pattern'] as $pattern)
                            {
                                
                                if (in_array ($pattern['type'],
                                        array (1, 2, 3, 4, 5))
                                ) {
                                    
                                    switch ($pattern['type'])
                                    {
                                        
                                        case 1:
                                            
                                            if (!ctype_digit($this->args[
                                                        'req_args'
                                                    ][$i])
                                            ) {
                                                
                                                $yes = false;
                                                break;
                                                
                                            }
                                            break;
                                            
                                        case 2: 
                                            
                                            if (!preg_match ('/[A-Za-z]/i',
                                                    $this->args['req_args'][$i])
                                            ) {
                                                
                                                $yes = false;
                                                break;
                                                
                                            }
                                            break;
                                            
                                        case 3: 
                                            
                                            if (!ctype_alnum($this->args[
                                                        'req_args'
                                                    ][$i])
                                            ) {
                                                
                                                $yes = false;                                                
                                                break;
                                                
                                            }
                                            break;
                                            
                                        case 5: 
                                            
                                            if (!preg_match ($pattern['val'],
                                                    $this->args['req_args'][$i])
                                            ) {
                                                
                                                $yes = false;                                                
                                                break;
                                                
                                            }
                                            break;
                                        
                                    }
                                    
                                } else {
                                    
                                    if ($pattern['val']
                                        != $this->args['req_args'][$i]
                                    ) {
                                        
                                        $yes = false;                                        
                                        break;
                                        
                                    }
                                    
                                }
                                
                                $i++;
                                
                            }
                            
                            if ($yes) {
                                
                                $runn = true;
                                $arr = $get['func'];
                                
                            }
                            
                        }
                        
                    }
                    
                    if (!$runn) {
                        
                        if ($backUp != null) {
                            
                            $runn = true;
                            $arr = $backUp;
                            
                        }
                        
                    }
                        
                    if ($runn) {
                        
                        $pass = null;
                        
                        if (is_array ($arr)) {
                            
                            if (isset ($this->$arr['class'])
                                && is_object ($this->$arr['class'])
                            ) {
                                
                                if (FileCache::check(array(
                                        'class' => $arr['class'],
                                        'func' => $arr['func']))
                                ) {
                                    
                                    FileCache::pop();
                                    
                                    Controller::shutdown('FileCache::push');
                                    
                                }
                        
                                $pass = array($this->$arr['class'],
                                              $arr['func']);
                            
                                if (!is_callable(
                                    array(
                                        $this->getClass($arr['class'])
                                            . 'Controller',
                                       $arr['func']))
                                ) {
                                
                                    $this->render('404');
                                    exit;
                                    
                                } else {
                                    
                                    if (is_callable(
                                        array(
                                            $this->getClass($arr['class'])
                                                . 'Controller'
                                            , '__pre'))
                                    ) {
                                            
                                        call_user_func_array(array ($this->$arr['class'], '__pre'),array());
                                            
                                    }
                                        
                                }
                                    
                            } else {
                                  
                                $this->render('404');
                                exit;
                                    
                            }
                                
                        }
                        else
                        {
                                
                            $pass = $arr;
                          
                            if (!is_callable($arr)) {
                                
                                $this->render('404');
                                exit;
                                
                            }
                            
                        }
                        
                        if (is_array($arr)) {
                            
                            if (isset ($this->$arr['class'])
                                    && is_object ($this->$arr['class'])
                            ) {
                                  
                                $this->$arr['class']->request = $pass[1];
                                
                                if (is_callable(
                                        array(
                                            $this->getClass($arr['class'])
                                                . 'Controller'
                                            , '__post'))
                                ) {
                                        
                                    Controller::shutdown(
                                        array(
                                            $this->$arr['class'],
                                            '__post'), 1);
                                    
                                }
                            
                            }

                            $model = new Model;

                            $columns = $model->get_fields($arr['class']);

                            $data = array();

                            foreach ($columns as $column)
                            {

                                if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {

                                    $data[$column] = isset($this->args['get'][$column]) ? $this->args['get'][$column] : null;

                                } else {

                                    $data[$column] = isset($this->args['post'][$column])? $this->args['post'][$column]
                                        : (isset($this->args['get'][$column]) ? $this->args['get'][$column] : null);

                                }

                            }

                            $this->$arr['class']->data = $data;
                            
                        }
                                
                        if (!call_user_func_array($pass, array())) {
                                
                            if (is_array($arr)) {
                                   
                                if (substr($arr['func'], 0, 1) === "_") {
                                       
                                    $func = substr($arr['func'], 1);
                                        
                                } else {

                                    $func = $arr['func'];
                                        
                                }
                                    
                                $this->$arr['class']->render($arr['class']
                                    . DS . $func);
                                                        
                            }
                                
                        }
                            
#                            if (!empty($msg)) {
#                            
#                                global $var4View;
#                                $var4View['error'] = $msg;
#                                
#                            }
#                            
#                            if (is_array ($arr)) {
#                                
#                                $this->$arr['class']->request = $arr['func'];
#                                $this->$arr['class']->render($arr['class'] . DS . $arr['func']);
#                                
#                            } else {
#                                
#                                $this->render();
#                                
#                            }
#                            // Renders the default view for called class-method[if not exists, renders the default site view]
#                            // For procedural functions, calls the default site view
                        
                        return;
                        
                    }
                    
                }
                
                // Check if number of arguments in URL == 2
                if (count ($this->args['req_args']) == 2) {
                    
                    // Get short Class name (Object Name)
                    $short_class = $this->args['req_args'][0];
                    
                    // Make Controller Name from Object Name 
                    $class = $this->getClass($this->args['req_args'][0])
                             . 'Controller';
                    
                    // Check if Controller Class Exists
                    if (class_exists ($class)) {
                            
                        $req = strtolower($_SERVER['REQUEST_METHOD']);
                        
                        // Get Method Name form Url
                        $method = $this->args['req_args'][1];
                        
                        $om = $method;
                        
                        global $var4View;
                        $var4View['req'] = $req;
                        
                        if ($req == 'get') {
                        
                            $method = '_' . $method; // Add _ for get methods
                            
                        }                        
                        
                        // Fetch the Parameters of the Class-Method
                        is_callable (array ($class, $method))
                            ? ($classMethod = new ReflectionMethod(
                                                $class, $method)) : null;
                            
                        if (is_callable (array ($class, $method))
                            && $classMethod->getDeclaringClass()->getName() == $class
                            && !in_array ($method, array ('__pre', '__post', 'index'))
                        ) { // Check if Method of Class exists
                                            
                            if (FileCache::check(
                                    array(
                                        'class' => $short_class,
                                        'func' => $method
                                    )
                                )
                            ) {
                                    
                                FileCache::pop();
                                
                                Controller::shutdown('FileCache::push');
                                    
                            }
                            
                            $count = 0;
                            $unset = 0;

                            if (is_callable(array($class, '__pre'))) {
                            
                                call_user_func_array(
                                    array(
                                        $this->$short_class,
                                        '__pre'
                                    )
                                    , array()
                                ); // If pre method is defined, call it.
                                
                            }
                                    
                            if (is_callable (array ($class, '__post'))) {
                            
                                Controller::shutdown(
                                    array(
                                        $this->$short_class,
                                        '__post'
                                    ),
                                    1
                                ); // If post method is defined, call it.
                                
                            }
                                
                            $this->$short_class->request = $method;

                            $model = new Model;
                            $columns = $model->get_fields($short_class);

                            $data = array();

                            foreach ($columns as $column)
                            {

                                if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {

                                    $data[$column] = isset($this->args['get'][$column]) ? $this->args['get'][$column] : null;

                                } else {

                                    $data[$column] = isset($this->args['post'][$column])? $this->args['post'][$column]
                                        : (isset($this->args['get'][$column]) ? $this->args['get'][$column] : null);

                                }

                            }

                            $this->$short_class->data = $data;
                                
/*
 *                           Dynamically Call the Class-Method
 *                           and supply arguments to it and render its view
 */
                            if (!call_user_func_array(
                                    array(
                                        $this->$short_class,
                                        $method
                                    ),
                                    array()
                                    )
                                ) {

                                    $this->$short_class->render($short_class
                                                        . DS . $om);
                                     
                                }
                            
                        } else {
                            
                            $this->render ('404');
/*                            
 *                          Method Does Not exist in the child class
 *                          Renders 404 Error Page
 */
                            
                        }
                            
                    } else {
                        
                        $this->render ('404');
/*                        
 *                      Class Does Not exist
 *                      Renders 404 Error Page
 */
                        
                    }
                    
                } else if (count ($this->args['req_args']) == 1) {
                        
                    $short_class = $this->args['req_args'][0];
                    // Get short Class name (Object Name)
                    
                    $class = $this->getClass($this->args['req_args'][0])
                                . 'Controller';
                    // Make Controller Name from Object Name 
                    
                    if (class_exists($class)) {
                            
                        if (is_callable(array($class, 'index'))) {
                            
                            if (is_callable (array ($class, '__pre'))) {
                            
                                call_user_func_array(
                                    array(
                                        $this->$short_class,
                                        '__pre'
                                    ),
                                    array()
                                ); // If pre method is defined, call it.
                                
                            }
                                
                            if (is_callable (array ($class, '__post'))) {
                            
                                Controller::shutdown(
                                    array(
                                        $this->$short_class,
                                        '__post'
                                    ),
                                    1
                                ); // If post method is defined, call it.
                                
                            }
                            
                            $this->$short_class->request = 'index';
                            
                            if (!call_user_func_array(
                                    array(
                                        $this->$short_class,
                                        'index'
                                    ),
                                    array()
                                    )
                                ) { // If index method is defined, call it.
                                    
                                    $this->$short_class->render($short_class . DS . "index");
                                    
                                }
/*                            
 *                          if (is_callable (array ($class, '__post'))) {
 *                          
 *                              call_user_func_array(
 *                                  array(
 *                                      $this->$short_class,
 *                                      '__post'
 *                                  ),
 *                                  array()
 *                              ); // If post method is defined, call it.
 *                              
 *                          }
 */
                            
                        } else {
                                
                            $this->render ('404'); // Renders 404 Error Page
                                
                        }

                    } else {
                            
                        $this->render ('404'); // Renders 404 Error Page
                            
                    }
                        
                } else {
                    
                    $this->render ('404'); // Renders 404 Error Page
                    
                }
            
            }
            
        }
        
        static function get ($routes, $fn)
        {
            
            self::make (__FUNCTION__, $routes, $fn);
            
        }
        
        static function put ($routes, $fn)
        {
            
            self::make (__FUNCTION__, $routes, $fn);
            
        }
        
        static function delete ($routes, $fn)
        {
            
            self::make (__FUNCTION__, $routes, $fn);
            
        }
        
        static function post ($routes, $fn)
        {
            
            self::make (__FUNCTION__, $routes, $fn);
            
        }
        
        static function all ($routes, $fn)
        {
        
            self::post ($routes, $fn);
            self::get ($routes, $fn);
            self::put ($routes, $fn);
            self::delete ($routes, $fn);
            
        }
        
        private static function make ($type, $routes, $fn)
        {
            
            $routes = (array) $routes;
            
            foreach ($routes as $route)
            {
                
                $route = (substr ($route, 0, 1) != '/' ? '/' : null)
                          . (strlen ($route)
                            ? (substr ($route, strlen ($route) - 1, 1) != '/'
                          ? $route
                            : substr ($route, 0, strlen ($route) - 1)) : null);
                
                $toFind = '(:regx[';
                
                $start = 0;
                
                while (($pos = strpos ($route, $toFind, $start)) !== false)
                {
                    
                    $part1 = substr($route, 0, $pos + strlen ($toFind));
                    $part2 = substr($route, $pos + strlen ($toFind),
                                strpos ($route, '])', $pos)
                                    - $pos - strlen ($toFind));
                    $part3 = substr($route, strlen ($part1 . $part2));
                    
                    $part2 = urlencode ($part2);

                    $route = $part1 . $part2 . $part3;
                    
                    $start = strlen ($part1 . $part2);
                
                }
                
                $args = getArgs ($route);
                
                $config = array();
                
                if ($route == '/*') {
                    
                    $config = array(
                                'args' => -1,
                                'func' => $fn
                               );
                    
                } else {
                    
                    $pattern = array();
                    
                    foreach ($args as $arg)
                    {
                    
                        if ((strlen ($arg) >= strlen ('(:regx'))
                            && (substr ($arg, 0, strlen ('(:regx'))
                                == '(:regx')
                        ) {
                            
                            $pattern[] = array(
                                            'type' => 5,
                                            'val' => urldecode(substr($arg,
                                                strlen ('(:regx['),
                                                strlen ($arg)
                                                    - strlen ('(:regx[') - 2
                                                )
                                            )
                                         );
                            
                        } else {
                    
                            $pattern[] = array(
                                            'type' => ($arg != '(:num)'
                                                && $arg != '(:char)'
                                                && $arg != '(:alnum)'
                                                && $arg != '(:all)'
                                                ) ? 0 : ($arg == '(:num)'
                                                    ? 1 : ($arg == '(:char)'
                                                        ? 2 : ($arg == '(:alnum)'
                                                            ? 3 : 4)
                                                          )
                                                        ),
                                            'val' => ($arg != '(:num)'
                                                && $arg != '(:char)'
                                                && $arg != '(:alnum)'
                                                && $arg != '(:all)'
                                             ) ? $arg : null
                                       );
                                            
                        }
                        
                    }
                    
                    $config = array (
                                        'args' => count ($args),
                                        'pattern' => $pattern,
                                        'func' => $fn
                                   );
                    
                }
                                   
                if (is_array ($config['func'])
                    && (!isset ($config['func']['func']))
                    || empty ($config['func']['func'])
                ) {
                        
                    $config['func']['func'] = 'index';
                        
                }
                
                array_push (self::${$type}, $config);
                
            }
            
        }
        
    }
    
?>