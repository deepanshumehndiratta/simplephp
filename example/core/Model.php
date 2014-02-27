<?php

    // Primary Model Class

    class Model extends Simple
    {
        
        static $models = array();
        public static $con;
        private $obj = false;
        private $id = false;
        
        final function __construct ($caller = null)
        {
        
            // Restrict Child classes from having their own constructors
    
            /**
             * Establish Memcached connection
            **/
    
            $this->cache = new Cache;
            
        }
        
        // Return fetched Database array as object 
        final protected function rt_obj ($array = array())
        {
            
            if ($this->obj) {
                
                $array = $this->to_obj ($array);
                $this->obj = false;
            
            }
            
            return $array;
            
        }
        
        // Get Model object for a Class
        final static function get ($table = null)
        {
            
            global $tables;
            
            if ($table == null) {
                
                $table = @self::getTable(get_calling_class());
                
            }
            
            $class = @self::getClass($table) . 'Model';
            
            $filename = BASE_PATH . APP_FOLDER . DS . 'models' . DS . $class . '.php';
            
            if (file_exists ($filename)) {
                
                requires (APP_FOLDER . DS . 'models' . DS . $class . '.php');
                
                if (class_exists ($class))
                {
                    
                    if (!(isset (self::$models[$table])
                        && is_object (self::$models[$table]))
                    ) {
                        
                        if (in_array ($table, $tables)) {
                        
                            self::$models[$table] = new $class;
                            
                        } else {
                    
                            $message = 'Table ' . $table . ' for Controller - '
                                        . str_replace('Model', 'Controller'
                                            , $class)
                                        . ', could not be found';
                        
                            logger ($message, 'Error');
                    
                            trigger_error ($message);
                            
                        }
                        
                    }
                    
                    $rtr = &self::$models[$table];
                
                    if (!isset ($rtr->table)) {
                        
                        $rtr->table = $table;
                        
                    }
                    
                    return $rtr;
                    
                } else {
                    
                    $message = 'Class ' . $class . ' could not be found in '
                                . APP_FOLDER . DS . 'models' . DS . $class
                                . '.php';
                    
                    logger ($message, 'Error');
                    
                    trigger_error ($message);
                    
                }
                
            }
            else
            {
                
/*
 *               $message = 'Model file for Controller - '
 *                          . str_replace ('Model', 'Controller' , $class)
 *                          . ' does not exist';
 *
 *              
 *              logger ($message, 'Error');
 *                  
 *              trigger_error ($message);
 */
                
                // Dynamically create and extend class
                
                @eval ("class " . $class . " extends Model {}");
                self::$models[$table] = new $class;
                $rtr = &self::$models[$table];
                
                if (!isset ($rtr->table)) {
                    
                    $rtr->table = $table;
                    
                }
                    
                return $rtr;
                
            }
            
        }
        
        // Check Length of a string
        final function checkLength ($string, $length)
        {
            
            return (strlen($string) >= $length);
            
        }
        
        // Set to retrieve Database array as Object
        final function obj ()
        {
            
            $this->obj = true;
            return $this;
            
        }
        
        // Fetch All keys from Database Table
        final function fetchAll ($sql = null, $args = array(), $table = null)
        { //Select All columns From a table
            
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            $order = isset ($args['order']) ? $args['order'] : null;
            $limit = isset ($args['limit']) ? $args['limit'] : null;
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            if ($limit != null) {
            
                if (is_array ($limit)) {
                    
                    $limit = ' LIMIT ' . intval ($limit[0]) . ','
                             . intval ($limit[1]); 
                    
                } else {
                    
                    $limit = ' LIMIT 0,' . intval ($limit);
                    
                }
                
            } else {

                $limit = null;
                
            }
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
            
            $sql = "SELECT * FROM `" . $table . "`"
                    . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group) ? null
                        : (' GROUP by ' . implode (', ', $group)))
                    . ($order ? (' ORDER by ' . $order) : null) . $limit;
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get ($sql))) {
                
                return $this->rt_obj ($results);
                
            }
            
            $query = mysqli_query (Model::$con, $sql);
            
            $results = array();
            
            profile ($sql, $query, $query ? mysqli_num_rows ($query) : 0,
                     $this->cache->check());
            
            if ($query) {
                
                while (($row = mysqli_fetch_array ($query)))
                {
                    
                    $current = array();
                    
                    while (list ($k, $column) = each ($row))
                    {
                        
                        if (is_numeric ($k))
                            $current[key($row)] = stripslashes($row[key($row)]);
                        
                    }
                    
                    array_push ($results, $current);
                
                }
            
                $this->cache->put ($sql, $results);
                
            } else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj ($results);
            
        }
        
        // Fetch Count of rows from table
        final function fetchCount ($sql = null, $args = array(), $table = null)
        {
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
            
            $sql = "SELECT COUNT(*) FROM `" . $table . "`"
                    . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group) ? null
                        : (' GROUP by ' . implode (', ', $group)));
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get ($sql))) {
                
                return $results;
                
            }
            
            $query = mysqli_query (Model::$con, $sql);
            
            $results = array();
            
            profile ($sql, $query, $query ? mysqli_num_rows ($query) : 0);
            
            if ($query) {
                
                list ($results) = mysqli_fetch_row ($query);
            
                $this->cache->put ($sql, $results);
                
            } else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $results;
            
        }
        
        // Fetch Maximum of fields from a table
        final function fetchMax ($args = array(), $sql = null, $table = null)
        {
            $columns = isset ($args['columns'])
                        ? ((array)$args['columns']) : array();
                        
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
                
            for ($i = 0; $i < count ($columns); $i++)
            {
                
                $columns[$i] = 'MAX(' . $columns[$i] . ')';
                
            }
            
            $sql = "SELECT " . implode (', ' ,$columns) . " FROM `" . $table
                    . "`" . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group)
                        ? null : (' GROUP by ' . implode (', ', $group)));
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get ($sql))) {
                
                return $this->rt_obj ($results);
                
            }
            
            $query = mysqli_query (Model::$con, $sql);
            
            $results = array();
            
            profile ($sql, $query, $query ? mysqli_num_rows ($query) : 0);
            
            if ($query) {
                
                while (($row = mysqli_fetch_array ($query)))
                {
                    
                    $current = array();
                    
                    while (list ($k, $column) = each ($row))
                    {
                        
                        if (is_numeric ($k)) {

                            $current[substr(key($row),
                                strlen('MAX('), strlen (key ($row))
                                - strlen ('MAX(') - 1)
                            ] = stripslashes ($row[key ($row)]);
                        
                        }

                    }
                    
                    if (count ($current) == 1) {
                        
                        $results = $current[key ($current)];
                        
                    } else {
                    
                        $results = $current;
                        
                    }
                
                }
            
                $this->cache->put ($sql, $results);
                
            }
            else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj ($results);
            
        }
        
        // Fetch Minimum of fields from table
        final function fetchMin ($args = array(), $sql = null, $table = null)
        {
            $columns = isset ($args['columns']) ? ((array)$args['columns']) : array();
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
                
            $columns = (array) $columns;
                
            for ($i = 0; $i < count ($columns); $i++)
            {
                
                $columns[$i] = 'MIN(' . $columns[$i] . ')';
                
            }
            
            $sql = "SELECT " . implode (', ' ,$columns) . " FROM `"
                    . $table . "`" . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group)
                        ? null : (' GROUP by ' . implode (', ', $group)));
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get ($sql))) {
                
                return $this->rt_obj ($results);
                
            }
            
            $query = mysqli_query (Model::$con, $sql);
            
            $results = array();
            
            profile ($sql, $query, $query ? mysqli_num_rows ($query) : 0);
            
            if ($query) {
                
                while (($row = mysqli_fetch_array ($query)))
                {
                    
                    $current = array();
                    
                    while (list ($k, $column) = each ($row))
                    {
                        
                        if (is_numeric ($k)) {

                            $current[substr(key($row), strlen('MIN('),
                                strlen(key($row)) - strlen('MIN(') - 1)
                            ] = stripslashes($row[key($row)]);
                        
                        }

                    }
                    
                    if (count ($current) == 1) {
                        
                        $results = $current[key($current)];
                        
                    } else {
                    
                        $results = $current;
                        
                    }
                
                }
            
                $this->cache->put ($sql, $results);
                
            } else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj ($results);
            
        }
        
        // Fetch Average of fields from table
        final function fetchAvg ($args = array(), $sql = null, $table = null)
        {
            $columns = isset($args['columns'])
                            ? ((array)$args['columns']) : array();
                            
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
                
            $columns = (array) $columns;
                
            for ($i = 0; $i < count ($columns); $i++)
            {
                
                $columns[$i] = 'AVG(' . $columns[$i] . ')';
                
            }
            
            $sql = "SELECT " . implode (', ' ,$columns) . " FROM `" . $table
                    . "`" . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group)
                        ? null : (' GROUP by ' . implode (', ', $group)));
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get($sql))) {
                
                return $this->rt_obj ($results);
                
            }
            
            $query = mysqli_query (Model::$con, $sql);
            
            $results = array();
            
            profile($sql, $query, $query ? mysqli_num_rows ($query) : 0);
            
            if ($query) {
                
                while (($row = mysqli_fetch_array ($query)))
                {
                    
                    $current = array();
                    
                    while (list ($k, $column) = each ($row))
                    {
                        
                        if (is_numeric ($k)) {

                            $current[substr(key($row), strlen('AVG('),
                                strlen(key($row)) - strlen('AVG(') - 1)
                            ] = stripslashes($row[key($row)]);
                        
                        }

                    }
                    
                    if (count ($current) == 1) {
                        
                        $results = $current[key($current)];
                        
                    } else {
                    
                        $results = $current;
                        
                    }
                
                }
            
                $this->cache->put($sql, $results);
                
            }
            else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj ($results);
            
        }
        
        // Execute a mysqli query
        final function fetchByQuery ($sql)
        {
            
            db_reconn();
            
            $query = mysqli_query(Model::$con, $sql);
            
            $results = array();
            
            profile($sql, $query, $query ? mysqli_num_rows($query) : 0);
            
            if ($query) {
                
                while (($row = mysqli_fetch_array($query)))
                {
                    
                    $current = array();
                    
                    while (list($k, $column) = each($row))
                    {
                        
                        if (is_numeric($k))
                            $current[key($row)] = stripslashes($row[key($row)]);
                        
                    }
                    
                    array_push($results, $current);
                
                }
                
            } else {
                
                trigger_error(mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj($results);
            
        }
        
        // Fetch Specific keys from a table
        final function fetchKeys ($keys = array(), $sql = null,
            $args = array(), $table = null
        ) { //Fetch selected Column from a table
        
            $keys = (array) $keys;
            // $keys = isset ($args['keys']) ? ((array)$args['keys']) : array();
            // $sql = isset ($args['sql']) ? $args['sql'] : null;
            $order = isset($args['order']) ? $args['order'] : null;
            $limit = isset($args['limit']) ? $args['limit'] : null;
            $group = isset($args['group']) ? ((array)$args['group']) : array();
            
            if ($limit != null) {
            
                if (is_array($limit)) {
                    
                    $limit = ' LIMIT ' . intval($limit[0]) . ','
                             . intval($limit[1]); 
                    
                } else {
                    
                    $limit = ' LIMIT 0,' . intval($limit);
                    
                }
                
            } else {

                $limit = null;
                
            }
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset($this->table)) {
                    
                    $table = $this->getTable(get_class($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
            
            $cc = false;
            
            while (($pos = array_search(strtolower ('COUNT(*)'),
                    array_map ('strtolower', $keys)))
            ) {
                
                unset ($keys[$pos]);
                
                $keys = array_values ($keys);
                
                $cc = true;
                
            }
            
            sort ($keys);
        
            $sql = "SELECT " . ($cc ? ('COUNT(*)'
                        . (count ($keys) ? ', ' : null)) : null)
                    . (count ($keys) ? '`' : null) . implode ('`, `', $keys)
                    . (count ($keys) ? '`' : null) . " FROM `" . $table . "`"
                    . (($sql) ? (' WHERE ' . $sql):null)
                    . (empty ($group) ? null : (' GROUP by '
                        . implode (', ', $group)))
                    . ($order ? (' ORDER by ' . $order) : null) . $limit;
            
            //return $sql; // Uncomment for sample output
            
            if (($results = $this->cache->get($sql))) {
                
                return $this->rt_obj($results);
                
            }
            
            $query = mysqli_query(Model::$con, $sql);
            
            $results = array();
            
            profile ($sql, $query, $query ? mysqli_num_rows($query) : 0);
            
            if ($query) {
                
                if (count($keys) == 1) {
                    
                    while ((list($row) = mysqli_fetch_row($query)))
                    
                        array_push($results, stripslashes($row));
                    
                } else {
                
                    while (($row = mysqli_fetch_array($query)))
                    {
                    
                            $current = array();
                    
                            while (list($k, $column) = each($row))
                            {
                        
                                if (is_numeric($k))
                                    $current[key($row)] = stripslashes($row[key($row)]);
                        
                            }
                    
                            array_push($results, $current);

                        }                
                
                }
            
                $this->cache->put($sql, $results);
                
            }
            else
            {
                
                trigger_error(mysqli_error(Model::$con));
                
            }
            
            mysqli_free_result($query);
            
            db_close();
            
            return $this->rt_obj($results);
            
        }
        
        final protected function escape ($arr)
        {
            
            foreach ($arr as $k=>$array)
            {
                
                if (is_array($array)) {

                    $arr[$k] = array_map(array($this, __FUNCTION__), $array);
                
                } else {
                    
                    $arr[$k] = stripslashes($array);
                    $arr[$k] = mysqli_real_escape_string(Model::$con, $array);
                    
                }
                
            }
            
            return $arr;
            
        }
        
        // Add a row to a table
        final function add ($data = array(), $table = null)
        {

            if (!isset($this->table)) {
                    
                $table = $this->getTable(get_class($this));
                    
            } else {
                    
                $table = $this->table;
                    
            }

            Controller::$objects[$table]->check_data($data, $this);
            
            db_reconn();
            
            $data = $this->escape($data);
            
            if ($table == null)
            {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
            
            $sql = "INSERT INTO `" . $table . "`" . " (`"
                    . implode ('`,`', array_keys ($data))
                    . "`) VALUES ('" . implode ("','", $data) . "')";
                        
            $query = mysqli_query(Model::$con, $sql);
            
            profile($sql, $query, $query ? mysqli_affected_rows(Model::$con) : 0);
            
            if (!$query) {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            db_close();
            
            $this->id = true;
            
            return $query;
            
        }
        
        // Return ID of Auto-Increment field from last INSERT query
        final function id ()
        {
            
            $id = false;

            if ($this->id) {

                $id = mysqli_insert_id();
                $this->id = false;
                
            }
            
            return $id;
            
        }
        
        // Edit database records
        final function edit ($data = array(), $sql = null, $table = null)
        {

            if (!isset($this->table)) {
                    
                $table = $this->getTable(get_class($this));
                    
            } else {
                    
                $table = $this->table;
                    
            }

            Controller::$objects[$table]->check_data($data, $this);
            
            db_reconn();
            
            $data = $this->escape($data);
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
            
            $statement = array();
            
            foreach (array_keys ($data) as $key)
            {
                array_push($statement, '`' . $table . '`.`'
                           . $key . "` = '" . $data[$key] . "'");
            }
                
            $sql = "UPDATE `" . $table . "` SET " . implode (',', $statement)
                    . (($sql) ? (' WHERE ' . $sql):null);
            
            $query = mysqli_query (Model::$con, $sql);
            
            profile ($sql, $query, $query ? mysqli_affected_rows(Model::$con) : 0);
            
            if (!$query) {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            db_close();
            
            return $query;
            
        }
        
        // Delete database records
        final function delete ($sql = null, $table = null)
        {
            
            db_reconn();
            
            if ($table == null) {
             
                if (!isset ($this->table)) {
                    
                    $table = $this->getTable (get_class ($this));
                    
                } else {
                    
                    $table = $this->table;
                    
                }
                
            }
                
            $sql = "DELETE FROM `" . $table . "`"
                    . (($sql) ? (' WHERE ' . $sql) : null);
            
            $query = mysqli_query (Model::$con, $sql);
            
            profile ($sql, $query, $query ? mysqli_affected_rows(Model::$con) : 0);
            
            if (!$query) {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            db_close();
            
            return $query;
            
        }
        
        // Fetch Complex Equi-Joins
        final function fetchByJoin ($args = array())
        {
            
            $tables = isset ($args['tables']) ? ((array)$args['tables']) : array();
            $keys = isset ($args['keys']) ? ((array)$args['keys']) : array();
            $joins = isset ($args['joins']) ? ((array)$args['joins']) : array();
            $sql = isset ($args['sql']) ? $args['sql'] : null;
            $order = isset ($args['order']) ? $args['order'] : null;
            $limit = isset ($args['limit']) ? $args['limit'] : null;
            $group = isset ($args['group']) ? ((array)$args['group']) : array();
            
            if ($limit != null) {
            
                if (is_array ($limit)) {
                    
                    $limit = ' LIMIT ' . intval ($limit[0]) . ','
                             . intval ($limit[1]); 
                    
                } else {
                    
                    $limit = ' LIMIT 0,' . intval ($limit);
                    
                }
                
            } else {

                $limit = null;
                
            }
            
            db_reconn();
            
            $columns = array();
            $conds = array();
            
/*            
 *          $num =  intval (((count ($joins) > 2) ? ((count ($joins) % 2)
 *                   ? (count ($joins)) : (count ($joins))) : 0) / 2);
 */
            $num = (count ($joins) > 2) ? (count ($joins) - 2) : 0;
            $i = 0;
            $cond = null;
            
            $results = array();
            $prefixes = array();
                
            ksort ($keys);
            
            foreach ($tables as $table)
            {
                
                $results[$table] = array();
                
                array_push ($prefixes, 'T' . (count ($prefixes) + 1));
                
            }
            
            foreach ($keys as $k => $value)
            {
                
                $table = $keys[$k];
                
                sort ($table);
            
                foreach ($table as $key)
                {
                    array_push ($columns, '`' . $k . '`.`' . $key . '` as '
                        . $prefixes[array_search ($k, $tables)] . '_' . $key);
                }

            }
            
            foreach ($joins as $join)
            {
                
/*                
 *              $cond .= $prefixes[array_search ($join[0]['table'], $tables)]
 *                      . '_' . $join[0]['key']  . ' = '
 *                      . $prefixes[array_search ($join[1]['table'], $tables)]
 *                      . '_' . $join[1]['key'];
 */
                
                $cond .= '`' . $join[0]['table'] . '`.`' . $join[0]['key'] 
                         . '` = `' . $join[1]['table'] . '`.`'
                         . $join[1]['key'] . '`';
                
                if ($i == count ($joins) - 1) {
                
                    for ($j = 0; $j < count ($joins) - 2; $j++)
                        $cond .= ')';
                    
                } else {
                    
                    $cond .= " AND ";
                    
                }
                
                if ($num > 0 && $i < count ($joins) - 2) {
                    
                    $cond .= "(";
                    
                }
                    
                
                $i++;
                
            }
            
            $sql = "SELECT " . implode (', ', $columns) . ' FROM `'
                    . implode ('`,`', $tables)
                    . '` WHERE (' . $cond . ')'
                    . (($sql) ? (' AND ' . $sql) : null)
                    . (empty ($group) ? null
                        : (' GROUP by ' . implode (', ', $group)))
                    . ($order ? (' ORDER by ' . $order) : null)
                    . $limit;
            
            //return $sql; //Uncomment this for sample SQL output
            
            if (($res = $this->cache->get($sql))) {
                
                return $this->rt_obj($res);
                
            }
            
            $query = mysqli_query(Model::$con, $sql);
            
            profile($sql, $query, $query ? mysqli_num_rows ($query) : 0);
            
            if ($query) {
                
                while (($row = mysqli_fetch_array ($query)))
                {
                    
                    $current = array();
                    
                    foreach ($tables as $table)
                        $current[$table] = array();
                    
                    while (list ($k, $column) = each ($row))
                    {
                        
                        if (is_numeric ($k)) {
                        
                            $k = explode ('_', key ($row));
                        
                            $prefix = $k[0];
                    
                            unset ($k[0]);
                        
                            $k = implode ('_', $k);
                        
                            $current[$tables[
                                    array_search($prefix, $prefixes)]
                                ][$k] = $row[key ($row)];
                            
                        }
                    
                    }
                    
                    while (list ($key, $now) = each ($current))
                        array_push ($results[$key], $now);
                    
                }
            
                $this->cache->put($sql, $results);
                
            } else {
                
                trigger_error (mysqli_error(Model::$con));
                
            }
            
            db_close();
            
            mysqli_free_result($query);
            
            return $this->rt_obj($results);
            
        }

        final function get_fields($table)
        {

            global $config;
            $file = BASE_PATH . APP_FOLDER . DS . 'tmp' . DS . 'schema' . DS . $table . '.cache';

            if (file_exists($file)) {

                if ((time() - filemtime($file)) >= $config['schemaCachingDuration']) {

                    unlink($file);
                    $columns = $this->fetchByQuery('SHOW COLUMNS FROM ' . $table);
                    $f = fopen($file, 'w');
                    fwrite($f, serialize($columns));
                    fclose($f);

                } else {

                    $columns = unserialize(file_get_contents($file));

                }

            } else {
                    
                $columns = $this->fetchByQuery('SHOW COLUMNS FROM ' . $table);
                $f = fopen($file, 'w');
                fwrite($f, serialize($columns));
                fclose($f);

            }

            $c = array();

            foreach ($columns as $col) {

                $c[] = $col['Field'];

            }

            return $c;

        }
    
    }

?>