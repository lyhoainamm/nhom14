<?php
// Landing page
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Webapp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
    <div class="container">
      <h1 class="mb-4">Demo Webapp</h1>
      <p>Chọn chức năng:</p>
      <div class="list-group">
        <a class="list-group-item list-group-item-action" href="search.php">Tìm kiếm điện thoại</a>
  <!-- Thêm điện thoại: đã hợp nhất vào Quản lý (CRUD) -->
        <a class="list-group-item list-group-item-action" href="cart.php">Giỏ hàng / Thanh toán</a>
        <a class="list-group-item list-group-item-action" href="phones.php">Quản lý (CRUD) điện thoại</a>
      </div>
    </div>
  </body>
</html>
