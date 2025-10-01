<?php
class Content {
private $id;
private $organisationId;
private $title;
private $body;


public function getId() { return $this->id; }
public function getOrganisationId() { return $this->organisationId; }
public function getTitle() { return $this->title; }
public function getBody() { return $this->body; }


public function setId($id) { $this->id = $id; }
public function setOrganisationId($orgId) { $this->organisationId = $orgId; }
public function setTitle($title) { $this->title = $title; }
public function setBody($body) { $this->body = $body; }

}