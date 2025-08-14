<?php 

class Stage{
	private $id;
	private $owner;
	private $name;
	private $source;
	private $sink;
	private $transformation;
	private $created;

	public function __GET($k){
		return $this->$k;
	}
	public function __SET($k, $v){
		return $this->$k = $v;
	}
}
?>
