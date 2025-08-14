<?php 

class User{
	private $id;
	private $token;
	private $newtoken;
	// private $name;
	// private $email;
	// private $password;
	// private $typeuser;


	public function __GET($k){
		return $this->$k;
	}
	public function __SET($k, $v){
		return $this->$k = $v;
	}
}
?>