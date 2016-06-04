<?php

/**
 * MYSQLI
 *
 * @package db class
 */
require_once '_db.class.php';
class _mysqli extends _db {
    
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
        if(empty($this->options['dbport'])) {
            $this->options['dbport'] = 3306;
        }
        try {
            $this->link = @new mysqli($this->options['dbhost'], $this->options['dbuser'], $this->options['dbpw'], $this->options['dbname'], $this->options['dbport']);
            if($this->link->connect_error) {
                throw new Exception('Can not connect to DB server #['.$this->link->connect_errno.']'.$this->link->connect_error);
            }
        } catch (Exception $e) {
			$this->re_connect($e);
        }
        
        if ($this->version() > '4.1') {
            if ($this->options['dbcharset']) {
                //mysql_query('SET NAMES ' . $this->options['dbcharset']);
                $this->link->query('SET character_set_connection='.$this->options['dbcharset'].', character_set_results='.$this->options['dbcharset'].', character_set_client=binary');
            }
            
            if ($this->version() > '5.0.1') {
                $this->link->query('SET sql_mode=""');
            }
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
                    $result = MYSQLI_BOTH;
                    break;
                case 'ASSOC' :
                    $result = MYSQLI_ASSOC;
                    break;
                case 'NUM' :
                    $result = MYSQLI_NUM;
                    break;
                default :
                    $result = MYSQLI_BOTH;
                    break;
            }
            return $result;
        }
    }
    
    public function query($sql, $cachetime = false) {
        if (!$this->query = $this->link->query($sql)) {
            if($this->options['debug']) {
                $this->halt('MySQL Query Error', $sql);
            } else {
                return false;
            }
        }
        $this->querynum++;
        $this->histories[] = $sql;
		//$this->link->next_result();
        return $this->query;
    }
    
    public function get_var($sql) {
        if($query = $this->query($sql)) {
            list ($var) = $query->fetch_array();
            return $var;
        }
    }
    
    public function get_col($sql) {
        if($query = $this->query($sql)) {
            return $query->num_rows;
        }
    }
    
    public function get_row($sql, $attrib = 'BOTH') {
        if($query = $this->query($sql)) {
            return $query->fetch_array($this->set_attrib($attrib));
        }
    }
    
    public function get_results($sql, $attrib = 'BOTH') {
        $array = array ();
        if($query = $this->query($sql)) {
            while ($data = $query->fetch_array($this->set_attrib($attrib))) {
                $array[] = $data;
            }
            return $array;
        }
    }
    
    public function affected_rows() {
        if(!$this->link->connect_error) {
            return $this->link->affected_rows;
        }
    }
    
    public function error() {
        if(!$this->link->connect_error) {
            return $this->link->error;
        }
    }
    
    public function errno() {
        if(!$this->link->connect_error) {
            return $this->link->errno;
        }
    }
    
    public function insert_id() {
        return ($id = $this->link->insert_id) >= 0 ? $id : $this->get_var('SELECT last_insert_id()');
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
        if(!$this->link->connect_error) {
            return $this->link->server_version;
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
        $this->query->close();
    }
    
    public function close_db() {
        if(!$this->link->connect_error) {
            $this->link->close();
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
