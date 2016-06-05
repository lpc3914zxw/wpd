<?php

/**
 * MONGO
 *
 * @package db class
 */

class _mongo {

    public $options = array ();

    private $querynum = 0;

    private $link;

    private $query;

    private $histories;

    private $goneaway = 5;

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
                'dbuser' => '',
                'dbpw' => '',
                'dbport'=> 27017,
                'dbname' => '',
                'dbcharset' => 'utf8',
                'debug' => false,
            );
        }
        $this->connect();
    }

    protected function connect() {
        if(empty($this->options['dbport'])) {
            $this->options['dbport'] = 27017;
        }
        try {
            $param = 'mongodb://';
			$default_db = '';
            if ($this->options['dbuser'] && $this->options['dbpw']) {
                $param .= $this->options['dbuser'].':'.$this->options['dbpw'].'@';
				$default_db = '/admin';
            }
            $param .= $this->options['dbhost'].':'.$this->options['dbport'].$default_db;
            $this->link = new MongoClient($param);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * [auto_increment 自动编号计算]
     * @param  [type] $name       [编号名称]
     * @param  [type] $default_id [默认值]
     * @return [type]             [结果]
     */
    protected function auto_increment($name,$default_id) {
        $update = array('$inc'=>array('id'=>$default_id));
        $query = array('_id'=>$name);
        $command = array(
            'findandmodify'=>'AUTO_INCREMENT',
            'update'=>$update,
            'query'=>$query,
            'new'=>true,//是否返回最新(modify之后)的记录
            'upsert'=>true//如果记录不存在是否插入新的记录
        );
        $auto_increment = $this->link->selectDB($this->options['dbname'])->command($command);
        return $auto_increment['value']['id'];
    }

    /**
     * [use_dbname 解析库名/表名]
     * @param  [type] $table_name [库名.表名]
     * @return [type]             [结果]
     */
    protected function use_dbname($table_name) {
        if(strstr($table_name,'.') != '') {
            $table_arr = explode('.',$table_name);
            $db_name = $table_arr[0];
            $table_name = $table_arr[1];
        }
        if(empty($db_name)) {
            $db_name = $this->options['dbname'];
        }
        return array('db_name'=>$db_name,'table_name'=>$table_name);
    }

    /**
     * [build_cursor 返回mongodb cursor]
     * @param  [type] $table_name       [表名]
     * @param  [type] $query_condition  [条件]
     * @param  [type] $result_condition [排序/条数]
     * @param  [type] $fields           [显示字段]
     * @return [type]                   [结果]
     */
    protected function build_cursor($table_name,$query_condition,$result_condition,$fields) {
        if(!empty($fields[0]) && in_array($fields[0],array('COUNT','_id'))) {
            $field = $fields[0];
            $result_condition = array();
            $fields = array('_id');
        }
        $table_arr = $this->use_dbname($table_name);
        $cursor = $this->link->selectCollection($table_arr['db_name'],$table_arr['table_name'])->find($query_condition,$fields);
        if (!empty($result_condition['sort']) && is_array($result_condition['sort'])) {
            $cursor->sort($result_condition['sort']);
        }
        if (!empty($result_condition['limit']) && is_array($result_condition['limit'])) {
            if(!empty($result_condition['limit']['offset'])) {
                $cursor->skip($result_condition['limit']['offset']);
            }
            if(!empty($result_condition['limit']['rows'])) {
                $cursor->limit($result_condition['limit']['rows']);
            }
        }
        return $cursor;
    }

    /**
     * [fetch 返回cursor成数组]
     * @param  [type] $table_name       [表名]
     * @param  [type] $query_condition  [条件]
     * @param  [type] $result_condition [排序/条数]
     * @param  [type] $fields           [显示字段]
     * @return [type]                   [结果]
     */
    protected function fetch($table_name,$query_condition,$result_condition,$fields){
        $cursor = $this->build_cursor($table_name,$query_condition,$result_condition,$fields);
        try {
            $result = array();
            if(!empty($fields[0]) && $fields[0] == 'COUNT') {
            	$result[][$fields[0]] = $cursor->count(true);
            } else {
            	$array = iterator_to_array($cursor);
            	if(!empty($fields[0]) && $fields[0] == '_id') {
            		$result[][$fields[0]] = key($array);
            	} else {
            		foreach((array)$array as $val) {
            			$result[] = $val;
            		}
            	}
            }
            return $result;
        } catch (Exception $e) {
            if($this->options['debug']) {
                $this->halt($e->getMessage());
            } else {
                return false;
            }
        }
    }

    /**
     * [getGridfs 获取gridFS的handle]
     * @param  string $dbname [表名]
     * @return [type]         [结果]
     */
    public function getGridfs($dbname=''){
        if( ! empty($db)){
            $db = $this->link->selectDB($dbname);
        }else{
            $db = $this->link->selectDB($this->options['dbname']);
        }
        if( ! $db) $this->halt('no db');
        return $db->getGridFS();
    }

    /**
     * [getGridfs 执行insert/update等query操作]
     * @param  string $dbname [表名]
     * @param  string $record [记录]
     * @param  array  $where  [条件]
     * @param  array  $option [选项值]
     * @return [type]         [结果]
     */
    public function query($table_name,$record,$where = array(),$option = array('upsert' => true,'multiple' => false,'default_id' => 0)) {
        $table_arr = $this->use_dbname($table_name);
        $default_id = $option['default_id'];
        unset($option['default_id']);
        $collection = $this->link->selectCollection($table_arr['db_name'],$table_arr['table_name']);
        if(empty($record['_id']) && $default_id > 0) {
            $cursor = iterator_to_array($collection->find(array(),array('_id'=>true))->sort(array('_id'=>-1))->limit(1));
            if(is_array($cursor) && !empty($cursor)) {
                foreach((array)$cursor as $val) {
                    $record = array_merge(array('_id'=>++$val['_id']),$record);
                }
            }
            elseif(!empty($default_id)) {
                $record = array_merge(array('_id'=>$default_id),$record);
            }
        }
        if(empty($where)) {
            $result = $collection->save($record);
        } else {
            $result = $collection->update($where,$record,$option);
        }
        if ($result == false) {
            if($this->options['debug']) {
                $this->halt('Mongo Query Error', $record);
            } else {
                return false;
            }
        }
        $this->querynum++;
        $this->query = $record;
        $this->histories[] = $record;
        return $result;
    }

    /**
     * [group 获取mongodb group结果]
     * @param  [type] $table_name [表名]
     * @param  [type] $group      [group操作]
     * @return [type]             [结果]
     */
    public function group($table_name,$group) {
        extract($group);
        $table_arr = $this->use_dbname($table_name);
        return $this->link->selectCollection($table_arr['db_name'],$table_arr['table_name'])->group($keys,$initial,$reduce,$condition);
    }

    /**
     * [command 执行mongobd的command指令]
     * @param  [type] $table_name       [表名]
     * @param  [type] $command          [command指令]
     * @param  [type] $result_condition [排序/条数]
     * @return [type]                   [结果]
     */
    public function command($table_name,$command,$result_condition){
        $table_arr = $this->use_dbname($table_name);
        $result = $this->link->$table_arr['db_name']->command($command);
        if($result['ok'] == 0){
            return false;
        }
        $statsCollection = $this->link->$table_arr['db_name']->selectCollection($result['result']);
        $cursor = $statsCollection->find();
        if (is_array($result_condition['sort']) && !empty($result_condition['sort'])) {
            $cursor->sort($result_condition['sort']);
        }
        if (is_array($result_condition['limit']) && !empty($result_condition['limit'])) {
            if(!empty($result_condition['limit']['offset'])) {
                $cursor->skip($result_condition['limit']['offset']);
            }
            if(!empty($result_condition['limit']['rows'])) {
                $cursor->limit($result_condition['limit']['rows']);
            }
        }
        return iterator_to_array($cursor,false);
    }

    /**
     * [get_var 获取单列内容]
     * @param  [type] $table_name      [表名]
     * @param  [type] $query_condition [条件]
     * @param  array  $fields          [显示字段]
     * @return [type]                  [结果]
     */
    public function get_var($table_name,$query_condition,$fields=array()) {
        $field = $fields[0];
        if($result = $this->fetch($table_name,$query_condition,array('limit'=>array('offset'=>0,'rows'=>1)),$fields)) {
            return $result[0][$field];
        }
    }

    /**
     * [get_results 获取行结果]
     * @param  [type] $table_name       [表名]
     * @param  [type] $query_condition  [条件]
     * @param  array  $result_condition [排序/条数]
     * @param  array  $fields           [显示字段]
     * @return [type]                   [结果]
     */
    public function get_row($table_name,$query_condition,$result_condition=array(),$fields=array()) {
        $result_condition['limit'] = array('offset'=>0,'rows'=>1);
        if($result = $this->fetch($table_name,$query_condition,$result_condition,$fields)) {
            return $result[0];
        }
    }

    /**
     * [get_results 获取结果集]
     * @param  [type] $table_name       [表名]
     * @param  [type] $query_condition  [条件]
     * @param  array  $result_condition [排序/条数]
     * @param  array  $fields           [显示字段]
     * @return [type]                   [结果]
     */
    public function get_results($table_name,$query_condition,$result_condition=array(),$fields=array()) {
        if($result = $this->fetch($table_name,$query_condition,$result_condition,$fields)) {
            return $result;
        }
    }

    /**
     * [distinct 去重显示]
     * @param  [type] $table_name [表名]
     * @param  [type] $field      [显示字段]
     * @return [type]             [结果]
     */
    public function distinct($table_name,$field) {
        $table_arr = $this->use_dbname($table_name);
        $result = $this->link->$table_arr['db_name']->command(array('distinct'=>$table_arr['table_name'],'key'=>$field));
        if(is_array($result)) {
            return $result['values'];
        }
    }

    /**
     * [explain 索引使用情况解释]
     * @param  [type] $table_name       [表名]
     * @param  [type] $query_condition  [条件]
     * @param  array  $result_condition [排序/条数]
     * @param  array  $fields           [显示字段]
     * @return [type]                   [结果]
     */
    public function explain($table_name,$query_condition,$result_condition=array(),$fields=array()) {
        $cursor = $this->build_cursor($table_name,$query_condition,$result_condition,$fields);
        return $cursor->explain();
    }

    /**
     * [delete 删除记录]
     * @param  [type] $table_name [表名]
     * @param  [type] $condition  [条件]
     * @param  array  $options    [选项值/条数控制等]
     * @return [type]             [结果]
     */
    public function delete($table_name, $condition, $options=array('safe' => true)) {
        try {
            $table_arr = $this->use_dbname($table_name);
            return $this->link->selectCollection($table_arr['db_name'],$table_arr['table_name'])->remove($condition, $options);
        } catch (MongoCursorException $e) {
           if($this->options['debug']) {
                $this->halt($e->getMessage());
            } else {
                return false;
            }
        }
    }

    /*
    public function affected_rows() {
        if(!$this->link->connect_error) {
            return $this->link->affected_rows;
        }
    }
    */

    /**
     * [insert_id 当前行插入的_id]
     * @return [type] [结果_id]
     */
    public function insert_id() {
        return (string)$this->query['_id'];
    }

    /**
     * [db_name 连接库名获取]
     * @return [type] [结果_库名]
     */
    public function db_name() {
        return $this->options['dbname'];
    }

    /**
     * [version 版本获取]
     * @return [type] [结果_版本]
     */
    public function version() {
        return Mongo::VERSION;
    }

    /**
     * [halt debug信息]
     * @param  string $message [信息内容]
     * @return [type]          [中断执行,输出结果]
     */
    protected function halt($message = '') {
        $s = '<b>Message:</b>' . $message;
        exit ($s);
    }
}