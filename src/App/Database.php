<?php

//Strict types on
declare(strict_types=1);

//In the namespace App
namespace App;

//Since PDO not in the same namespace
use PDO;

//Main Class
class Database
{
    //?PDO because it's null in the beginning
    private ?PDO $pdo = null;

    //Main constructor, with property promotion
    public function __construct(private string $host_name, private string $db_name, 
                                private string $user, private string $pwd)
    {
    }

    //Method to use for connection
    //Return type is a PDO object - we'll use this for prepared statements later on
    public function getDBConnection(): PDO
    {
        //Check if it exists already (already connected)
        if($this->pdo === null)
        {
            $connection_string = "mysql:host={$this->host_name};";
            $connection_string .= "dbname={$this->db_name};";
            $connection_string .= "charset=utf8;port=3306";

            $this->pdo = new PDO($connection_string, $this->user, $this->pwd, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        //In both cases
        return $this->pdo;
    }
}
