<?php
declare(strict_types=1);

//Autoloading
spl_autoload_register(function (string $class_name) {

    require __DIR__ . "/src/" . str_replace("\\", "/", $class_name) . ".php";

});

//Use statements for namespaces
use App\Database;
use App\Models\Item;
use App\Utility;

//Load up the info
Utility::loadEnv(__DIR__."/.env");

//Error and exception handling
set_error_handler("App\Utility::handleError");
set_exception_handler("App\Utility::handleException");


$database = new Database($_ENV['DB_HOST'],$_ENV['DB_NAME'],$_ENV['DB_USER'],$_ENV['DB_PASSWORD']);
$results = Item::searchForItemByName($database, 'Satchel');
print_r($results);

/*
$database = new Database('localhost','inventory_management','root','admin');
$results = Item::searchForItemByName($database, 'Satchel');
$parent_object = $results[0];
$parent_object->getListChildren();


$insertTest1 = ["name"=> "TEST2 PARENT", "description" => "inserted descrip", "is_container" => false];
$item1 = Item::insertItem($database, $insertTest1);

$insertTest2 = ["name"=> "TEST2 CHILD", "description" => "inserted descrip", "is_container" => false];
$item2 = Item::insertItem($database, $insertTest2);

$item2->item_parent_fk = $item1->id;
$item2->update();
*/

/*

$results = Item::getAllItems($database);



$database = new Database('localhost','inventory_management','root','admin');
$results = Item::searchForItemById($database, 3);


$backpack_result_one = $results[0];
$backpack_result_one->name = 'From test 5';
$backpack_result_one->update();

echo "<br/><br/><br/>";
$results = Item::searchForItemById($database, 1);
print_r($results);

$insertTest1 = ["name"=> "inserted name1", "description" => "inserted descrip", "is_container" => false];
Item::insertItem($database, $insertTest1);


$parent_object->name = 'modded yo';
echo '<br/><br/>updated?<br/>'.$parent_object->update();
var_dump($parent_object->children_items);
*/
//Item::massDeleteItems($database, $parent_object->children_items);

$mainPageTitle = 'This is the main page';
$mainParagraph = 'This is the main paragraph';

?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<title><?=$mainPageTitle?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://classless.de/classless.css">
<style>
</style>
<script src=""></script>
<body>


<div class="card">
    <h4><?=$mainPageTitle?></h4>
    <hr/>
    <p><?=$mainParagraph?></p>
</div>

</body>
</html>