<?php

abstract class _cache {
	
	protected $prefix;
	
	abstract public function set($key,$val,$time = null);
	
	abstract public function get($key);
	
	abstract public function delete($key);
	
	abstract public function flush();
	  
    protected function _write($file, $content = '', $flags = LOCK_EX) {
		if (!file_exists($file)) {
			$directory = dirname($file);
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
			}
			touch($file);
			chmod($file, 0777);
		}
		file_put_contents($file, $content, $flags);
	}
	
	protected function _unlink($file) {
		$file = realpath($file);
		if (is_dir($file)) {
			foreach ((glob($file . '/*') ? glob($file . '/*') : array()) as $tmp) {
				unlink($tmp);
			}
			rmdir($file);
			return;
		}
		elseif (file_exists($file)) {
			unlink($file);
		}
	}
}
