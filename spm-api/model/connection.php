<?php
    $dsn = 'mysql:host=localhost;dbname=mydb';
    $user = 'root';
    $pass = '';
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)
?>