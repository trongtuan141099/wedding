# Triển khai trên cPanel

## Yêu cầu

Apache 2.4/LiteSpeed áp dụng `.htaccess`, PHP tối thiểu 8.0; nên dùng phiên bản từ 8.2 còn được hosting hỗ trợ.
Cần session, JSON, fileinfo; GD/FreeType để tạo ảnh chia sẻ có chữ. PHP cần ghi được data và uploads.
Giới hạn upload 16M, POST 20M có trong `.user.ini`; dùng MultiPHP INI Editor nếu cần.
Bật HTTPS và ghi log, giữ display_errors=Off.

## Clone trực tiếp từ GitHub

1. Xác nhận Document Root tại cPanel → Domains. Nếu là `/home/tthubvna/public_html`, repository
   `/home/tthubvna/public_html/wedding` tương ứng `https://tt-hub.io.vn/wedding/`.
2. Clone đúng branch. index.php, admin.php, .htaccess nằm ngay trong wedding, không thêm thư mục bọc.
3. **Đặt chính thư mục wedding thành 0755**, các thư mục tài nguyên 0755, tệp mã nguồn 0644.
   Git không lưu quyền thư mục. Có Terminal thì chạy bằng tài khoản cPanel sở hữu dự án:

   ```bash
   cd /home/tthubvna/public_html/wedding
   bash tools/fix-permissions.sh
   ```

   Dữ liệu riêng dùng 0750/0600, session 0700/0600. Nếu PHP dùng tài khoản khác, nhờ hosting cấu hình
   owner/group để đọc/ghi; không cấp 0777. Script không đổi owner hoặc thư mục cha của dự án.
4. Bật Show Hidden Files; xác nhận .htaccess, .user.ini, data/.htaccess và uploads/.htaccess có mặt.
5. Mở `/wedding/admin.php`, tạo mật khẩu lần đầu và lưu nội dung thiệp. Không cần setup.php.
   Clone mới dùng defaults.php; Git không kèm dữ liệu hoặc ảnh tải lên từ máy phát triển.
6. Trong Chia sẻ Zalo & Facebook, đặt URL công khai `https://tt-hub.io.vn/wedding`.
7. Kiểm tra đăng nhập, upload, thiệp, nhạc sau bấm mở, tải QR, RSVP/xe và duyệt lời chúc.
   `/wedding/data/state.php` phải bị chặn qua web.

Clone nằm trực tiếp trong thư mục web có thể phục vụ ngay, không cần .cpanel.yml.
Clone ngoài thư mục web cần một bước triển khai riêng sang Document Root.

## Cập nhật bằng Git

Sao lưu data, uploads và tài nguyên riêng. Trong Git Version Control → Manage, dùng **Update from Remote**.
Hoặc dùng `git pull --ff-only` trong Terminal. Kiểm tra lại quyền và chức năng sau cập nhật.

data/* và uploads/* được loại khỏi Git, ngoại trừ .htaccess; cập nhật thông thường giữ nguyên nội dung,
mật khẩu và dữ liệu khách. Không dùng `git clean -fdx`, reset hoặc giải nén bản cài mới đè dữ liệu thật.
Nếu từng commit dữ liệu riêng, .gitignore không xóa chúng khỏi lịch sử GitHub.

## Dùng ZIP

Tạo bằng `python tools/package-release.py --php /duong/dan/php`. Kết quả trong releases/:

- wedding-invitation-cpanel.zip: cài mới, có cấu hình công khai và tài nguyên cục bộ được tham chiếu.
- wedding-hosting-fix.zip: cập nhật mã, không có state, mật khẩu hoặc ảnh tải lên.
- *.sha256: mã kiểm tra gói.

Cả hai không chứa session, RSVP, lời chúc hoặc danh sách khách. Giải nén vào đúng thư mục web.
ZIP ghi quyền UNIX 0644/0755; vẫn kiểm tra quyền thư mục đích đã tồn tại. Xóa ZIP khỏi web sau giải nén.
Không ghi đè data/state.php của bản cài mới lên site đang dùng. Di chuyển dữ liệu riêng cần sao lưu riêng.

## Khi gặp lỗi

- 404/403: kiểm tra Document Root, Repository Path, quyền wedding và thư mục cha, .htaccess ở cả
  public_html và wedding, rồi xem cPanel → Metrics → Errors.
- 500: kiểm tra log PHP, phiên bản PHP, quyền/owner data và uploads; tệp PHP không dùng 0666.
- Không lưu/upload: kiểm tra quyền ghi và giới hạn POST/upload.
- Preview cũ: kiểm tra ảnh công khai, URL chia sẻ và làm mới cache nền tảng.

Nginx cần cấu hình riêng để chặn data, tests, tools, tệp ẩn/nội bộ và thực thi script trong uploads;
.htaccess không có hiệu lực trên Nginx.
