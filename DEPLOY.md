# Đưa thiệp cưới lên server / cPanel

Nếu bản đã upload báo HTTP 500 và các tệp có quyền 0666, dùng **wedding-hosting-fix.zip**
và làm theo **HOSTING-FIX.md**. Gói sửa giữ nguyên state, mật khẩu và ảnh tải lên.

## Cài mới

1. Chọn PHP 8.2 trở lên, bật extensions `fileinfo`, `json`, `session`.
2. Upload `wedding-invitation-cpanel.zip` vào `public_html` hoặc thư mục website.
3. Giải nén ngay trong thư mục website. `index.php`, `admin.php`, `.htaccess` phải nằm
   cùng cấp, không có thêm thư mục bọc ngoài. Bật hiển thị tệp ẩn trong File Manager.
4. Gói mới có metadata UNIX: các tệp 0644, thư mục 0755. Kiểm tra sau giải nén, đặc biệt
   không để tệp PHP ở quyền 0666 vì PHP handler của hosting có thể từ chối chạy.
   Cho tài khoản PHP quyền ghi `data/` và `uploads/`; thông thường 0750/0755 tùy hosting.
   Không dùng quyền 0777.
5. Mở `https://tenmien/admin.php`, tự nhập và xác nhận mật khẩu quản trị lần đầu.
   Không cần Terminal hoặc setup.php. Sau này đổi tại Tài khoản & mật khẩu.
   Nếu đã có mật khẩu thì trang chỉ hiện đăng nhập, không cho tạo lại công khai.
6. Bật HTTPS / AutoSSL. Giữ `display_errors=Off`; bật ghi log lỗi tại hosting.
7. Bật GD / FreeType để tạo ảnh chia sẻ 1200×630 có tên và ngày cưới.
   Trong Chia sẻ Zalo & Facebook, đặt URL công khai HTTPS đúng tên miền/thư mục.
   Kiểm tra giới hạn PHP: `upload_max_filesize=16M`, `post_max_size=20M`.
   `.user.ini` đã kèm cấu hình; dùng MultiPHP INI Editor nếu hosting không áp dụng tệp này.
8. Kiểm tra thiệp, phát nhạc, tải QR, gửi thử RSVP, yêu cầu xe và duyệt lời chúc.
   Kiểm tra URL `/data/state.php` trả 403 qua web trước khi nhận khách thật.
9. Chia sẻ link từ dashboard trên tên miền thật. Xóa bản ZIP khỏi thư mục web sau khi
   giải nén và lưu một bản ngoại tuyến.

## Gói ZIP chứa gì?

- Mã nguồn mới nhất, gồm nút Lưu QR và các chỉnh sửa giao diện đã yêu cầu.
- Nội dung/cấu hình của các thiệp hiện có, đường dẫn thiệp, ảnh, nhạc và QR cục bộ
  đang được tham chiếu. Các URL HTTPS bên ngoài được giữ nguyên, không tải hộ.
- Các tệp `.htaccess` bảo vệ và hướng dẫn triển khai.

Gói **không chứa** mật khẩu quản trị, session, RSVP, danh sách link khách cá nhân,
lời chúc hoặc log. Các danh sách này bắt đầu trống trên server mới. Nếu cần các link
khách đã gửi tiếp tục hoạt động, cần một đợt di chuyển dữ liệu riêng có sao lưu bảo mật.
Link thiệp chung theo slug được giữ nguyên khi đưa lên tên miền mới.

## Nếu server đã có website đang dùng

Sao lưu trước. Chỉ cập nhật các tệp mã nguồn và tài nguyên cần thiết; **không ghi đè**
`data/state.php`, `data/credentials.php` hoặc thư mục `uploads/` đang có dữ liệu thật.
`data/state.php` trong ZIP là dữ liệu khởi tạo cho **cài mới**.

Gói dành cho Apache 2.4 / LiteSpeed có hỗ trợ `.htaccess`. Nếu dùng Nginx, phải cấu hình
chặn truy cập `/data/` và thực thi script trong uploads theo hướng dẫn README.md.
