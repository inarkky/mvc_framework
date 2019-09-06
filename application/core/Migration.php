<?php

namespace application\core;


use application\helpers\DB;

class Migration
{

    public static function parse($file)
    {
        $query = 'CREATE TABLE ';
        $subquery = ' ';
        $data = [];

        $fn = fopen($file,'r');
        while(! feof($fn))  {

            $result = fgets($fn);
            $chunk = explode( ':', $result);

            if(count($chunk) === 1 && $chunk[0] !== ''){
                $table = substr($chunk[0], 5);
                $table = strtolower(preg_replace('/\s+/', '', $table));

            }elseif(count($chunk) > 1){
                $operation = substr($chunk[0], 0, 1);
                $column = substr($chunk[0], 1);
                $type   = $chunk[1];
                $params = '';

                $data[(string)$column] = $type;

                $subquery .= "$column $type $params,";
            }

        }

        $query .= $table . '(' . rtrim($subquery, ',') . ');';
        var_dump($query);
        fclose($fn);

        $db = DB::connect();
        $db->raw($query);
    }

    private static function _resolveParams($params)
    {

    }

}