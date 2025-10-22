<?php
require_once __DIR__.'/functions.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$notice = null; $notice_err = null;

// Nếu submit form produce từ trang Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produce_topic'])) {
  $t   = trim($_POST['produce_topic'] ?? '');
  $key = trim($_POST['produce_key'] ?? '');
  $val = trim($_POST['produce_value'] ?? '');
  if ($t === '' || $val === '') {
    $notice_err = 'Topic/Value không được trống.';
  } else {
    $res = send_to_kafka($t, $key, $val);
    if ($res['ok']) $notice = "Đã gửi vào topic [$t].";
    else $notice_err = "Lỗi gửi Kafka: ".$res['msg'];
  }
}

// Lấy danh sách topics
$list = kafka_get('topics');
$topics = $list['ok'] ? json_decode($list['body'], true) : [];

// Topic được chọn để xem chi tiết
$sel = isset($_GET['t']) ? (string)$_GET['t'] : '';
$detail = null;
if ($sel !== '') {
  $detailRes = kafka_get('topics/'.rawurlencode($sel));
  if ($detailRes['ok']) $detail = json_decode($detailRes['body'], true);
  else $notice_err = "Không lấy được chi tiết topic [$sel]: HTTP ".$detailRes['status'];
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kafka Admin — Topics</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand">
      <div class="badge">ADMIN</div>
      <div class="h1">Kafka — Topics</div>
    </div>
    <div class="toolbar">
      <a class="btn" href="kafka_admin.php">↻ Làm mới</a>
      <a class="btn" href="index.php">← Về trang chính</a>
    </div>
  </div>

  <?php if ($notice): ?><div class="notice"><?=h($notice)?></div><?php endif; ?>
  <?php if ($notice_err): ?><div class="notice err"><?=h($notice_err)?></div><?php endif; ?>
  <?php if(!$list['ok']): ?>
    <div class="notice err">Không truy cập được REST Proxy: <?=h($list['status'])?> — <?=h($list['body'])?></div>
  <?php endif; ?>

  <div class="card panel">
    <h2>Danh sách topic</h2>
    <?php if ($topics): ?>
      <div class="kv" style="flex-wrap:wrap;gap:12px;">
        <?php foreach ($topics as $t): ?>
          <?php
            // Lấy nhanh số partition của từng topic
            $info = kafka_get('topics/'.rawurlencode($t));
            $pc = 0;
            if ($info['ok']) {
              $j = json_decode($info['body'], true);
              $pc = isset($j['partitions']) ? count($j['partitions']) : 0;
            }
          ?>
          <a href="?t=<?=rawurlencode($t)?>" title="Xem chi tiết"
             style="text-decoration:none">
            <span><?=h($t)?> (<?= (int)$pc ?>)</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted">Không có topic nào (hoặc REST Proxy chưa phản hồi).</p>
    <?php endif; ?>

    <hr class="sep">
    <p class="muted">REST Proxy: <?=h(KAFKA_REST_PROXY)?></p>
  </div>

  <?php if ($sel !== ''): ?>
    <div class="card panel" style="margin-top:18px">
      <h2>Chi tiết topic: <span class="pill neu"><?=h($sel)?></span></h2>

      <?php if ($detail && isset($detail['partitions'])): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:120px">Partition</th>
                <th>Leader</th>
                <th>Replicas</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($detail['partitions'] as $p): ?>
                <tr>
                  <td><?=h($p['partition'])?></td>
                  <td><?=h($p['leader'])?></td>
                  <td>
                    <?php
                      $rep = isset($p['replicas']) ? $p['replicas'] : [];
                      echo h(implode(',', $rep));
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="muted">Không có dữ liệu partition cho topic này.</p>
      <?php endif; ?>

      <hr class="sep">
      <h2>Produce test vào <span class="pill neu"><?=h($sel)?></span></h2>
      <form method="post" action="kafka_admin.php?t=<?=rawurlencode($sel)?>">
        <input type="hidden" name="produce_topic" value="<?=h($sel)?>">
        <div class="row">
          <div>
            <label>Key (tuỳ chọn)</label>
            <input class="input" name="produce_key" placeholder="ví dụ: 1">
          </div>
          <div>
            <label>Ví dụ nhanh</label>
            <input class="input" readonly value='{"event":"admin_test","note":"hello"}'>
          </div>
        </div>
        <div style="margin-top:10px">
          <label>Value (JSON hoặc chuỗi)</label>
          <textarea class="input" name="produce_value" rows="4" placeholder='{"event":"admin_test","note":"hello"}' required></textarea>
        </div>
        <div style="margin-top:12px">
          <button class="btn brand" type="submit">Gửi vào <?=h($sel)?></button>
          <a class="btn" href="index.php" style="margin-left:8px">Mở Dashboard</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="footer">© Nhom14 — Kafka demo. Dark mode auto.</div>
</div>
</body>
</html>
