<?php
include '_cache.class.php';
class _json_file extends _cache {
	
	protected $options = array ();
	
	protected $path = null;
	
	protected $prefix = null;
	
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
				'path' => './cache/',
				'time' => '3600',
				'prefix' => 'test'
			);
		}
		$this->connect();
	}
	
	protected function connect() {
		$this->path = rtrim($this->options['path'],'/') . '/';
		$this->prefix = $this->path . (empty($this->options['prefix']) ? '' : rtrim($this->options['prefix'],'/') . '/');
	}
	
	public function set($key,$val,$ttl = '') {
		$file = $this->prefix.'J_'.$key.'.php';
		if($ttl === '') {
			$expire = $this->options['time'];
		} else {
			$expire = $ttl;
		}
		$data = array('time' => time(), 'expire' => (int)$expire, 'data' => $val);
		$this->_write($file, '<?php exit;?>' . serialize($data));
        return true;
	}
	
	public function get($key) {
		$data = $this->prototype_get($key);
		if ($this->expire($data['time'],$data['expire'])) {
			return null;
		}
		return $data['data'];
	}
	
	public function long_get($key) {
		$data = $this->prototype_get($key);
		return $data['data'];
	}
	
	public function delete($key) {
		if(is_array($key) && !empty($key)) {
			foreach ($key as $k) {
				if (file_exists($file = $this->prefix.'J_'.$k.'.php')) {
					$this->_unlink($file);
				}
			}
		} else {
			if (file_exists($file = $this->prefix.'J_'.$key.'.php')) {
				$this->_unlink($file);
			}
		}
	}
	
	public function flush() {
		$this->_unlink($this->path);
	}
	
	protected function prototype_get($key) {
		$file = $this->prefix.'J_'.$key.'.php';
		if (!file_exists($file)) {
			return null;
		}
		return unserialize(file_get_contents($file, false, null, 13));
	}
	
	protected function expire($time,$expire) {
		return ($expire > 0 && (time() - $time) > $expire) ? true : false;
	}
}
