<?php session_start(); ?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
  <div class="container">
    <h1>Giỏ hàng</h1>
  <p><a href="index.php">Home</a> | <a href="search.php">Tìm kiếm</a></p>
    <div id="cart"></div>
    <div class="mt-3"><button id="checkout" class="btn btn-success">Thanh toán</button></div>
  </div>
  <script>
  async function loadCart(){ const r=await (await fetch('cart_api.php')).json(); const out=document.getElementById('cart'); if(!r.success){ out.innerHTML='<div class="alert alert-danger">'+r.error+'</div>'; return } if(r.items.length===0){ out.innerHTML='<div class="alert alert-info">Giỏ hàng trống</div>'; return } let html='<ul class="list-group">'; for(const it of r.items){ html += `<li class="list-group-item d-flex justify-content-between align-items-start"><div><strong>${it.model}</strong><div class="small text-muted">${it.brand} — $${it.price}</div></div><div>Qty: ${it.quantity} <button class="btn btn-sm btn-danger" onclick="remove(${it.phone_id})">Remove</button></div></li>` } html += '</ul>'; out.innerHTML=html; }

  async function remove(id){ const r=await (await fetch('cart_api.php?id='+id,{method:'DELETE'})).json(); if(r.success) loadCart(); else alert('Error'); }
  document.getElementById('checkout').addEventListener('click', async ()=>{ const r=await (await fetch('cart_api.php',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({checkout:true})})).json(); if(r.success) { alert('Order created id='+r.order_ids.join(',')); window.location.reload(); } else alert('Error: '+r.error); });
  loadCart();
  </script>
  </body>
  </html>
