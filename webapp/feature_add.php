<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if(!$input) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
if($title === ''){
    echo json_encode(['success'=>false,'error'=>'Tiêu đề không được rỗng']);
    exit;
}

$pdo = get_db();
try{
    $stmt = $pdo->prepare('INSERT INTO tasks (title, description, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$title, $description]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success'=>true,'id'=>$id]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
