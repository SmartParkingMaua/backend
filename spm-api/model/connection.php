<?php
    $dsn = 'mysql:host=127.0.0.1:3307;dbname=mydb';
    $user = 'root';
    $pass = '';
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)
?>