<?php

class X
{

    public static $conn;

    public static $db = '';

    public static $tbl = '';

    public static $selectStr = '';

    public static $whereStr = '';

    public static $whereOrStr = '';

    public static $orderByStr = '';

    public static $groupByStr = '';

    public static $limitStr = '';

    public static $debugConfig = 0;
    
    public static $recursive = 0;

    function __construct()
    {}

    function __destruct()
    {
        self::clear();
    }

    public static function clear()
    {
        self::$conn = null;
        self::$db = null;
        self::$tbl = null;
        self::$selectStr = null;
        self::$whereStr = null;
        self::$whereOrStr = null;
        self::$debugConfig = null;
        self::$orderByStr = null;
        self::$groupByStr = null;
        self::$limitStr = null;
        self::$recursive = null;
    }

    public static function flush()
    {
        self::$tbl = null;
        self::$selectStr = null;
        self::$whereStr = null;
        self::$whereOrStr = null;
        self::$orderByStr = null;
        self::$groupByStr = null;
        self::$limitStr = null;
    }
    
    // get array to json
    public static function getJson($arr = '')
    {
        return json_encode($arr);
    }
    
    // get array to Object
    public static function getObject($arr = '')
    {
        return json_decode(json_encode($arr));
    }
    
    // /////////////////////////////////////////////////////////////////////////////
    public static function setup($constr, $user, $pass, $debugConfig = 0)
    {
        try {
            self::clear();
            self::$debugConfig = $debugConfig;
            $conn = new PDO($constr, $user, $pass);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conn = $conn;
            //"Connected successfully";
            return true;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span></hr> ' . " Connection failed: " . $e->getMessage();
        }
    }
    
    //
    
    // /////////////////////////////////////////////////////////////////////////////
    public static function getAll($sql)
    {
        try {
            // return self::$conn->query($sql, PDO::FETCH_ASSOC);
            
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            
            // set the resulting array to associative
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function getPrimeryKey($tbl)
    {
        $id = self::getAll('SHOW KEYS FROM ' . $tbl . ' WHERE Key_name = "PRIMARY"');
        return $id[0]['Column_name'];
    }

    public static function debug($v, $ex = 1)
    {
        echo '<pre>';
        print_r($v);
        echo '</pre>';
        if ($ex)
            exit();
    }

    public static function dx($v)
    {
        echo '<hr><span style="color:blue;">*****</span> <span style="color:green;"> ';
        print($v);
        echo ' </span><span style="color:blue;">*****</span> </hr>';
    }

    /*
     * Ge the relational schema for fetch related data;
     * @paramm $tbl table name
     * return sql for related data
     */
    public static function getRelationSchema($tbl)
    {
        $sql = " SELECT CONCAT( `CONSTRAINT_SCHEMA`, '.', table_name, '.', column_name ) AS 'foreignKey',column_name as foreignKeySimple, CONCAT( `REFERENCED_TABLE_SCHEMA`, '.', referenced_table_name, '.', referenced_column_name ) AS 'references', CONCAT( `REFERENCED_TABLE_SCHEMA`, '.',referenced_table_name) AS tableName, constraint_name AS 'constraintName', REFERENCED_TABLE_SCHEMA AS dbName FROM information_schema.key_column_usage WHERE `TABLE_NAME` = '$tbl' ";
        return self::getAll($sql);
    }

    /*
     * Ge the relational data each in array form;
     * @paramm $tbl table name
     * return sql for related data
     */
    public static function getRelatedDataEach($tbl, $id = '')
    {
        self::$recursive= 0;
        $results = self::getRelationSchema($tbl);
        $relatedTables = $data = $endData = $primeries = array();
         
        if (count($results) >= 1) {
            foreach ($results as $i => $v) {
                if (! empty($v['foreignKey']) && ! empty($v['references'])) {
                    
                    $relatedTables[$v['foreignKeySimple']] = self::load($v['tableName']);
                    $pid = self::getPrimeryKey($v['tableName']);
                    $primeries[$v['foreignKeySimple']] = $pid;
                }
            }
        }

        //setting index as primary id
        foreach ($relatedTables as $j=>$k){ 
            foreach ($k as $i=>$v){ 
                $data[$j][$v[$primeries[$j]]] = $v; 
            }
            
        } 
        
        //loading main table in array
        $endData = self::load($tbl, $id);
        foreach($endData as $i=>$v){
            foreach($v as $x=>$z){
                if(array_key_exists($x, $data)) {
                    if(array_key_exists($z, $data[$x])) {
                        $v[$x.'_X'] = $data[$x][$v[$x]];
                        $endData[$i] = $v;
                    }
                    
                    
                }
            }
        }
          
        return $endData;
    }
     
    /*
     * Ge the relational data query for fetch related data;
     * @paramm $tbl table name
     * return sql for related data
     */
    public static function getRelatedDataQuery($tbl, $id = '')
    {
        $results = self::getRelationSchema($tbl);
        $relatedTables = array();
        $returnSql = ' ';
        $select = 'SELECT ';
        $joins = '';
        $where = '';
        
        if (count($results) >= 1) {
            foreach ($results as $i => $v) {
                
                if (! empty($v['foreignKey']) && ! empty($v['references'])) {
                    $relatedTables[$v['dbName']] = $v['tableName'];
                    // concatinating related tables and fields
                    $select .= ' ' . $v['tableName'] . '.*, ';
                    $joins .= ' JOIN ' . $v['tableName'] . ' ON ' . $v['foreignKey'] . ' = ' . $v['references'];
                }
            }
        }
        
        $select .= ' ' . $tbl . '.* FROM ' . $tbl;
        
        if (! empty($id)) {
            // getting and setting in where primary id
            $pid = self::getPrimeryKey($tbl);
            $where = ' WHERE ' . $tbl . '.' . $pid . '=' . $id;
        }
        
        $returnSql .= $select . $joins . $where . ';';
        return array('sql'=>$returnSql,'tables'=>$relatedTables) ;
    }

    public static function load($tbl, $id = '')
    {
        if (self::$recursive==2) {
            return self::getRelatedDataEach($tbl, $id);
        }
        
        $pid = self::getPrimeryKey($tbl);
        
        try {
            
            if (! empty($id)) {
                
                $id = self::escapeTags($id);
                $sql = 'SELECT * FROM ' . $tbl . ' WHERE ' . $pid . '=' . $id;
            } else
                $sql = 'SELECT * FROM ' . $tbl . ' WHERE 1';
            
            if (self::$debugConfig)
                self::dx($sql);
                
            // recursive query
            if (self::$recursive==1) {
                $response = self::getRelatedDataQuery($tbl, $id);
                $sql = $response['sql'];
            }
                
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            
            // set the resulting array to associative
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (self::$recursive==2) {
                /*
                foreach($response['tables'] as $i=>$r) {
                    
                    $getFieldNames = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$i."' AND `TABLE_NAME`='".str_replace($i.'.', '', $r)."'";
                    echo $getFieldNames;
                    debug(self::getAll($getFieldNames));
                }
                */
                return $result;
                
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function setRecursive($r = 0)
    { 
        self::$recursive = $r;
    }
    
    public static function manage($tbl, $c = 0)
    {
        self::flush();
        self::$tbl = $tbl;
        
        if ($c)
            self::setTable();
        
        $sql = 'DESCRIBE ' . $tbl;
        try {
            
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            
            // set the resulting array to associative
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($result as $v)
                $obj[$v] = $v;
            
            $obj = (object) $obj;
            return $obj;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . "</hr>" . $e->getMessage();
        }
    }

    public static function escapeTags($str)
    {
        //return $str;
        // $sKeys = array('&', '\'', '<', '>' ,'%', '#', '?', '(', ')','`');
        // $sValues = array('\&', '\'', '\<', '\>', '\%', '\#', '\?', '\(', '\)','\`');
        return str_replace("'", "''", $str);
    }

    public static function emptyX($tbl = '')
    {
        if (empty($tbl))
            return 0;
        $sql = 'TRUNCATE ' . $tbl;
        
        try {
            // use exec() because no results are returned
            return self::$conn->exec($sql);
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function drop($tbl = '')
    {
        if (empty($tbl))
            return 0;
        $sql = 'DROP TABLE ' . $tbl;
        
        try {
            // use exec() because no results are returned
            return self::$conn->exec($sql);
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function select($fieldsStr)
    {
        if (! empty($fieldsStr)) {
            
            self::$selectStr .= ' SELECT ' . $fieldsStr . ' FROM ' . self::$tbl;
        }
        
        return self::getResults();
    }

    public static function orderBy($key = '', $otype = 'DESC')
    {
        if (! empty($key)) {
            self::$orderByStr .= ' ORDER BY ' . $key . '  ' . $otype;
        }
        return self::getResults();
    }

    public static function groupBy($key = '')
    {
        if (! empty($key)) {
            self::$groupByStr .= ' GROUP BY ' . $key;
        }
        
        return self::getResults();
    }

    public static function limit($start = 0, $end = 0)
    {
        if (! empty($end)) {
            self::$limitStr .= ' LIMIT ' . $start . ' , ' . $end;
        } else
            self::$limitStr .= ' LIMIT ' . $start;
        
        return self::getResults();
    }

    public static function where($key = '', $op = '', $val = '')
    {
        if (! empty($val)) {
            $val = self::escapeTags($val);
            
            self::$whereStr .= ' AND ';
            
            if (is_numeric($val))
                self::$whereStr .= $key . ' ' . $op . ' ' . $val . ' ';
            else
                self::$whereStr .= $key . ' ' . $op . ' \'' . $val . '\' ';
        }
        
        if (empty($val) && ! empty($op) && ! empty($key)) {
            $op = self::escapeTags($op);
            $key = self::escapeTags($key);
            
            self::$whereStr .= ' AND ';
            
            if (is_numeric($op))
                self::$whereStr .= $key . ' = ' . $op . ' ';
            else
                self::$whereStr .= $key . ' = \'' . $op . '\' ';
        }
        
        return self::getResults();
    }

    public static function whereOr($key = '', $op = '', $val = '')
    {
        if (! empty($val)) {
            $val = self::escapeTags($val);
            
            self::$whereStr .= ' OR ';
            if (is_numeric($val))
                self::$whereStr .= $key . ' ' . $op . ' ' . $val . ' ';
            else
                self::$whereStr .= $key . ' ' . $op . ' \'' . $val . '\' ';
        }
        
        if (empty($val) && ! empty($op) && ! empty($key)) {
            $op = self::escapeTags($op);
            $key = self::escapeTags($key);
            
            self::$whereStr .= ' OR ';
            
            if (is_numeric($op))
                self::$whereStr .= $key . ' = ' . $op . ' ';
            else
                self::$whereStr .= $key . ' = \'' . $op . '\' ';
        }
        
        return self::getResults();
    }

    public static function getResults()
    {
        try {
            if (empty(self::$selectStr))
                $sql = 'SELECT * FROM ' . self::$tbl . ' WHERE ' . self::getPrimeryKey(self::$tbl);
            else
                $sql = self::$selectStr . ' WHERE ' . self::getPrimeryKey(self::$tbl);
            
            $sql .= ' ' . self::$whereStr;
            $sql .= ' ' . self::$groupByStr;
            $sql .= ' ' . self::$orderByStr;
            $sql .= ' ' . self::$limitStr;
            
            if (self::$debugConfig)
                self::dx($sql);
            
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            
            // set the resulting array to associative
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . '<hr>' . $e->getMessage() . '<hr>';
        }
    }

    public static function paginateByArray($length = 10, $current_page = 0, $data = '')
    {
        try {
            
            if (empty($data))
                $data = self::getResults();
            
            $result = array();
            $start = 0;
            if ($current_page >= 1)
                $start = $length * $current_page;
            
            if (! empty($data)) {
                $total_items = count($data);
                
                if ($total_items > $length)
                    $total_pages = $total_items / $length;
                else
                    $total_pages = 1;
                
                $previous_link = '';
                if ($total_items > $length) {
                    $next_link = 'page=' . ($current_page + 1);
                    if ($current_page > 1)
                        $previous_link = 'page=' . ($current_page - 1);
                } else {
                    $next_link = '';
                    $previous_link = '';
                }
                
                $result['pagination']['total_items'] = $total_items;
                $result['pagination']['total_pages'] = $total_pages;
                $result['pagination']['current_page'] = $current_page;
                $result['pagination']['length'] = $length;
                $result['pagination']['previous_link'] = $previous_link;
                $result['pagination']['next_link'] = $next_link;
                $result['pagination']['start'] = $start;
                $result['items'] = array_slice($data, $start, $length);
            }
            
            return $result;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . '<hr>' . $e->getMessage() . '<hr>';
        }
    }

    public static function paginate($length = 10, $current_page = 0)
    {
        try {
            
            $total_items = self::getAll('select count(*) as total from ' . self::$tbl);
            
            $total_items = $total_items[0]['total'];
            $result = array();
            $start = 0;
            $end = $length;
            
            if ($current_page > 1) {
                $start = ($length * $current_page) - $length;
                // $end = ($length * $current_page)+$length;
            }
            
            self::limit($start, $length);
            
            $data = self::getResults();
            
            if (! empty($total_items)) {
                
                if ($total_items > $length)
                    $total_pages = $total_items / $length;
                else
                    $total_pages = 1;
                
                $previous_link = '';
                if ($total_items > $length) {
                    $next_link = 'page=' . ($current_page + 1);
                    if ($current_page > 1)
                        $previous_link = 'page=' . ($current_page - 1);
                } else {
                    $next_link = '';
                    $previous_link = '';
                }
                
                $result['pagination']['total_items'] = $total_items;
                $result['pagination']['total_pages'] = intval($total_pages);
                $result['pagination']['current_page'] = $current_page;
                $result['pagination']['length'] = $length;
                $result['pagination']['previous_link'] = $previous_link;
                $result['pagination']['next_link'] = $next_link;
                $result['pagination']['start'] = $start;
                $result['items'] = $data;
            }
            
            return $result;
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . '<hr>' . $e->getMessage() . '<hr>';
        }
    }

    public static function setTable($arr = '')
    {
        if (self::$conn->query('SHOW TABLES LIKE \'' . self::$tbl . '\'')->rowCount() > 0) {
            $sql = '';
        } else {
            
            $sql = 'CREATE TABLE ' . self::$tbl . ' ( ';
            $sql .= 'id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY ';
            $i = 1;
            if (! empty($arr)) {
                foreach ($arr as $k => $v) {
                    
                    if (count($arr) != $i)
                        $sql .= $k . ' VARCHAR(255) NULL, ';
                    else
                        $sql .= $k . ' VARCHAR(255) NULL ';
                    
                    $i ++;
                }
            }
            
            $sql .= ');';
            
            // return $sql;
            try {
                // use exec() because no results are returned
                self::$conn->exec($sql);
            } catch (PDOException $e) {
                echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
            }
        }
    }

    public static function save($arr)
    {
        self::setTable($arr);
        $pid = self::getPrimeryKey(self::$tbl);
        
        if (! isset($arr[$pid]))
            $sql = self::arrayToSql($arr);
        else {
            $result = self::load(self::$tbl, $arr[$pid]);
            if ($result)
                $sql = self::arrayToSql($arr, 2);
            else
                $sql = self::arrayToSql($arr);
        }
        
        try {
            if (self::$debugConfig)
                self::dx($sql);
            
            self::$conn->exec($sql);
            return self::$conn->lastInsertId();
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function exec($sql)
    {
        try {
            return self::$conn->exec($sql);
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
        /* */
    }

    public static function update($arr, $keyVal)
    {
        /*
         * echo self::setTable($arr);
         *
         * if(!isset($arr['id']))
         * return false;
         *
         * $sql = self::arrayToSql($arr,2);
         * // echo $sql;
         *
         * try{
         * //self::$conn->exec($sql);
         * }
         * catch(PDOException $e)
         * {
         * echo $sql . "</hr>" . $e->getMessage();
         * }
         */
    }

    public static function delete($tbl, $id)
    {
        $pid = self::getPrimeryKey($tbl);
        $sql = 'DELETE FROM ' . $tbl . ' WHERE ' . $pid . '=' . $id;
        try {
            self::$conn->exec($sql);
        } catch (PDOException $e) {
            echo '<br><span style="color:red;">Line #' . __LINE__ . ' (/X.php) </span> ' . $sql . "</hr>" . $e->getMessage();
        }
    }

    public static function arrayToSql($arr = '', $type = 1)
    {
        if (empty($arr))
            return '';
        $sql = $keys = $values = '';
        $i = 1;
        if ($type == 1) {
            $sql = 'INSERT INTO ' . self::$tbl . ' (';
            
            foreach ($arr as $k => $v) {
                
                if (count($arr) != $i) {
                    $keys .= $k . ', ';
                    $values .= '\'' . self::escapeTags($v) . '\', ';
                } else {
                    $keys .= $k . ')';
                    $values .= '\'' . self::escapeTags($v) . '\')';
                }
                
                $i ++;
            }
            
            $sql .= $keys . ' VALUES (' . $values . ';';
        }
        
        if ($type == 2) {
            // UPDATE `myguests` SET `firstname` = 'fa', `lastname` = 'na' WHERE `myguests`.`id` = 1;
            $pid = self::getPrimeryKey(self::$tbl);
            $sql = 'UPDATE ' . self::$tbl . ' SET ';
            foreach ($arr as $k => $v) {
                
                if (count($arr) != $i) {
                    $sql .= $k . '= ';
                    $sql .= '\'' . self::escapeTags($v) . '\', ';
                } else {
                    $sql .= $k . '= ';
                    $sql .= '\'' . self::escapeTags($v) . '\' ';
                }
                
                $i ++;
            }
            
            $sql .= ' WHERE ' . $pid . '=' . $arr[$pid];
        }
        
        if ($type == 3) {}
        
        return $sql;
    }
}
