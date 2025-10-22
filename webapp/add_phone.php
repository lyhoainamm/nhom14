<?php ?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm điện thoại</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
  <div class="container">
    <h1>Thêm điện thoại</h1>
    <p><a href="index.php">Home</a> | <a href="search.php">Tìm kiếm</a> | <a href="cart.php">Giỏ hàng</a></p>
    <form id="f">
      <div class="mb-3"><label class="form-label">Model</label><input id="model" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Brand</label><input id="brand" class="form-control"></div>
      <div class="mb-3"><label class="form-label">Price</label><input id="price" type="number" step="0.01" class="form-control"></div>
      <div class="mb-3"><label class="form-label">Stock</label><input id="stock" type="number" class="form-control"></div>
      <button class="btn btn-primary">Add</button>
    </form>
    <div id="res" class="mt-3"></div>
  </div>
  <script>
  document.getElementById('f').addEventListener('submit', async e=>{ e.preventDefault(); const payload={model:document.getElementById('model').value, brand:document.getElementById('brand').value, price:parseFloat(document.getElementById('price').value)||0, stock:parseInt(document.getElementById('stock').value)||0}; const r=await (await fetch('phone_api.php',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)})).json(); const out=document.getElementById('res'); if(r.success){ out.innerHTML='<div class="alert alert-success">Added id='+r.id+'</div>'; document.getElementById('f').reset(); } else out.innerHTML='<div class="alert alert-danger">'+r.error+'</div>'; });
  </script>
  </body>
  </html>
