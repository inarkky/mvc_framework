<?php

namespace application\models;


use application\core\Model;

class User extends Model
{
	protected static $_table = 'users';
	protected static $primaryKey = 'id';

	public $id;
	public $username;
	public $password;
	public $email;
	public $fullname;
	public $privilege;

	public function getId()
	{

		return $this->get("id");
	}

	public function getUsername()
	{

		return $this->get("username");
	}

	public function getPassword()
	{

		return $this->get("password");
	}

	public function getEmail()
	{

		return $this->get("email");
	}

	public function getFullname()
	{

		return $this->get("fullname");
	}

	public function getPrivilege()
	{

		return $this->get("privilege");
	}

	public function setId($value)
	{

		$this->set("id", $value);
	}

	public function setUsername($value)
	{

		$this->set("username", $value);
	}

	public function setPassword($value)
	{

		$this->set("password", $value);
	}

	public function setEmail($value)
	{

		$this->set("email", $value);
	}

	public function setFullname($value)
	{

		$this->set("fullname", $value);
	}

	public function setPrivilege($value)
	{

		$this->set("privilege", $value);
	}

	public function whereId($id)
	{

		$this->populate($this->findOne($id);
		return $this;
	}

	public function whereUsername($username)
	{

		$this->getAll(["username" => $username]);
		return $this;
	}

	public function whereEmail($email)
	{

		$this->getAll(["email" => $email]);
		return $this;
	}

	public function whereFullname($fullname)
	{

		$this->getAll(["fullname" => $fullname]);
		return $this;
	}

	public function wherePrivilege($privilege)
	{

		$this->getAll(["privilege" => $privilege]);
		return $this;
	}

}
