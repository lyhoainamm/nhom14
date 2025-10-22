<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/db.php';

$q = trim($_GET['q'] ?? '');
$pdo = get_db();
try{
    if($q === ''){
        $stmt = $pdo->query('SELECT id, title, description, created_at FROM tasks ORDER BY id DESC LIMIT 50');
        $rows = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT id, title, description, created_at FROM tasks WHERE title LIKE ? ORDER BY id DESC LIMIT 100');
        $stmt->execute(['%'.$q.'%']);
        $rows = $stmt->fetchAll();
    }
    echo json_encode(['success'=>true,'rows'=>$rows]);
}catch(Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
