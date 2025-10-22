<?php
// Simple Phone store UI: list phones, add/edit/delete, create order
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phone Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
  <div class="container">
    <h1 class="mb-4">Phone Store — Demo</h1>
    <p><a href="index.php">Back to main</a></p>

    <div class="row">
      <div class="col-md-6">
        <h4>Add / Edit Phone</h4>
        <form id="phoneForm">
          <input type="hidden" id="phoneId">
          <div class="mb-3"><label class="form-label">Model</label><input id="model" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Brand</label><input id="brand" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Price</label><input id="price" type="number" step="0.01" class="form-control" value="0.00"></div>
          <div class="mb-3"><label class="form-label">Stock</label><input id="stock" type="number" class="form-control" value="0"></div>
          <button class="btn btn-primary" type="submit">Save</button>
          <button class="btn btn-secondary" type="button" id="cancelEdit">Cancel</button>
        </form>
      </div>

      <div class="col-md-6">
        <h4>Phones</h4>
        <div id="phonesList"></div>
      </div>
    </div>
  </div>

  <script>
  async function loadPhones(){
    const res = await fetch('phone_api.php');
    const data = await res.json();
    const out = document.getElementById('phonesList');
    if(!data.success){ out.innerHTML = '<div class="alert alert-danger">'+(data.error||'Error')+'</div>'; return }
    let html = '<ul class="list-group">';
    for(const p of data.rows){
      html += `<li class="list-group-item d-flex justify-content-between align-items-start">`+
              `<div><strong>#${p.id} ${escapeHtml(p.model)}</strong><div class="small text-muted">${escapeHtml(p.brand)} — $${p.price} — stock: ${p.stock}</div></div>`+
              `<div class="btn-group btn-group-sm" role="group">`+
                `<button class="btn btn-success" onclick="order(${p.id})">Order</button>`+
                `<button class="btn btn-primary" onclick="edit(${p.id})">Edit</button>`+
                `<button class="btn btn-danger" onclick="del(${p.id})">Delete</button>`+
              `</div></li>`;
    }
    html += '</ul>';
    out.innerHTML = html;
  }

  function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>'"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;" })[c]); }

  document.getElementById('phoneForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const id = document.getElementById('phoneId').value;
    const payload = { model: document.getElementById('model').value, brand: document.getElementById('brand').value, price: parseFloat(document.getElementById('price').value)||0, stock: parseInt(document.getElementById('stock').value)||0 };
    const url = 'phone_api.php' + (id?('?id='+id):'');
    const method = id? 'PUT' : 'POST';
    const resp = await fetch(url,{method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const data = await resp.json();
    if(data.success){ document.getElementById('phoneForm').reset(); document.getElementById('phoneId').value=''; loadPhones(); }
    else alert('Error: '+(data.error||'unknown'));
  });

  document.getElementById('cancelEdit').addEventListener('click', ()=>{ document.getElementById('phoneForm').reset(); document.getElementById('phoneId').value=''; });

  async function edit(id){ const r = await (await fetch('phone_api.php?id='+id)).json(); if(r.success){ const p = r.row; document.getElementById('phoneId').value=p.id; document.getElementById('model').value=p.model; document.getElementById('brand').value=p.brand; document.getElementById('price').value=p.price; document.getElementById('stock').value=p.stock; } }
  async function del(id){ if(!confirm('Delete phone #'+id+'?')) return; const r = await (await fetch('phone_api.php?id='+id,{method:'DELETE'})).json(); if(r.success) loadPhones(); else alert('Error: '+r.error); }

  async function order(id){ const qty = prompt('Quantity to order', '1'); if(!qty) return; const r = await (await fetch('order_api.php',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({phone_id:id, quantity: parseInt(qty)||1})})).json(); if(r.success) alert('Order created id='+r.id); else alert('Error: '+r.error); }

  loadPhones();
  </script>
  </body>
</html>
