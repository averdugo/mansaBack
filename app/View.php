<?php 

namespace App;

class View
{
	protected $tmpl;
	protected $data = [];
	
	
	public function __construct($tmpl)
	{
		$this->tmpl = $tmpl;
	}
	
	public function __call($func, $args)
	{
		$this->data[$func] = $args[0];
		return $this;
	}
	
	public function __set($key, $val)
	{
		$this->data[$key] = $val;
	}
	
	public function __get($key)
	{
		return $this->data[$key];
	}
	
	public function __isset($key)
	{
		return isset($this->data[$key]);
	}
	
	public function __toString()
	{
		ob_start();
		extract($this->data);
		include __DIR__ . '/View/' . $this->tmpl . '.php';
		return ob_get_clean();
	}
}
