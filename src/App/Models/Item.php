<?php

//Strict
declare(strict_types=1);

namespace App\Models;
use App\Common\Model;
use App\Database;
use PDO;

class Item extends Model
{

    //Constructor
    public function __construct(public Database $database, public int $id, public string $name, 
        public ?string $description, public bool $is_container, public ?int $item_parent_fk, public ?array $children_items)
    {
        parent::__construct($database);
    }

    //Updates the said item
    public function update(): bool
    {
        $sql_string = "UPDATE item SET ";

        $associative_array = self::objectToAssociativeArray($this);

        //Can't update the id
        unset($associative_array['id']);

        $array_errors = self::validate($associative_array);
        //If not empty, return false
        if(!empty($array_errors))
        {
            return false;
        }

        $countElements = 0;
        foreach($associative_array as $key => $value)
        {
            $countElements++;
            $sql_string .= "$key = :$key";
            if($countElements != count($associative_array))
            {
                $sql_string .=', ';
            }   
        }

        $sql_string .= ' WHERE id = :theId';

        //Statement time
        $connection = $this->database->getDBConnection();
        $statement = $connection->prepare($sql_string);

        foreach($associative_array as $key => $value)
        {
            //Determine type for pdo
            $type = match(gettype($value)) {
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };

            $statement->bindValue(":$key", "$value", $type);
        }

        $statement->bindValue("theId", $this->id, PDO::PARAM_INT);

        return $statement->execute();       

    }

    //Deleting a record
    public function delete(): bool
    {
        $sql_string = "DELETE FROM item WHERE id = :theId";
        //Statement time
        $connection = $this->database->getDBConnection();
        $statement = $connection->prepare($sql_string);
        $statement->bindValue("theId", $this->id, PDO::PARAM_INT);

        return $statement->execute(); 
    }

    //Finding all of the children elements
    public function getListChildren(): int
    {
        $sql_string = "SELECT * FROM item WHERE item_parent_fk = 2";
        $connection = $this->database->getDBConnection();
        $statement = $connection->prepare($sql_string);
        $statement->bindValue("theForeignKey", $this->id, PDO::PARAM_INT);

        $results = $connection->query($sql_string)->fetchAll(PDO::FETCH_OBJ);
        
        $this->children_items = self::transformObject($this->database, $results);

        return sizeof($this->children_items);
    }

    //Validate an item before insertion
    public static function validate(array $data): array
    {
        $array_errors = [];

        //public string $name, public ?string $description, public bool $is_container
        if(empty($data["name"]))
        {
            $array_errors['name'] = "The name is required";
        }

        if(empty($data["is_container"]) && $data["is_container"] !== false)
        {
            $array_errors['is_container'] = "Missing information about container";
        }


        return $array_errors;
    }

    //Static deletion so we unset the object at the same time.
    public static function deleteItem(Item $theItem): bool
    {
        $operation_outcome = $theItem->delete();
        unset($theItem);
        return $operation_outcome;
    }

    //Item creation
    public static function insertItem(Database $database, array $data): ?Item
    {
        $array_errors = self::validate($data);

        //If not empty, return false
        if(!empty($array_errors))
        {
            return false;
        }

        //Continue
        $sql_string = "INSERT INTO item ";

        //The column names
        $first_part_fields = "(";

        //Similarly, but for the values
        $second_part_fields = "(";


        $countElements = 0;
        foreach($data as $key => $value)
        {
            $countElements++;
            
            //Both cases
            $first_part_fields .= $key;
            $second_part_fields .= ":$key";

            
            //If not the final element
            if($countElements != count($data))
            {
                $first_part_fields .= ", ";
                $second_part_fields .= ", ";
            }
            else
            {
                $first_part_fields .= ")";
                $second_part_fields .= ")";
            }   
        }

        $sql_string .= $first_part_fields." VALUES ".$second_part_fields;

        //Statement
        $connection = $database->getDBConnection();
        $statement = $connection->prepare($sql_string);

        foreach($data as $key => $value)
        {
            //Determine type for pdo
            $type = match(gettype($value)) {
                "boolean" => PDO::PARAM_BOOL,
                "integer" => PDO::PARAM_INT,
                "NULL" => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };

            $statement->bindValue(":$key", "$value", $type);
        }

        $returned_item = null;

        if($statement->execute())
        {
            $returned_item = self::searchForItemById($database, (int) $connection->lastInsertId());
        }


        return $returned_item;
    }

    //Mass delete items
    public static function massDeleteItems(Database $database, array $items_array): bool
    {
        $array_ids = [];
        foreach($items_array as $individual_item)
        {
            array_push($array_ids, $individual_item->id);
        }
        
        
        $in_query = str_repeat('?,', count($array_ids) - 1) . '?';
        $sql_string = "DELETE FROM item WHERE id IN ($in_query)";
        //Statement time
        $connection = $database->getDBConnection();
        $statement = $connection->prepare($sql_string);

        return $statement->execute($array_ids); 
    }


    //---------------------- Static search methods
    //Gets all items
    public static function getAllItems(Database $database): array
    {
        $sql_string = "SELECT * FROM item";

        $conn = $database->getDBConnection();
        $results = $conn->query($sql_string)->fetchAll(PDO::FETCH_OBJ);
        
        return self::transformObject($database, $results);
    }

    //Searches for items by name
    public static function searchForItemByName(Database $database, string $query_string)
    {
        $sql_string = "SELECT * FROM item WHERE name LIKE :query_string";

        $connection = $database->getDBConnection();
        $statement = $connection->prepare($sql_string);
        $statement->bindValue(":query_string", "%$query_string%", PDO::PARAM_STR);
        

        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_OBJ);

        return self::transformObject($database, $results);
    }

    //Searches for an item by id
    public static function searchForItemById(Database $database, int $id): Item
    {
        $sql_string = "SELECT * FROM item WHERE id = :id";

        $connection = $database->getDBConnection();
        $statement = $connection->prepare($sql_string);
        $statement->bindValue(":id", $id, PDO::PARAM_INT);
        

        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_OBJ);

        return self::transformObject($database, $results)[0];
    }

    //---------------------- Static utility methods
    //Common function used by other static and non static queries
    private static function transformObject($database, array $results) : array
    {
        $array_results = [];

        foreach($results as $item)
        {
            array_push($array_results, new Item($database, $item->id, $item->name, $item->description, (bool) $item->is_container, $item->item_parent_fk, null));
        }

        return $array_results;
    }

    //Typecast it but remove unwanted refs
    private static function objectToAssociativeArray(Item $theItem) : array
    {
        $theArray = (array) $theItem;

        //Database - needs to be private so we unset.
        unset($theArray['database']);

        //Can't update children either, so remove them
        unset($theArray['children_items']);
        return $theArray;
    }
}