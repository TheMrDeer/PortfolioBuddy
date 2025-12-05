<?php

require_once 'dbaccess.php';
$db_obj = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

$sqlSelect = "SELECT * FROM user";
$result = $db_obj->query($sqlSelect);

while ($row = $result->fetch_assoc()){
 echo "id: " . $row["id"] . "<br />";
 echo "username: " . $row["username"] . "<br />";
 echo "password: " . $row["password"] . "<br />";
 echo "usermail: " . $row["usermail"] . "<br />";
}
