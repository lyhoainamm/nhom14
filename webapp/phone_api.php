<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/db.php';
$pdo = get_db();

$method = $_SERVER['REQUEST_METHOD'];
try{
  if($method === 'GET'){
    if(isset($_GET['id'])){
      $stmt = $pdo->prepare('SELECT * FROM phones WHERE id = ?'); $stmt->execute([$_GET['id']]); $row = $stmt->fetch();
      if(!$row) echo json_encode(['success'=>false,'error'=>'Not found']); else echo json_encode(['success'=>true,'row'=>$row]);
      exit;
    }
    // support optional search q parameter
    if(isset($_GET['q'])){
      $q = '%'.trim($_GET['q']).'%';
      $stmt = $pdo->prepare('SELECT * FROM phones WHERE model LIKE ? OR brand LIKE ? ORDER BY id');
      $stmt->execute([$q,$q]);
      $rows = $stmt->fetchAll();
      echo json_encode(['success'=>true,'rows'=>$rows]);
      exit;
    }
    $stmt = $pdo->query('SELECT * FROM phones ORDER BY id');
    $rows = $stmt->fetchAll();
    echo json_encode(['success'=>true,'rows'=>$rows]);
    exit;
  }

  $input = json_decode(file_get_contents('php://input'), true) ?? [];

  if($method === 'POST'){
    $stmt = $pdo->prepare('INSERT INTO phones (model, brand, price, stock) VALUES (?, ?, ?, ?)');
    $stmt->execute([$input['model'] ?? '', $input['brand'] ?? null, $input['price'] ?? 0, $input['stock'] ?? 0]);
    echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); exit;
  }

  if($method === 'PUT'){
    $id = $_GET['id'] ?? null; if(!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }
    $stmt = $pdo->prepare('UPDATE phones SET model=?, brand=?, price=?, stock=? WHERE id=?');
    $stmt->execute([$input['model'] ?? '', $input['brand'] ?? null, $input['price'] ?? 0, $input['stock'] ?? 0, $id]);
    echo json_encode(['success'=>true]); exit;
  }

  if($method === 'DELETE'){
    $id = $_GET['id'] ?? null; if(!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }
    $stmt = $pdo->prepare('DELETE FROM phones WHERE id=?'); $stmt->execute([$id]); echo json_encode(['success'=>true]); exit;
  }

  echo json_encode(['success'=>false,'error'=>'Unsupported method']);
}catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
