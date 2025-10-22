Webapp demo (PHP + MySQL via XAMPP)

Mục tiêu: Một trang web nhỏ tích hợp 2 chức năng mẫu mà bạn có thể thay thế: "Thêm bản ghi" và "Tìm kiếm bản ghi". Dùng XAMPP (Apache + MySQL) để chạy local.

Files:
- index.php - UI (form thêm và tìm kiếm)
- db.php - PDO connector (chỉnh DB constants nếu cần)
- feature_add.php - API endpoint để thêm bản ghi (POST JSON)
- feature_search.php - API endpoint để tìm kiếm (GET q=...)
- sample.sql - SQL tạo database + bảng + dữ liệu mẫu

Cài đặt nhanh (Windows + XAMPP):

1) Mở XAMPP Control Panel, Start Apache và MySQL.

2) Tạo database và bảng bằng cách import `sample.sql` vào phpMyAdmin hoặc dùng PowerShell:

```powershell
# nếu mysql.exe nằm ở C:\xampp\mysql\bin
& "C:\\xampp\\mysql\\bin\\mysql.exe" -u root < "C:\\path\\to\\nhom14\\webapp\\sample.sql"
```

3) Copy thư mục `webapp` vào `C:\xampp\htdocs\`.

4) Mở trình duyệt: http://localhost/webapp/index.php

Cấu hình DB:

- Mặc định `db.php` dùng host=127.0.0.1, database=webapp_demo, user=root, password="" (XAMPP mặc định). Nếu bạn thay đổi user/password hoặc tên database, chỉnh `db.php`.

Kiểm tra API bằng PowerShell (ví dụ):

```powershell
# 1) Thêm bản ghi (POST JSON)
$body = @{ title = 'Test từ PowerShell'; description = 'Mô tả' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://localhost/webapp/feature_add.php' -Method Post -Body $body -ContentType 'application/json'

# 2) Tìm kiếm (GET)
Invoke-RestMethod -Uri "http://localhost/webapp/feature_search.php?q=Test"
```

Ghi chú:

- `sample.sql` chứa lệnh tạo database `webapp_demo` và bảng `tasks` cùng vài bản ghi mẫu.
- Đây là một ví dụ nhỏ để tích hợp nhanh cùng XAMPP. Nếu muốn tôi sẽ đổi hai chức năng mẫu (Thêm/Tìm) sang chức năng thực tế bạn đã mô tả — cho tôi biết chi tiết tính năng: inputs, outputs, và schema nếu có.

