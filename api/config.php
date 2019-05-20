<?php

$config = [
    "host"      => "127.0.0.1",
    "dbname"    => "v1app",
    "user"      => "root",
    "password"  => ""
];


$conn = null;

try {

    $conn = new PDO("mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'], $config['user'], $config['password']);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch ( PDOException $e ) {

    response(["error" => $e->getMessage()]);

}