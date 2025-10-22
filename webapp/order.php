<?php
require_once __DIR__.'/functions.php';
$id=(int)($_GET['id']??0); $pdo=db();
$o=$pdo->prepare('SELECT id,code,customer_name,total,status,created_at FROM orders WHERE id=?');
$o->execute([$id]); $order=$o->fetch();
if(!$order){ echo "<p>Không tìm thấy đơn #{$id}</p><p><a href='index.php'>Về trang chính</a></p>"; exit; }

$pdo->prepare("INSERT INTO messages(topic,mkey,mvalue,status,producer_id,created_at,updated_at)
               VALUES (?,?,?,?,UUID(),NOW(),NOW())")
    ->execute([TOPIC_ORDER_VIEWED,(string)$order['id'],json_encode($order,JSON_UNESCAPED_UNICODE),'queued']);
$msgId=(int)$pdo->lastInsertId();

$res=send_to_kafka(TOPIC_ORDER_VIEWED,(string)$order['id'],json_encode([
  'event'=>'order_viewed','orderId'=>$order['id'],'code'=>$order['code'],'viewedAt'=>date('c'),
]));
if($res['ok']){ $pdo->prepare("UPDATE messages SET status='sent',updated_at=NOW() WHERE id=?")->execute([$msgId]);
  $notice='ĐÃ PHÁT SỰ KIỆN Kafka: order-viewed';
}else{ $pdo->prepare("UPDATE messages SET status='error',error=?,updated_at=NOW() WHERE id=?")->execute([$res['msg'],$msgId]);
  $notice='LỖI gửi Kafka (đã lưu DB): '.htmlspecialchars($res['msg']);
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Đơn #<?=$order['id']?></title></head>
<body style="font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif">
<p><a href="index.php">← Về trang chính</a></p>
<h1>Đơn #<?=$order['id']?> — <?=htmlspecialchars($order['code'])?></h1>
<p><b>Khách:</b> <?=htmlspecialchars($order['customer_name'])?></p>
<p><b>Tổng tiền:</b> <?=number_format($order['total'])?> đ</p>
<p><b>Trạng thái:</b> <?=htmlspecialchars($order['status'])?></p>
<hr><p><b>Kafka:</b> <?=$notice?></p>
</body></html>
