<?php 

class PublishWorkflow{
	private $idworkflow;
	private $iduser;
	private $subscribed;
	private $created;

	public function __GET($k){
		return $this->$k;
	}
	public function __SET($k, $v){
		return $this->$k = $v;
	}
}
?>
