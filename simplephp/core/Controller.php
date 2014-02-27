<?php
    
    // Primary Controller Class
    
    class Controller extends Simple
    {
        
        static $objects = array();
        static $classes = array();
        static $hasObject = array();
        static $call = false;
        static $ses;
        static $pwd;
        protected $PasswordHash;
        private static $shutdown;
        protected $data = array();
        
        // Restrict Child classes from having their own constructors    
        final function __construct ($caller = null)
        {
        
            if (!isset (Controller::$ses) || !is_object (Controller::$ses)) {
                
                Controller::$ses = new Session;
                
            }
        
            if (!isset (Controller::$pwd) || !is_object (Controller::$pwd)) {
                
                Controller::$pwd = new PasswordHash(8, TRUE);
                
            }
            
            $this->PasswordHash = &Controller::$pwd;
        
            $this->session = &Controller::$ses;
            
            global $_parms, $isAjax;
            
            if (!isset ($this->table) || empty ($this->table)) {
                
                $this->table = $this->getTable (get_class ($this));
                
            }
            
/*
 *          Set Parameters as object for application
 *          & as an array for Router and Scribe Controller
 */
            isset ($this->args) ? null
                    : (!in_array (strtolower(get_class($this)),
                        array ('router', 'scribe'))
                        ? ($this->args = (object) $_parms)
                        : ($this->args = &$_parms)
                      );
                      
            isset ($this->ajax) ? null : ($this->ajax = &$isAjax);
            
/*
 *          If Parent constructor is called automatically, 
 *          get name of class whose object was made
 */
          if ($caller == null)
                $caller = $this->getTable (get_class ($this));
            
            //Check if __construct() for Controller has been called already.
            if (!Controller::$call)
                Controller::$call = true;
            else {
                
                //Assign Previously created objects to this class.
                foreach (Controller::$classes as $class)
                {
                    
                    if ($class != $caller) {

                        if (!(isset($this->$class) && is_object($this->$class)))
                            $this->$class = &Controller::$objects[$class];
                    
                    }
                    
                }
                
                return;
            
            }
            
            if (empty (Controller::$classes))
            {
                
                $type = get_parent_class ($this);
                
                $base = BASE_PATH . APP_FOLDER;
                    
                $paths = array ('controllers');
                
                foreach ($paths as $path)
                {
/*
 *                  Load all classes by File Name
 *                  [Filename should be same as Controller/Model Name]
 */
                    if ($handle = opendir ($base . DS . $path)) {
           
                       while (false !== ($entry = readdir ($handle)))
                       {
           
                            $ext = pathinfo ($entry, PATHINFO_EXTENSION);
                    
                            if ($entry !=
                                    pathinfo(__FILE__, PATHINFO_FILENAME)
                                    . '.' . $ext
                            ) {
                    
                                if ($ext == 'php') {
                            
                                    $get = explode ('.php', $entry);
                                
                                    if ($get[0] != $type &&
                                        !in_array ($get[0],
                                            array ('AppController'))
                                    ) {
                                
                                        array_push (self::$classes,
                                                    $this->getTable ($get[0]));
                                
                                    }
                        
                                }
        
                            }
       
                        }
                    
                    }
                    
                }
                
            }
            
            foreach (Controller::$classes as $class)
            {
                // Check BOOLEAN if the class already has an object
                if (!isset (Controller::$hasObject[$class])
                    || !Controller::$hasObject[$class]
                ) {
                    // Check the object
                    if (!(isset (Controller::$objects[$class])
                        && is_object (Controller::$objects[$class]))
                    ) {
                            
                        Controller::$hasObject[$class] = true;
                        $phpClass = $this->getClass ($class) . 'Controller';
                        Controller::$objects[$class] = new $phpClass();
                        
                    }
/*                    
 *                  Assign the objects of various classes as properties
 *                  to $this (Class calling the constructor)
 */
                    if (!(isset ($this->$class) && is_object ($this->$class)))
                        //check for not assigning the object of a class to itself
                        if ($caller != $class)
                            $this->$class = &Controller::$objects[$class];

                }
            
            }
            
        }
        
        // Add functions to shutdown stack
        final static function shutdown ($pass = array(), $priority = 0)
        {
            // Push Functions into run on shutdown stack
            self::$shutdown = (array)self::$shutdown;
            
            if ($priority) {
                
                self::$shutdown = array_reverse (self::$shutdown);
                array_push (self::$shutdown, $pass);
                self::$shutdown = array_reverse (self::$shutdown);
                
            } else {
                
                array_push (self::$shutdown, $pass);
                
            }
            
        }
        
        // Run functions from shutdown stack
        final static function runShutdown ()
        {
            // Run the Functions in shutdown stack
            self::$shutdown = (array)self::$shutdown;
            
            foreach (self::$shutdown as $shutdown)
            {
            
                if (isset ($shutdown) && !empty ($shutdown)) {
                
                    call_user_func_array ($shutdown, array());
                
                }
                
            }
            
        }
        
        // Check if a variable is set for view
        final protected function is_set ($var = null)
        {
            
            global $var4View;
            return isset($var4View[$var]);
            
        }
        
        // Get a variable, set for view
        final protected function vget ($var = null)
        {
            
            if ($this->is_set($var)) {
            
                global $var4View;
                return $var4View[$var];
                
            }
            return false;
            
        }
        
        // Set a variable for view
        final protected function set ($var = null, $val = null)
        {
            // Set variables for view
            if (is_array ($var)) {
                
                foreach ($var as $key=>$value)
                {
/*                    
 *                  If array is given as input,
 *                  map it to set all values in the view
 */
                    $this->{__FUNCTION__} ($key, $value);
                    
                }
                
            } else {
            
                global $var4View;
            
                if ($var != null) {
                
                    if (!in_array($var,
                                    array ('config', 'db', 'error', 'content'))
                    ) {
                
                        $var4View[$var] = $val;
                    
                    } else {
/*
 *                        Fire in case 'config', 'db',' error'
 *                          or 'content' is set
 */
                        trigger_error('Unable to overwrite default variable "'
                                        . $var . '" for view');
                    
                    }
                
                }
    
            }
            
        }
        
        // Send Mail Function
        final protected function sendMail ($recievers = array(), $subject = null,
            $variables = array(), $details = array(), $template = 'default'
        ) {
            
            global $config;
            
            $mail = new PHPMailer();
            
            if ($config['mail']['isSMTP']) {
                
                $mconfig = $config['mail']['config'][0];
                
                $mail->Mailer = "smtp";
                $mail->IsSMTP();

                $mail->Host = $mconfig['host'];
                $mail->Port = $mconfig['port'];

                $mail->SMTPAuth = true;
                $mail->SMTPDebug = 0;

                $mail->Username = $mconfig['username'];
                $mail->Password = $mconfig['passwd'];
                
            } else {
            
                $mconfig = $config['mail']['config'][1];
                
            }
            
            $sender = (!isset ($details['sender'])
                      || empty ($details['sender']))
                        ? $mconfig['name'] : $details['sender'];
                        
            $from = (!isset ($details['from'])
                    || empty ($details['from']))
                        ? $mconfig['sender'] : $details['from'];
            
            $reply = (!isset ($details['reply'])
                      || empty ($details['reply']))
                            ? $mconfig['sender'] : $details['reply'];
                            
            $rName = (!isset ($details['rName'])
                      || empty ($details['rName']))
                        ? $mconfig['name'] : $details['rName'];

            $mail->From = $from;
            $mail->FromName = $sender;
            $mail->Sender = $from;
            $mail->AddReplyTo($reply, $rName);
            
            foreach ($recievers['to'] as $recipient)
            {
                
                $mail->AddAddress($recipient['email'],
                        isset ($recipient['name']) ? $recipient['name'] : null);
                
            }
            
            !isset ($recievers['cc']) ? ($recievers['cc'] = array()) : null;
            
            foreach ($recievers['cc'] as $recipient)
            {
                
                $mail->AddCC($recipient['email'],
                        isset ($recipient['name']) ? $recipient['name'] : null);
                
            }
            
            !isset ($recievers['bcc']) ? ($recievers['bcc'] = array()) : null;
            
            foreach ($recievers['bcc'] as $recipient)
            {
                
                $mail->AddBCC($recipient['email'],
                        isset ($recipient['name']) ? $recipient['name'] : null);
                
            }
            
            $mail->WordWrap = 50;
            $mail->IsHTML(true);
            $mail->Subject = $subject;
                    
            $buffer = ob_get_contents();
            if (ob_get_length())
                ob_clean();
            
            $this->renderPartial(DS . 'app' . DS. 'views' .DS .'_layouts'
                    . DS .'_email' . DS . 'html' . DS . $template, $variables);
                    
            $mail->Body = ob_get_contents();
            if (ob_get_length())
                ob_clean();
                
            $this->renderPartial(DS . 'app' . DS. 'views' .DS .'_layouts'
                    . DS .'_email' . DS . 'text' . DS . $template, $variables);
                    
            $mail->AltBody = ob_get_contents();
            if (ob_get_length())
                ob_clean();
                
            print $buffer;
            
            $send = false;
            
            for ($i = 0; $i < 5 && !$send; $i++)
            {
            
                $send = $mail->Send();
                
            }
            
            return $send;
            
        }
        
        // Integer Typecasting function
        final protected function smart_val ($string)
        {
              
              return @number_format($string,0,'.','');

        }
        
        // Return JSON object for an Ajax Request
        final protected function returnAjaxJson ($data)
        {
            
            echo json_encode($data);
            exit;
            
        }
        
        // Render an element without flushing the output buffer or exiting the app
        final protected function renderPartial ($filename = null, $variables = array())
        {
            // If file-path begins with / map it relative to BASE_PATH, else map it relative to current directory
            if (substr ($filename, 0, 1) != DS) {
                
                $backtrace = debug_backtrace();
                $f = $backtrace[0]['file'];
                $file = explode(DS, substr($f, strlen(BASE_PATH)));
                
                if (count($file)) {
                    
                    unset($file[count($file) - 1]);
                    
                }
                $filename = implode(DS, $file) . DS . $filename;
                
            }
            else {
                
                $filename = substr($filename, 1);
                
            }
            
            $filename .= '.php';
            
            $this->partial = (array) $variables;
            
            $this->requires($filename, true, true);
            
        }
        
        // Render a view and exit application
        final protected function render ($class = null)
        {
            
            if (is_callable(array($this, '__beforeRender')))
                $this->__beforeRender();
        
            if ($class == null)
            {
                
                $class = $this->getTable (get_class ($this));
                
                $trace = debug_backtrace();
                $caller = isset($trace[1]) ? $trace[1] : $trace[0];
                $func = ($caller['function'] != 'call_user_func_array')
                                                ? $caller['function'] : 'index';
                
                if (substr($func, 0, 1) == '_')
                    $func = substr ($func, 1);
                
                if (!file_exists(BASE_PATH . APP_FOLDER . DS . 'views' . DS
                    . $class . ($func != null ? (DS . $func) : null) . '.php')
                ) {
                    $func = 'index';
                }
                
            }
            else
            {
                
                $func = null;
                
            }
                
            global $var4View, $config, $db;
            
            $var4View['config'] = &$config;
            $var4View['db'] = &$db;
                            
            if (file_exists (BASE_PATH . APP_FOLDER . DS . 'views' . DS
                . $class . ($func != null ? (DS . $func) : null) . '.php')
            ) {
                $file = APP_FOLDER . DS . 'views' . DS . $class
                        . ($func != null ? (DS . $func) : null) . '.php';
            }
            else {
                $file = APP_FOLDER . DS . 'views' . DS . 'default' . '.php';
            }
                
            $this->requires($file, true);
            
            $var4View['content'] = ob_get_contents();
            
            if (ob_get_length())
                ob_clean();
                
            if (isset($this->layout)) {
                
                if (!empty($this->layout)) {
    
                    $this->requires(APP_FOLDER . DS . 'views' . DS . '_layouts'
                                . DS . $this->layout . DS . 'main.php', true);
                                
                }
                else {

                    print $var4View['content'];
                    
                }
                exit;
                
            }
            
            if (file_exists(BASE_PATH . APP_FOLDER . DS . 'views' . DS
                . '_layouts' . DS . $class . DS . 'main.php')
            ) {
                $this->requires(APP_FOLDER . DS . 'views' . DS . '_layouts'
                                . DS . $class . DS . 'main.php', true);
            } else {
                $this->requires (APP_FOLDER . DS . 'views' . DS . '_layouts'
                                . DS . 'main.php', true);
            }
                
            $var4View = array();
                
            exit;
            
        }
        
        // Return Model Class for a Controller
        final protected function getModel ($class = null)
        {
            
            if ($class == null)
                $class = get_class ($this);
            
            $class = explode ('Controller', $class);
            $class = $class[0];
            
            $class = explode ('Model', $class);
            $class = $class[0];
            
            return $class . 'Model';
            
        }
        
        // Load a file with error handling enabled
        final protected function requires ($files, $view = false, $partial = false)
        {
        
            if (is_array ($files))
                array_map (array ($this, 'requires'), $files);
            else
            {
                //Check against base path if file exists
                if (file_exists (BASE_PATH . $files)) {
                    
                    global $loaded_files;
                
                    if (!in_array(BASE_PATH . $files, $loaded_files)) {
                    
                        if ($view) {
                            
                            if (!$partial) {
                            
                                global $var4View;
                            
                                $keys = array_keys($var4View);
                                
                                foreach ($keys as $key)
                                {
                        
                                    ${$key} = $var4View[$key];
                                
                                }
                                
                            } else {
                                
                                if (!isset ($this->partial))
                                    $this->partial = array();
                                    
                                $this->partial = (array) $this->partial;
                                
                                $keys = array_keys ($this->partial);
                                
                                foreach ($keys as $key)
                                {
                        
                                    ${$key} = $this->partial[$key];
                                
                                }
                                
                                unset($this->partial);
                                
                            }
                            
                        }
                    
                        require_once (BASE_PATH . $files); //Loads File
                        
                        logger('Loaded file: ' . BASE_PATH . $files
                                . ' | Requested on ' . date(DATE_RFC822)
                                . ' via ' . $_SERVER['HTTP_USER_AGENT']
                                . ' | Request IP: ' . $_SERVER['REMOTE_ADDR']);
                        // Logs event
                        
                        array_push($GLOBALS['loaded_files'], BASE_PATH . $files);
                
                    }
                
                }
                else
                {

                    $message = 'Unable to load file: '
                                . rtrim(dirname(dirname(__FILE__)), '/\\')
                                . DS . $files;
                                
                    logger ($message . ' | Requested on ' . date(DATE_RFC822)
                                . ' via ' . $_SERVER['HTTP_USER_AGENT']
                                . ' | Request IP: ' . $_SERVER['REMOTE_ADDR']
                            , 'Error');
                    //Logs event
                    
                    trigger_error($message); // Generate Error Message
                
                }
        
            }
        
        }

        final function check_data ($data = array(), $model)
        {

            if (count(explode('Model', get_class($model))) <= 1) {

                trigger_error("Invalid Operation Performed. <br>Application will now exit.");

            }

            $validators = &$model->validators;

            global $_parms, $isAjax;

            $msg = null;
            $break = false;

            foreach ($data as $parameter => $value)
            {
                                
                // Check if parameter is set in $_POST and is not empty
                                    
/*                                
 *              Set sentinel = true,
 *              the loop should now exit,
 *              user did not supply all required data
 */
                                    
                if (isset ($validators[$parameter])) {
                                        
                    foreach ($validators[$parameter]
                                as $key => $val
                    ) {

                        $type = (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
                                    ? 'get' : 'post';
                                            
                        if ($key != 'notEmpty' && $key != 'isset') {
                                                
                            $prms = isset($validators[$parameter][$key]['params']) ? (array)$validators[$parameter][$key]['params'] : array();
                            $prms = array_reverse ($prms);
                            array_push ($prms, $_parms[$type][$parameter]);
                            $prms = array_reverse ($prms);
                                            
                            if (!call_user_func_array(array($model, $key), $prms)) {
                                                    
                                $break = true;
                                $msg = !$isAjax ? $validators[$parameter][$key]['msg'] : $validators[$parameter][$key]['code'];
                                break;
                                                    
                            }
                                                
                        } else if ($key == 'isset') {
                                                
                            if (isset($_parms[$type][$parameter])) {
                                                
                                $break = true;
                                $msg = !$isAjax ? $validators[$parameter][$key]['msg'] : $validators[$parameter][$key]['code'];
                                break;
                                                    
                            }

                        } else {
                                                
                            if (empty($_parms[$type][$parameter])) {
                                                
                                $break = true;
                                $msg = !$isAjax ? $validators[$parameter][$key]['msg'] : $validators[$parameter][$key]['code'];
                                break;
                                                    
                            }
                                                
                        }
                                            
                    }

                    if ($break) {

                        break;

                    }
                                                                           
                }

            }

            if ($break && !empty($msg)) {

                if ($isAjax) {

                    $this->returnAjaxJson(array(
                            'status' => false,
                            'code' => $msg)
                        );

                } else {

                    if (!empty($msg)) {
                            
                        global $var4View;
                        $var4View['error'] = $msg;
                                
                    }
                            
                    $class = $this->getTable(get_class($model));
                    Controller::$objects[$class]->render($class . DS . Controller::$objects[$class]->request);

                }

            }

        }
        
    }


?>