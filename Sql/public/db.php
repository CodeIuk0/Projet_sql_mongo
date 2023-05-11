<?php

function ConnectDB()
{
    $host = 'localhost';
    $port = 3306;
    $database = 'mydb';
    $user = 'root';
    $password = '';
    $dsn = "mysql:host={$host}:{$port};dbname={$database}";

    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

?>