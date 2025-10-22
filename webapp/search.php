<?php
// Search page for phones
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tìm kiếm điện thoại</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
  <div class="container">
    <h1>Tìm kiếm điện thoại</h1>
  <p><a href="index.php">Home</a> | <a href="cart.php">Giỏ hàng</a></p>
    <div class="mb-3"><input id="q" class="form-control" placeholder="Tìm theo model hoặc brand, nhấn Enter"></div>
    <div id="results"></div>
  </div>
  <script>
  document.getElementById('q').addEventListener('keydown', async function(e){ if(e.key==='Enter'){ e.preventDefault(); const q=this.value.trim(); const r=await (await fetch('phone_api.php?q='+encodeURIComponent(q))).json(); const out=document.getElementById('results'); if(!r.success){ out.innerHTML='<div class="alert alert-danger">'+r.error+'</div>'; return } if(r.rows.length===0){ out.innerHTML='<div class="alert alert-info">Không tìm thấy</div>'; return } let html='<div class="list-group">'; for(const p of r.rows){ html += `<div class="list-group-item d-flex justify-content-between align-items-start"><div><strong>${p.model}</strong><div class="small text-muted">${p.brand} — $${p.price} — stock: ${p.stock}</div></div><div><button class="btn btn-sm btn-primary" onclick="addToCart(${p.id})">Thêm vào giỏ</button></div></div>` } html += '</div>'; out.innerHTML=html; } });

  async function addToCart(id){ const r = await (await fetch('cart_api.php',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({phone_id:id, quantity:1})})).json(); if(r.success) alert('Đã thêm vào giỏ'); else alert('Lỗi: '+r.error); }
  </script>
  </body>
  </html>
