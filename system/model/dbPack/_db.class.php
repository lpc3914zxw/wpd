<?php

abstract class _db {
	
	abstract function query($sql, $cachetime = false);
	
	abstract function get_var($sql);
	
	abstract function get_col($sql);
	
	abstract function get_row($sql, $attrib = 'BOTH');
	
	abstract function get_results($sql, $attrib = 'BOTH');
	
	abstract function affected_rows();
	
	abstract function error();
	
	abstract function errno();
	
	abstract function insert_id();
	
	abstract function start_transaction();
	
	abstract function commit();
	
	abstract function rollback();
	
	abstract function db_name();
	
	abstract function version();
	
	abstract function halt($message = '', $sql = '');
	
	abstract function free_result();
	
	abstract function close_db();
}
