<?php
require_once __DIR__.'/functions.php';
$pdo = db();
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$tab = $_GET['t'] ?? 'messages';
$valid = ['messages','consumed','orders'];
if ($tab === 'consumed') { $sql = "SELECT id,topic,mkey,mvalue,partition_id,offset_val,consumed_at FROM consumed_messages ORDER BY id DESC LIMIT 100"; }
elseif ($tab === 'orders') { $sql = "SELECT id,code,customer_name,total,status,created_at FROM orders ORDER BY id DESC LIMIT 100"; }
else { $tab='messages'; $sql = "SELECT id,topic,mkey,mvalue,status,created_at FROM messages ORDER BY id DESC LIMIT 100"; }

$rows = $pdo->query($sql)->fetchAll();
?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data viewer</title>
<link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="badge">DATA</div><div class="h1">Xem dữ liệu MySQL</div></div>
    <div class="toolbar"><a class="btn" href="index.php">← Về trang chính</a></div>
  </div>

  <div class="card">
    <div class="toolbar" style="margin-bottom:10px">
      <a class="btn<?= $tab==='messages'?' brand':''?>" href="?t=messages">messages</a>
      <a class="btn<?= $tab==='consumed'?' brand':''?>" href="?t=consumed">consumed_messages</a>
      <a class="btn<?= $tab==='orders'?' brand':''?>" href="?t=orders">orders</a>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
        <tr>
          <?php if ($rows) foreach(array_keys($rows[0]) as $c) echo '<th>'.h($c).'</th>'; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <?php foreach($r as $v): ?>
              <td style="white-space:pre-line"><?=h((string)$v)?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="muted">Hiển thị tối đa 100 dòng gần nhất (read-only).</p>
  </div>
</div>
</body></html>
