<?php

class Organisation {
private $id;
private $name;
private $approved;


public function getId() { return $this->id; }
public function getName() { return $this->name; }
public function getApproved() { return $this->approved; }


public function setId($id) { $this->id = $id; }
public function setName($name) { $this->name = $name; }
public function setApproved($approved) { $this->approved = $approved; }
}