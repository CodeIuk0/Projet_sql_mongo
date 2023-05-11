<?php

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;


class BackEndManager
{

    public static Client $CurrentHost;
    public static string $CDBName;

    



    public static function ObjectId(string $id) 
    {
        $_id = null;

        try {
           $_id = new MongoDB\BSON\ObjectID($id);
        } catch (\Throwable $th) {
            $_id=null;
        }

        return $_id;
    }

    public static function Join(string $table,string $table_to_join,string $table_field,string $table_to_join_field, $MatchValue) 
    {
        if($MatchValue!= null)
        {
        return BackEndManager::GetTable($table)->aggregate([
            [
              '$lookup' => [
                'from' => $table_to_join,
                'localField' => $table_field,
                'foreignField' => $table_to_join_field,
                'as' => $table_to_join,
              ],
            ],
            [
                '$match' => [
                    $table_to_join.".".$table_to_join_field =>  $MatchValue,
                ],
              ],
    
          ]);

        }else if($MatchValue == null)
        {
            return BackEndManager::GetTable($table)->aggregate([
                [
                  '$lookup' => [
                    'from' => $table_to_join,
                    'localField' => $table_field,
                    'foreignField' => $table_to_join_field,
                    'as' => $table_to_join,
                  ],
                ],
              ]);
        }
    }

    public static function  DropCollection(string $CollectioName) : static
    {
        BackEndManager::$CurrentHost->selectCollection(BackEndManager::$CDBName ,$CollectioName)->drop();

        return new static();
    }

    public static function  SwitchDataBase(string $DBName = "") : static
    {
        BackEndManager::$CDBName = $DBName;

        return new static();
    }

    public static function GetTable(string $CollectioName) : Collection
    {
        if(BackEndManager::$CurrentHost==null)
            return null;

        return BackEndManager::$CurrentHost->selectCollection(BackEndManager::$CDBName ,$CollectioName);  
    }

    public static function ConnectDataBase(string $Host="",string $DBName="") : static
    {
        if($Host != "")
        {
           BackEndManager::$CurrentHost = new Client("mongodb://localhost:27017/");
           BackEndManager::$CDBName = $DBName;
        }

        return new static();
    }
};



?>