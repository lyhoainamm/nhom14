<?php
require_once __DIR__.'/functions.php';
$topic=trim($_POST['topic']??''); $mkey=trim($_POST['mkey']??''); $mvalue=trim($_POST['mvalue']??'');
if($topic===''||$mvalue===''){ header('Location: index.php?err='.rawurlencode('Topic/Value không được trống')); exit; }
$pdo=db(); $pdo->beginTransaction();
try{
  $pdo->prepare("INSERT INTO messages(topic,mkey,mvalue,status,producer_id,created_at,updated_at)
                 VALUES (?,?,?,?,UUID(),NOW(),NOW())")
     ->execute([$topic,$mkey!==''?$mkey:null,$mvalue,'queued']);
  $id=(int)$pdo->lastInsertId();
  $res=send_to_kafka($topic,$mkey,$mvalue);
  if($res['ok']){
    $pdo->prepare("UPDATE messages SET status='sent',updated_at=NOW() WHERE id=?")->execute([$id]);
    $pdo->commit(); header('Location: index.php?ok='.rawurlencode('Đã gửi lên Kafka (và lưu DB)'));
  }else{
    $pdo->prepare("UPDATE messages SET status='error',error=?,updated_at=NOW() WHERE id=?")
       ->execute([$res['msg'],$id]);
    $pdo->commit(); header('Location: index.php?err='.rawurlencode('Gửi Kafka lỗi: '.$res['msg'].' (đã lưu DB)'));
  }
}catch(Throwable $e){ $pdo->rollBack(); header('Location: index.php?err='.rawurlencode('Lỗi: '.$e->getMessage())); }
