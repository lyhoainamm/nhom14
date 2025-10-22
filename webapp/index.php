<?php
require_once __DIR__.'/functions.php';
$pdo = db();
$sent = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 80")->fetchAll();
$consumed = $pdo->query("SELECT * FROM consumed_messages ORDER BY id DESC LIMIT 80")->fetchAll();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$ok = $_GET['ok'] ?? null; $err = $_GET['err'] ?? null;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nhom14 • Kafka Showcase</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <div class="badge">NHOM14</div>
        <div>
          <div class="h1">PHP + Kafka (REST Proxy) + Java Consumer</div>
          <div class="kv">
            <span>MySQL</span><span>Docker Compose</span><span>Event-driven</span>
          </div>
        </div>
      </div>
      <div class="toolbar">
        <!-- <a class="btn brand" href="order.php?id=1">Xem đơn #1 → publish event</a>
        <a class="btn" href="order.php?id=2">Xem đơn #2</a> -->
        <a class="btn" href="kafka_admin.php">Kafka Admin</a>
        <a class="btn" href="data.php">Xem dữ liệu</a>
      </div>
    </div>

    <?php if($ok): ?><div class="notice"><?=h($ok)?></div><?php endif; ?>
    <?php if($err): ?><div class="notice err"><?=h($err)?></div><?php endif; ?>

    <div class="grid">
      <!-- LEFT: form + messages sent -->
      <div>
        <div class="card">
          <h2>Gửi message thủ công</h2>
          <form method="post" action="send.php" autocomplete="off">
            <div class="row">
              <div>
                <label>Topic</label>
                <input class="input" name="topic" value="<?=h(KAFKA_DEFAULT_TOPIC)?>" list="topics" required>
                <datalist id="topics">
                  <option value="order-viewed"><option value="demo-topic"><option value="order-created">
                </datalist>
              </div>
              <div>
                <label>Key (tuỳ chọn)</label>
                <input class="input" name="mkey" placeholder="ví dụ: 1">
              </div>
            </div>
            <div style="margin-top:10px">
              <label>Value (JSON hoặc chuỗi)</label>
              <textarea class="input" name="mvalue" rows="5" placeholder='{"event":"manual_send","note":"hello"}' required></textarea>
            </div>
            <div style="display:flex;gap:10px;align-items:center;margin-top:12px">
              <button class="btn brand" type="submit">Gửi lên Kafka</button>
              <small class="muted" style="color:var(--muted)">REST Proxy: <?=h(KAFKA_REST_PROXY)?></small>
            </div>
          </form>
        </div>

        <div class="card">
          <h2>Messages đã gửi</h2>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th style="width:70px">ID</th>
                <th>Topic</th>
                <th>Key</th>
                <th>Value</th>
                <th>Status</th>
                <th style="width:140px">Created</th>
              </tr></thead>
              <tbody>
              <?php foreach($sent as $r): ?>
                <tr>
                  <td><?=h($r['id'])?></td>
                  <td><span class="pill neu"><?=h($r['topic'])?></span></td>
                  <td><?=h($r['mkey'])?></td>
                  <td style="white-space:pre-line"><?=h($r['mvalue'])?></td>
                  <td>
                    <?php if($r['status']==='sent'): ?>
                      <span class="pill ok">sent</span>
                    <?php elseif($r['status']==='error'): ?>
                      <span class="pill err" title="<?=h($r['error'])?>">error</span>
                    <?php else: ?>
                      <span class="pill neu"><?=h($r['status'])?></span>
                    <?php endif; ?>
                  </td>
                  <td><?=h($r['created_at'])?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if(!empty($r['error'])): ?><p class="muted">* Di chuột vào <b>error</b> để xem chi tiết.</p><?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: consumed -->
      <div>
        <div class="card">
          <h2>Messages đã tiêu thụ</h2>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th style="width:70px">ID</th>
                <th>Topic</th>
                <th>Key</th>
                <th>Value</th>
                <th>Partition</th>
                <th>Offset</th>
                <th style="width:140px">Consumed</th>
              </tr></thead>
              <tbody>
              <?php foreach($consumed as $r): ?>
                <tr>
                  <td><?=h($r['id'])?></td>
                  <td><span class="pill neu"><?=h($r['topic'])?></span></td>
                  <td><?=h($r['mkey'])?></td>
                  <td style="white-space:pre-line"><?=h($r['mvalue'])?></td>
                  <td><?=h($r['partition_id'])?></td>
                  <td><?=h($r['offset_val'])?></td>
                  <td><?=h($r['consumed_at'])?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="muted">Consumer Java subscribe: <b><?=h(getenv('TOPICS') ?: 'order-viewed')?></b></p>
          <hr class="sep">
          <p class="muted">Không thấy dữ liệu? Kiểm tra <code>docker compose logs -f java-consumer</code>.</p>
        </div>
      </div>
    </div>

    <div class="footer">© Nhom14 — Kafka demo. Dark mode auto.</div>
  </div>
</body>
</html>
