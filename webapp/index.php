<?php
// Simple web UI to interact with two features: add task, search tasks
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Webapp - 2 features</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
    <div class="container">
      <h1 class="mb-4">Demo: Thêm và Tìm kiếm</h1>

      <div class="row">
        <div class="col-md-6">
          <h4>Feature 1 — Thêm bản ghi</h4>
          <form id="addForm">
            <div class="mb-3">
              <label class="form-label">Tiêu đề</label>
              <input id="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Mô tả</label>
              <textarea id="description" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Thêm</button>
            <div id="addResult" class="mt-3"></div>
          </form>
        </div>

        <div class="col-md-6">
          <h4>Feature 2 — Tìm kiếm</h4>
          <div class="mb-3">
            <input id="q" class="form-control" placeholder="Nhập từ khóa tiêu đề và Enter để tìm">
          </div>
          <div id="searchResults"></div>
        </div>
      </div>
    </div>

    <script>
    document.getElementById('addForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const title = document.getElementById('title').value.trim();
      const description = document.getElementById('description').value.trim();
      const resEl = document.getElementById('addResult');
      resEl.innerText = 'Đang gửi...';

      try{
        const resp = await fetch('feature_add.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({title, description})
        });
        const data = await resp.json();
        if(data.success){
          resEl.innerHTML = '<div class="alert alert-success">Thêm thành công (id='+data.id+')</div>';
          document.getElementById('addForm').reset();
        } else {
          resEl.innerHTML = '<div class="alert alert-danger">Lỗi: '+(data.error||'Không rõ')+'</div>';
        }
      }catch(err){
        resEl.innerHTML = '<div class="alert alert-danger">Request failed: '+err.message+'</div>';
      }
    });

    document.getElementById('q').addEventListener('keydown', async function(e){
      if(e.key === 'Enter'){
        e.preventDefault();
        const q = this.value.trim();
        const out = document.getElementById('searchResults');
        out.innerHTML = 'Đang tìm...';
        try{
          const resp = await fetch('feature_search.php?q='+encodeURIComponent(q));
          const data = await resp.json();
          if(data.success){
            if(data.rows.length === 0){
              out.innerHTML = '<div class="alert alert-info">Không tìm thấy</div>';
            } else {
              let html = '<ul class="list-group">';
              for(const r of data.rows){
                html += '<li class="list-group-item">';
                html += ' <strong>#'+r.id+'</strong> ' + escapeHtml(r.title);
                html += ' <div class="small text-muted">'+escapeHtml(r.description)+'</div>';
                html += '</li>';
              }
              html += '</ul>';
              out.innerHTML = html;
            }
          } else {
            out.innerHTML = '<div class="alert alert-danger">Lỗi: '+(data.error||'Không rõ')+'</div>';
          }
        }catch(err){
          out.innerHTML = '<div class="alert alert-danger">Request failed: '+err.message+'</div>';
        }
      }
    });

    function escapeHtml(s){
      if(!s) return '';
      return s.replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];
      });
    }
    </script>
  </body>
</html>
