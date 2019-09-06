<?php

namespace application\models;


use application\core\Model;
use PDO;

class User extends Model
{
	protected static $_table = 'users';
	protected static $_primaryKey = 'id';

	const PRIV_ADMINISTRATOR = 1;
	const PRIV_MODERATOR= 2;
	const PRIV_EDITOR= 3;
	const PRIV_MEMBER= 8;
	const PRIV_GUEST= 99;

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

		return $this->db->select(" * FROM " . static::$_table . " WHERE id = :id", [":id" => $id], $fetchMode = PDO::FETCH_OBJ, $class = '', 1);
	}

	public function whereUsername($username)
	{

		return $this->db->select(" * FROM " . static::$_table . " WHERE username = :username", [":username" => $username]);
	}

	public function wherePassword($password)
	{

		return $this->db->select(" * FROM " . static::$_table . " WHERE password = :password", [":password" => $password]);
	}

	public function whereEmail($email)
	{

		return $this->db->select(" * FROM " . static::$_table . " WHERE email = :email", [":email" => $email]);
	}

	public function whereFullname($fullname)
	{

		return $this->db->select(" * FROM " . static::$_table . " WHERE fullname = :fullname", [":fullname" => $fullname]);
	}

	public function wherePrivilege($privilege)
	{

		return $this->db->select(" * FROM " . static::$_table . " WHERE privilege = :privilege", [":privilege" => $privilege]);
	}

}