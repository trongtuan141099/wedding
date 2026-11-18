# Sửa lỗi HTTP 500 sau khi giải nén trên cPanel

## Lỗi đã xác định trong ZIP cũ

Các tệp trong bản ZIP trước có metadata Windows với quyền 0666. Ảnh File Manager
cho thấy quyền này đã được giữ lại sau khi giải nén. Gói mới ghi rõ metadata UNIX:
tệp 0644, thư mục 0755. Một số PHP handler như suPHP từ chối chạy PHP có quyền ghi
cho Group/World, gây HTTP 500.

Tài liệu cPanel:
https://support.cpanel.net/hc/en-us/articles/1500009376861-PHP-website-error-500-Apache-error-log-reports-SoftException-in-Application-cpp

## Sửa website đã upload, giữ nguyên dữ liệu

1. Sao lưu website và thư mục data qua cPanel trước khi cập nhật.
2. Trong File Manager, mở public_html. Chọn các **tệp**, không chọn thư mục, rồi mở
   Permissions và đặt 0644. Đặc biệt sửa index.php, admin.php, api.php và các tệp PHP
   khác đang là 0666. Các thư mục giữ 0755; không dùng 0777/0666.
3. Upload và giải nén **wedding-hosting-fix.zip** ngay trong public_html, cho phép ghi
   đè mã nguồn. Gói sửa không chứa data/state.php, data/credentials.php hoặc ảnh tải lên.
   Vì vậy gói này không khởi tạo lại nội dung, khách mời hoặc mật khẩu đang có.
4. Kiểm tra lại quyền sau giải nén. Một số công cụ có thể giữ quyền của tệp cũ;
   nếu vẫn là 0666, đổi thủ công sang 0644.
5. Vào MultiPHP Manager / Select PHP Version, chọn PHP 8.2 trở lên cho đúng tên miền.
   Mã cần PHP 8.0 tối thiểu; nên dùng phiên bản còn được hosting hỗ trợ.
6. Bật Settings → Show Hidden Files để xác nhận có .htaccess và .user.ini ở public_html,
   và .htaccess trong data/ và uploads/. Không xóa tệp bảo vệ trong data/.
7. Mở trang chính. Nếu vẫn báo 500, mở cPanel → Metrics → Errors hoặc Error Log,
   lấy các dòng lỗi vừa phát sinh khi tải lại trang. Không bật hiển thị lỗi chứa đường
   dẫn hoặc dữ liệu riêng trên website công khai.

## Đọc log để phân biệt lỗi còn lại

- `is writeable by group`, `SoftException`: kiểm tra quyền 0644/0755 và chủ sở hữu tệp.
- `Parse error`, lỗi gần `mixed`, `match`, `=>`: kiểm tra phiên bản PHP đang áp dụng.
- `Permission denied`, `Storage lock failed`: tài khoản PHP cần quyền đọc/ghi data/
  và quyền ghi uploads/; không sửa bằng cách cấp 0777. Nhờ hosting sửa owner nếu cần.
- `Options not allowed here`, `Invalid command`: hosting có thể hạn chế chỉ thị
  .htaccess. Gửi đúng dòng log để sửa cấu hình tương ứng; không tắt toàn bộ bảo vệ data/.
- Website đã mở nhưng chưa có mật khẩu: mở `/admin.php` để tạo mật khẩu ngay trên web.
  Không cần Terminal hoặc chạy setup.php ở phiên bản mới.

Gói **wedding-invitation-cpanel.zip** là bản cài mới đầy đủ, có cấu hình thiệp khởi tạo.
Không dùng tệp data/state.php của gói cài mới để ghi đè dữ liệu thật trên server.
