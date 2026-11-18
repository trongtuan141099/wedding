# Wedding Invitation

Thiệp cưới và quản trị tiếng Việt dùng PHP, HTML/CSS, Tailwind CDN và JavaScript.
Chạy trực tiếp trên Apache 2.4/LiteSpeed; không cần Composer, Node, MySQL hoặc build.

## Chức năng

- Ba mẫu Song Hỷ, Ivory Maison, The Vow; tạo và sao chép nhiều thiệp.
- Chỉnh tên, gia đình, ảnh, lễ, tiệc, bản đồ, dress code, timeline và tài khoản nhận quà.
- Hiệu ứng mở thiệp, chữ Hỷ rơi, album 3D tự chuyển, phóng lớn ảnh, tự cuộn có thể dừng.
- Điều chỉnh tốc độ cuộn, thời gian xem/chuyển ảnh, cỡ chữ và cỡ tên cân đối trên điện thoại.
- Link theo slug, link cá nhân có tên khách; nhập danh sách, sao chép và xuất CSV.
- RSVP và yêu cầu xe, địa chỉ đón, số người, điện thoại; dữ liệu chỉ hiển thị cho quản trị.
- Lời chúc chờ duyệt, sổ lưu bút, lời chúc nổi; lịch tháng và đếm ngược.
- Hộp quà có Lưu QR; đã bỏ nút lưu ngày và ô lời nhắn trong RSVP.
- Tạo mật khẩu lần đầu trên web, đăng nhập, đổi mật khẩu và vô hiệu phiên cũ.
- Metadata Open Graph/Twitter từ máy chủ; ảnh chia sẻ 1200×630 khi có GD/FreeType.

## Cài đặt

Xem [DEPLOY.md](DEPLOY.md) để clone GitHub, sửa quyền 0755/0644, dùng ZIP và cập nhật giữ dữ liệu.
PHP tối thiểu 8.0; nên chọn bản từ 8.2 còn được hosting hỗ trợ. Cần session, JSON, fileinfo;
GD/FreeType dùng cho ảnh chia sẻ có chữ. Mở `admin.php` để tự tạo mật khẩu, không có mật khẩu mặc định.

Chạy cục bộ với XAMPP: `.\start-local.ps1`, rồi mở `http://127.0.0.1:8088/admin.php`.
Hoặc dùng `php -S 127.0.0.1:8088` trong thư mục dự án; server này chỉ dùng phát triển.

## Cấu trúc

| Tệp/thư mục | Vai trò |
| --- | --- |
| `index.php`, `admin.php` | Thiệp và quản trị |
| `api.php`, `studio-api.php` | API form, khách mời, upload, RSVP và lời chúc |
| `bootstrap.php`, `account.php` | Lưu trữ JSON, validation, session, CSRF, mật khẩu |
| `defaults.php` | Nội dung khởi tạo cho bản clone mới |
| `social.php`, `social-image.php` | Metadata và ảnh chia sẻ |
| Các tệp CSS/JS ở gốc | Form, preview, bố cục và hiệu ứng |
| `assets/` | Ảnh, nhạc, font và favicon mẫu |
| `data/`, `uploads/` | Dữ liệu và tệp tải lên, được loại khỏi Git |
| `tests/`, `tools/` | Kiểm tra, đóng gói, sửa quyền |

## Lưu trữ và vận hành

`data/state.php` chứa JSON sau tiền tố PHP exit, được chặn thêm bằng `.htaccess`.
Mật khẩu hash trong `data/credentials.php`, session trong `data/sessions/`.
Lưu trữ dùng flock, ghi nguyên tử và revision để tránh lưu đè giữa các tab.
Sao lưu data, uploads và tài nguyên riêng trước khi cập nhật; không đưa chúng lên GitHub.
Chưa có adapter SQL; có thể thay lớp lưu trữ bằng repository dùng transaction.

Giới hạn: 100 thiệp, 2.000 khách/thiệp, 10.000 RSVP và 10.000 lời chúc/thiệp.
Gửi công khai tối đa 5 lượt/loại/IP/giờ; khách dùng chung IP có thể gặp giới hạn này.
Link cá nhân có thể được chuyển tiếp. Yêu cầu xe chỉ ghi nhận, chưa có điều phối/SMS.
API quản trị yêu cầu đăng nhập và CSRF; nội dung được escape, upload kiểm tra MIME/dung lượng,
CSV phòng công thức, lời chúc phải được duyệt trước khi công khai.

Nếu quên mật khẩu, quản trị hosting có thể sao lưu rồi gỡ `data/credentials.php` và tạo lại ngay tại
admin.php. Nếu dùng `WEDDING_ADMIN_PASSWORD_HASH`, đổi hash ở cấu hình môi trường hosting.

## Chia sẻ và tài nguyên

Đặt URL công khai đúng thư mục, ví dụ `https://tt-hub.io.vn/wedding`, trong phần chia sẻ quản trị.
Metadata không chứa tên khách/token cá nhân. Hiển thị và cache preview do Zalo/Facebook quyết định.
Tham khảo [Open Graph](https://ogp.me/) và [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/).

Ảnh/nhạc mẫu lấy từ tài nguyên công khai của thiệp chungdoi.com do người dùng cung cấp.
Cần quyền sử dụng hoặc thay bằng tài nguyên của bạn trước khi phát hành.
Font Playfair Display kèm giấy phép SIL OFL trong `assets/fonts/OFL.txt`.
Tailwind CDN, Google Fonts và Maps cần Internet; CSS nền tảng và font dự phòng có sẵn.

## Kiểm tra và đóng gói

Yêu cầu phát triển: PHP có GD/FreeType, Python 3.9+, Pillow (`python -m pip install Pillow`).
Node chỉ cần khi kiểm tra cú pháp JavaScript.

```powershell
python tests/run-tests.py
python tools/package-release.py --php D:/xampp/php/php.exe
```

Kiểm tra dùng dữ liệu tạm, không thay dữ liệu hay mật khẩu đang dùng. ZIP và SHA-256 nằm trong
`releases/`, không đưa vào Git. Gói cài mới chứa cấu hình công khai đã làm sạch và ảnh được tham chiếu;
gói cập nhật chỉ chứa mã. Không đóng gói mật khẩu, session, RSVP, lời chúc hoặc danh sách khách.
