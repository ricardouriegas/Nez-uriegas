<?php
/*
* Node
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/
class Node {
	public $id;
	public $url;
	public $used;
	public $total;
	public $uf;

	public function __construct($id, $url, $used, $total) {
		$this->id    = $id;
		$this->url   = $url;
		$this->used  = $used;
		$this->total = $total;
		$this->uf = 1;
	}

	public function setUf($fileSize) {
		$this->uf = 1.0 - (double) (($this->total - ($this->used + $fileSize)) / $this->total);
	}

	public static function compare($node1, $node2) {
		if ($node1->uf < $node2->uf) return -1;
		else if($node1->uf == $node2->uf) return 0;
		else return 1;
	}

	public function __toString() {
		return $this->id . " is url " . $this->url . " is used ". $this->used . " is total " . $this->total. " is uf " .$this->uf ;
	}
}
?>