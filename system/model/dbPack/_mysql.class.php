<?php

/**
 * MYSQL
 *
 * @package db class
 */
require_once '_db.class.php';
class _mysql extends _db {
    
    public $options = array ();
    
    private $querynum = 0;
    
    private $link;
    
    private $query;
    
    private $histories;
    
    private $goneaway = 5;

    private $reconn_num = 0;
    
    private static $instance = false;
    
    private function __construct() {
    }
    
    private function __clone() {
    }
    
    public static function get_instance() {
        if (!self :: $instance instanceof self) {
            self :: $instance = new self();
        }
        return self :: $instance;
    }
    
    public function set_options(array $options) {
        if ($options && is_array($options)) {
            foreach ($options as $name => $value) {
                $this->options[$name] = $value;
            }
        } else {
            $this->options = array (
                'dbhost' => 'localhost',
                'dbuser' => 'root',
                'dbpw' => '',
                'dbport'=> 3306,
                'dbname' => 'test',
                'dbcharset' => 'utf8',
                'pconnect' => 0,
                'debug' => false,
                'tablepre' => '',
                'time' => ''
            );
        }
        $this->connect();
    }
    
    protected function connect() {
        if(empty($this->options['dbport'])){
            $this->options['dbport'] = 3306;
        }

        try {
            if ($this->options['pconnect']) {
                $this->link = @mysql_pconnect($this->options['dbhost'].':'.$this->options['dbport'], $this->options['dbuser'], $this->options['dbpw']);
            } else {
                $this->link = @mysql_connect($this->options['dbhost'].':'.$this->options['dbport'], $this->options['dbuser'], $this->options['dbpw']);
            }
            if(!$this->link) {
                throw new Exception('Can not connect to DB server #['.$this->errno().']'.$this->error());
            }
        } catch(Exception $e) {
            $this->re_connect($e);
        }
        
        if ($this->version() > '4.1') {
            if ($this->options['dbcharset']) {
                //mysql_query('SET NAMES ' . $this->options['dbcharset']);
                mysql_query('SET character_set_connection='.$this->options['dbcharset'].', character_set_results='.$this->options['dbcharset'].', character_set_client=binary', $this->link);
            }
            
            if ($this->version() > '5.0.1') {
                mysql_query('SET sql_mode=""', $this->link);
            }
        }
        
        if ($this->options['dbname']) {
            mysql_select_db($this->options['dbname'], $this->link);
        }
    }
	
	protected function re_connect($e) {
		$this->reconn_num++;
		if($this->reconn_num > 1) {
			die($e->getMessage());
		} else {
			$this->connect();
		}
	}
    
    protected function set_attrib($attrib) {
        if (!empty ($attrib)) {
            switch ($attrib) {
                case 'BOTH' :
                    $result = MYSQL_BOTH;
                    break;
                case 'ASSOC' :
                    $result = MYSQL_ASSOC;
                    break;
                case 'NUM' :
                    $result = MYSQL_NUM;
                    break;
                default :
                    $result = MYSQL_BOTH;
                    break;
            }
            return $result;
        }
    }
    
    public function query($sql, $cachetime = false) {
        if (!$this->query = mysql_query($sql, $this->link)) {
            if($this->options['debug']) {
                $this->halt('MySQL Query Error', $sql);
            } else {
                return false;
            }
        }
        $this->querynum++;
        $this->histories[] = $sql;
        return $this->query;
    }
    
    public function get_var($sql) {
        if($query = $this->query($sql)) {
            list ($var) = mysql_fetch_array($query);
            return $var;
        }
    }
    
    public function get_col($sql) {
        if($query = $this->query($sql)) {
            return mysql_num_rows($query);
        }
    }
    
    public function get_row($sql, $attrib = 'BOTH') {
        if($query = $this->query($sql)) {
            return mysql_fetch_array($query, $this->set_attrib($attrib));
        }
    }
    
    public function get_results($sql, $attrib = 'BOTH') {
        $array = array ();
        if($query = $this->query($sql)) {
            $query = $this->query($sql);
            while ($data = mysql_fetch_array($query, $this->set_attrib($attrib))) {
                $array[] = $data;
            }
            return $array;
        }
    }
    
    public function affected_rows() {
        if($this->link) {
            return mysql_affected_rows($this->link);
        }
    }
    
    public function error() {
        return $this->link ? mysql_error($this->link) : mysql_error();
    }
    
    public function errno() {
        return $this->link ? mysql_errno($this->link) : mysql_errno();
    }
    
    public function insert_id() {
        return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->get_var('SELECT last_insert_id()');
    }
    
    public function start_transaction() {
        $this->query('START TRANSACTION');
    }
    
    public function commit() {
        $this->query('COMMIT');
    }
    
    public function rollback() {
        $this->query('ROLLBACK');
    }
    
    public function xa_start($xid) {
        $this->query('XA START "' . $xid . '"');
    }
    
    public function xa_end($xid) {
        $this->query('XA END "' . $xid . '"');
    }
    
    public function xa_prepare($xid) {
        $this->query('XA PREPARE "' . $xid . '"');
    }
    
    public function xa_commit($xid) {
        $this->query('XA COMMIT "' . $xid . '"');
    }
    
    public function xa_rollback($xid) {
        $this->query('XA ROLLBACK "' . $xid . '"');
    }
    
    public function db_name() {
        return $this->options['dbname'];
    }
    
    public function version() {
        if($this->link) {
            return mysql_get_server_info($this->link);
        }
    }
    
    public function halt($message = '', $sql = '') {
        $error = $this->error();
        $errorno = $this->errno();
        if ($errorno == 2006 && $this->goneaway-- > 0) {
            self :: set_options($this->options);
            $this->query($sql);
        } else {
            $s = '<b>Error:</b>' . $error . '<br />';
            $s .= '<b>Errno:</b>' . $errorno . '<br />';
            $s .= '<b>SQL:</b>' . $sql;
            exit ($s);
        }
    }
    
    public function free_result() {
        mysql_free_result($this->query);
    }
    
    public function close_db() {
        if($this->link) {
            mysql_close($this->link);
        }
    }
    
    public function __destruct() {
        if (!empty ($this->query) && is_resource($this->query)) {
            $this->free_result();
        }
        if (!empty ($this->link)) {
            $this->close_db();
        }
    }
}
