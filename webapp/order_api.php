<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/db.php';
$pdo = get_db();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$phone_id = $input['phone_id'] ?? null;
$quantity = intval($input['quantity'] ?? 1);
if(!$phone_id){ echo json_encode(['success'=>false,'error'=>'Missing phone_id']); exit; }

try{
  // create order
  $stmt = $pdo->prepare('INSERT INTO orders (phone_id, quantity, status) VALUES (?, ?, ?)');
  $stmt->execute([$phone_id, $quantity, 'created']);
  $orderId = $pdo->lastInsertId();

  // publish to kafka if docker available; container name expected 'broker' by repo docker compose
  $msg = json_encode(['order_id'=>$orderId,'phone_id'=>$phone_id,'quantity'=>$quantity,'created_at'=>date('c')]);
  // Prefer using repo local Java producer via run_producer.bat (builds docker image & runs it)
  $repoRoot = dirname(__DIR__, 1);
  $runner = "$repoRoot\\run_producer.bat";
  $published = false;
  if(file_exists($runner)){
    // escape message for cmd
    $escaped = escapeshellarg($msg);
    $cmd = "cmd /c \"$runner $escaped\"";
    exec($cmd, $output, $rc);
    if($rc === 0){ $published = true; }
  }

  if(!$published){
    // fallback: try docker exec -> kafka-console-producer
    $cmd = "docker exec -i broker /opt/kafka/bin/kafka-console-producer.sh --topic orders --bootstrap-server localhost:9092";
    $descriptorspec = [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
    $proc = @proc_open($cmd, $descriptorspec, $pipes);
    if(is_resource($proc)){
      fwrite($pipes[0], $msg.PHP_EOL);
      fclose($pipes[0]);
      $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
      $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
      $status = proc_close($proc);
      // ignore errors
    }
  }

  echo json_encode(['success'=>true,'id'=>$orderId]);
}catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
