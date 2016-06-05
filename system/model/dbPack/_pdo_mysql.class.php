<?php

/**
 * PDO_MYSQL
 *
 * @package db class
 */
require_once '_db.class.php';
class _pdo_mysql extends _db {
    
    public $options = array ();
    
    private $querynum = 0;
    
    private $link;
    
    private $query;
    
    private $exec;
    
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

        $dsn = 'mysql:dbname=' . $this->options['dbname'] . ';host=' . $this->options['dbhost'].';port=' . $this->options['dbport'];
        
        try {
            if($this->options['pconnect']) {
                $this->link = new PDO($dsn, $this->options['dbuser'], $this->options['dbpw'],array(PDO::ATTR_PERSISTENT => true));
            } else {
                $this->link = new PDO($dsn, $this->options['dbuser'], $this->options['dbpw']);
            }
        } catch (PDOException $e) {
            $this->re_connect($e);
        }
        
        if ($this->version() > '4.1') {
            if ($this->options['dbcharset']) {
                //$this->link->exec('SET NAMES ' . $this->options['dbcharset']);
                $this->link->exec('SET character_set_connection='.$this->options['dbcharset'].', character_set_results='.$this->options['dbcharset'].', character_set_client=binary');
            }
            
            if ($this->version() > '5.0.1') {
                $this->link->exec('SET sql_mode=""');
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
        if(is_object($this->link)) {
            if (!empty ($attrib)) {
                switch ($attrib) {
                    case 'BOTH' :
                        $result = PDO :: FETCH_BOTH;
                        break;
                    case 'ASSOC' :
                        $result = PDO :: FETCH_ASSOC;
                        break;
                    case 'NUM' :
                        $result = PDO :: FETCH_NUM;
                        break;
                    default :
                        $result = PDO :: FETCH_BOTH;
                        break;
                }
                $this->link->setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, $result);
            }
        }
    }
    
    public function query($sql, $cachetime = false) {
        if(is_object($this->link)) {
            $this->query = $this->link->prepare($sql);
            if (!$result = $this->query->execute()) {
                if($this->options['debug']) {
                    $this->halt('MySQL Query Error', $sql);
                } else {
                    return false;
                }
            }
            return $result;
        }
    }
    
    public function fetch($sql, $cachetime = false) {
        if(is_object($this->link)) {
            $this->query = $this->link->prepare($sql);
            if (!$this->query->execute()) {
                if($this->options['debug']){
                    $this->halt('MySQL Query Error', $sql);
                } else{
                    return false;
                }
            }
            $this->querynum++;
            $this->histories[] = $sql;
            return $this->query;
        }
    }
    
    public function get_var($sql) {
        if($query = $this->fetch($sql)) {
            list ($var) = $query->fetch();
            return $var;
        }
    }
    
    public function get_col($sql) {
        if($query = $this->fetch($sql)) {
            return $query->rowCount();
        }
    }
    
    public function get_row($sql, $attrib = 'BOTH') {
        $this->set_attrib($attrib);
        if($query = $this->fetch($sql)) {
            return $query->fetch();
        }
    }
    
    public function get_results($sql, $attrib = 'BOTH') {
        $array = array ();
        $this->set_attrib($attrib);
        if($query = $this->fetch($sql)) {
            return $query->fetchAll();
        }
    }
    
    public function affected_rows() {
        if(is_object($this->query)) {
            return $this->query->rowCount();
        } else{
            return -1;
        }
    }
    
    public function error() {
        return $this->query->errorInfo();
    }
    
    public function errno() {
    }
    
    public function insert_id() {
        return ($id = $this->link->lastInsertId()) >= 0 ? $id : $this->get_var('SELECT last_insert_id()');
    }
    
    public function start_transaction() {
        //$this->link->exec('START TRANSACTION');
        $this->link->beginTransaction();
    }
    
    public function commit() {
        //$this->link->exec('COMMIT');
        $this->link->commit();
    }
    
    public function rollback() {
        //$this->link->exec('ROLLBACK');
        $this->link->rollBack();
    }
    
    public function xa_start($xid) {
        $this->link->exec('XA START "' . $xid . '"');
    }
    
    public function xa_end($xid) {
        $this->link->exec('XA END "' . $xid . '"');
    }
    
    public function xa_prepare($xid) {
        $this->link->exec('XA PREPARE "' . $xid . '"');
    }
    
    public function xa_commit($xid) {
        $this->link->exec('XA COMMIT "' . $xid . '"');
    }
    
    public function xa_rollback($xid) {
        $this->link->exec('XA ROLLBACK "' . $xid . '"');
    }
    
    public function db_name() {
        return $this->options['dbname'];
    }
    
    public function version() {
        if(is_object($this->link)) {
            return $this->link->getAttribute(PDO :: ATTR_SERVER_VERSION);
        }
    }
    
    public function halt($message = '', $sql = '') {
        list (, $errorno, $error) = $this->error();
        if ($errorno == 2006 && $this->goneaway-- > 0) {
            self :: set_options($this->options);
            $this->fetch($sql);
        } else {
            $s = '<b>Error:</b>' . $error . '<br />';
            $s .= '<b>Errno:</b>' . $errorno . '<br />';
            $s .= '<b>SQL:</b>' . $sql;
            exit ($s);
        }
    }
    
    public function free_result() {
        unset($this->query);
    }
    
    public function close_db() {
        unset($this->link);
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
