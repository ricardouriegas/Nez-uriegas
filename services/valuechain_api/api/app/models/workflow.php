<?php 

class Workflow{
	private $id;
	private $owner;
	private $name;
	private $status;
	private $stages;
	private $rawgraph;
	private $created;

	public function __GET($k){
		return $this->$k;
	}
	public function __SET($k, $v){
		return $this->$k = $v;
	}
}
?>