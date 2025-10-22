<?php
// db.php - simple PDO based MySQL connector for XAMPP (localhost)
// Edit DB constants if your XAMPP uses different credentials.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'webapp_demo';
const DB_USER = 'root';
const DB_PASS = ''; // default XAMPP MySQL has empty password for root

function get_db(){
    static $pdo = null;
    if($pdo) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    try{
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }catch(PDOException $e){
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'DB connection failed: '.$e->getMessage()]);
        exit;
    }
}
