<?php 

class BlackBox{
	private $id;
	private $owner;
	private $name;
	private $command;
	private $image;
	private $port;
	private $created;

	public function __GET($k){
		return $this->$k;
	}
	public function __SET($k, $v){
		return $this->$k = $v;
	}
}
?>