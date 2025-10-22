<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/db.php';
$pdo = get_db();

$method = $_SERVER['REQUEST_METHOD'];
try{
  if($method === 'GET'){
    $items = $_SESSION['cart'] ?? [];
    // enrich items with phone data
    $result = [];
    foreach($items as $phone_id => $qty){
      $stmt = $pdo->prepare('SELECT id, model, brand, price FROM phones WHERE id=?'); $stmt->execute([$phone_id]); $p = $stmt->fetch();
      if($p) $result[] = ['phone_id'=>$p['id'],'model'=>$p['model'],'brand'=>$p['brand'],'price'=>$p['price'],'quantity'=>$qty];
    }
    echo json_encode(['success'=>true,'items'=>$result]); exit;
  }

  $input = json_decode(file_get_contents('php://input'), true) ?? [];
  if($method === 'POST'){
    // add item or checkout
    if(isset($input['checkout']) && $input['checkout']){
      $items = $_SESSION['cart'] ?? [];
      if(empty($items)) { echo json_encode(['success'=>false,'error'=>'Cart empty']); exit; }
      $order_ids = [];
      foreach($items as $phone_id => $qty){
        // create order via order_api.php internal call
        $ch = curl_init((isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/order_api.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['phone_id'=>$phone_id,'quantity'=>$qty]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $resp = curl_exec($ch);
        curl_close($ch);
        $j = json_decode($resp, true);
        if($j && $j['success']) $order_ids[] = $j['id'];
      }
      // clear cart
      $_SESSION['cart'] = [];
      echo json_encode(['success'=>true,'order_ids'=>$order_ids]); exit;
    }
    // add to cart
    $phone_id = $input['phone_id'] ?? null; $qty = max(1,intval($input['quantity'] ?? 1)); if(!$phone_id) { echo json_encode(['success'=>false,'error'=>'Missing phone_id']); exit; }
    if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if(isset($_SESSION['cart'][$phone_id])) $_SESSION['cart'][$phone_id] += $qty; else $_SESSION['cart'][$phone_id] = $qty;
    echo json_encode(['success'=>true]); exit;
  }

  if($method === 'DELETE'){
    $id = $_GET['id'] ?? null; if(!$id) { echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }
    if(isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]); echo json_encode(['success'=>true]); exit;
  }

  echo json_encode(['success'=>false,'error'=>'Unsupported method']);
}catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
