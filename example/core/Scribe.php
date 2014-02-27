<?php

    // Scribe Class to handle Ajax requests
    
    class scribe extends Controller
    {

        // Class access denied
        private static $ClassAccessDenied = 'ClassAccessDenied';
    
        // Class Exists
        private static $invalidCategory = 'invalidCategory';
    
        // Class-Method Exists
        private static $invalidAction = 'invalidAction';
        
        // Method Access Denied
        private static $MethodAccessDenied = 'MethodAccessDenied';
    
        // POST/GET Parameters not set
        private static $checkInput = 'checkInput';
        
        static function errorSet ($type = null, $code = null)
        {
            
            if (!empty ($type)) {
                
                if (isset (scribe::$$type)) {
                    
                    scribe::$$type = !empty ($code) ? $code : scribe::$$type;
                    
                } else {
                    
                    trigger_error ('Undefined Error Type.');
                    
                }

            } else {
                
                trigger_error ('Unable to set empty error code.');
                
            }
        
        }
        
        function init()
        {
            
            /**
             * Only Handle Ajax requests at http://$_SERVER['HTTP_HOST']/scribe
            */
            
            if (count ($this->args['req_args']) == 1
                && $this->args['req_args'][0] == 'scribe'
            ) {
                
                global $ajaxRoutes;
                
                (isset ($ajaxRoutes) && !empty ($ajaxRoutes))
                    ? ($actions = $ajaxRoutes) : null;
                
                if ((isset ($this->args['post']['category'])
                        && !empty ($this->args['post']['category'])
                    )
                    && class_exists(
                        $this->getClass($this->args['post']['category'])
                        . 'Controller'
                    )
                   ) {
                        
                        /**
                         * Check if Class is accessible via Ajax
                        */
                        
                        if (!isset ($actions) || (isset ($actions)
                            && empty ($actions))
                            || (isset ($actions) && !empty ($actions)
                                && array_key_exists(
                                    $this->args['post']['category'], $actions)
                                )
                           ) {
                        
                            /**
                             * Check if Method of Class exists
                            */
                        
                            is_callable (array(
                                            $this->getClass(
                                                $this->args['post']['category'])
                                                . 'Controller',
                                                $this->args['post']['action'])
                                        ) ? ($classMethod = new ReflectionMethod(
                                                $this->getClass(
                                                    $this->args['post']['category'])
                                                     . 'Controller',
                                                 $this->args['post']['action'])
                                            ) : null;
                            // Fetch the Parameters of the Class-Method                
                            
                            if ((isset ($this->args['post']['action'])
                                    && !empty ($this->args['post']['action']))
                                 && (is_callable (array($this->getClass(
                                        $this->args['post']['category'])
                                             . 'Controller',
                                        $this->args['post']['action'])
                                    )
                                    && $classMethod->getDeclaringClass()->getName()
                                        == ($this->getClass(
                                                $this->args['post']['category'])
                                                . 'Controller'
                                            )
                                    && !in_array($this->args['post']['action'],
                                                    array ('__pre', '__post')
                                        )
                                    )
                               ) {
                            
                                /**
                                 * Check if Class-Method is accessible via Ajax
                                */
                                
                                if (!isset($actions[$this->args['post']['category']])
                                    || (isset($actions[$this->args['post']['category']])
                                        && in_array($this->args['post']['action'],
                                            $actions[$this->args['post']['category']]
                                        )
                                       )
                                    ) {
                                
                                    /**
                                     * Fetch the Parameters of the Class-Method
                                    **/
                                    
                                    if (FileCache::check(array(
                                        'class' => $this->args['post']['category'],
                                        'func' => $this->args['post']['action']))
                                    ) {
                                    
                                        FileCache::pop ();
                                    
                                        Controller::shutdown ('FileCache::push');
                                    
                                    }
                                    
                                    $class = $this->args['post']['category'];
                                        
                                    if (is_callable(array(
                                            $this->getClass(
                                                $this->args['post']['category'])
                                                    . 'Controller',
                                                '__pre'))
                                    ) {
                            
                                        call_user_func_array(array(
                                                $this->$class,
                                                '__pre'
                                            ),
                                            array()
                                        ); // If pre method is defined, call it.
                                
                                    }
                            
                                    if (is_callable (array($this->getClass(
                                        $this->args['post']['category'])
                                        . 'Controller', '__post'))
                                    ) {
                            
                                        Controller::shutdown(
                                            array(
                                                $this->$class,
                                                '__post'
                                            ),
                                            1
                                        );
                                        // If post method is defined, call it.
                                
                                    }
                                        
                                    $this->{
                                        $this->getClass(
                                            $this->args['post']['category']
                                        )}->data = $parms;
                                            
                                    $this->{
                                        $this->getClass(
                                            $this->args['post']['category']
                                        )}->request
                                        = $this->args['post']['action'];

                                    $model = new Model;
                                    $columns = $model->get_fields($class);

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

                                    $this->$class->data = $data;
                                        
                                    // Call the Class-Method
                                    call_user_func_array(array(
                                            $this->$class,
                                            $this->args['post']['action']
                                        ),
                                        array()
                                    );
                                    
                                } else {
                                    
                                    // Class-Method is not Ajax Accessible
                                    $this->returnAjaxJson(array(
                                        'status' => false,
                                        'code' => $this->MethodAccessDenied)
                                    );
                                    
                                }
                            
                            } else {
                                
                                // Method Does not exist
                                $this->returnAjaxJson(array(
                                    'status' => false,
                                    'code' => $this->invalidAction)
                                );
                            
                            }
                        
                        } else {
                            
                            // Class Does Not exist
                            $this->returnAjaxJson(array(
                                'status' => false,
                                'code' => $this->ClassAccessDenied)
                            );
                        
                        }
                        
                } else {
                    
                    // Class is not Ajax Accessible
                    $this->returnAjaxJson(array(
                        'status' => false,
                        'code' => $this->invalidCategory)
                    );
                    
                }
                
            } else {
                
/*
 *              // Redirect to http://$_SERVER['HTTP_HOST']/
 *              $this->redirect();
 */
                
                // Render 404 Page
                $this->render('404');
                
            }
            
        }
        
    }
    
?>