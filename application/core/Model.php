<?php

namespace application\core;

use application\helpers\DB;
use application\helpers\Request;
use PDO;

abstract class Model 
{	
	protected $request;
	protected $db;

    protected static $_table = '';
    protected static $_primaryKey = '';

    protected $columns;

	public function __construct() 
	{
		$this->request = new Request;
        $this->db = DB::connect();

        $this->columns = [];
	}

    protected function populate($object)
    {
        foreach ($object as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function set($column,$value)
    {
        $this->columns[$column] = $value;
    }

    public function get($column)
    {
        return $this->columns[$column];
    }

    public function create()
    {

        return $this->db->insert(static::$_table, $this->columns);
    }

    public function update($where = NULL)
    {

        return $this->db->update(static::$_table, $this->columns, $where);
    }

    public function save($where = NULL)
    {
        if($where || $this->get(static::$_primaryKey) !== null) $this->db->update(static::$_table, $this->columns, ($where)?$where:[static::$_primaryKey=>$this->get(static::$_primaryKey)]);
        else $this->db->insert(static::$_table, $this->columns);
        return $this;
    }

    public static function delete($value){
        return DB::connect()->delete(static::$_table, [static::$_primaryKey => $value]);
    }

    public static function getAll($condition=array(),$order=NULL,$startIndex=NULL,$count=NULL){
        $query = "SELECT * FROM " . static::$_table;
        if(!empty($condition)){
            $query .= " WHERE ";
            foreach ($condition as $key => $value) {
                $query .= $key . "=:".$key." AND ";
            }
        }
        $query = rtrim($query,' AND ');
        if($order){
            $query .= " ORDER BY " . $order;
        }
        if($startIndex !== NULL){
            $query .= " LIMIT " . $startIndex;
            if($count){
                $query .= "," . $count;
            }
        }
        foreach ($condition as $key => $value) {
            $condition[':'.$key] = $value;
            unset($condition[$key]);
        }

        return DB::connect()->select($query,$condition);
    }

    public static function findOne($value){

	    $sql = "SELECT * FROM " . static::$_table . " WHERE " . static::$_primaryKey . " = :" . static::$_primaryKey;
	    $params = [ ":" . static::$_primaryKey => $value];
	    $mode = [ ":" . static::$_primaryKey => $value];

        $result = DB::connect()->find($sql, $params);
        
        return $result;
    }

    public static function getCount(){
        return DB::connect()->count(static::$_table);
    }
}