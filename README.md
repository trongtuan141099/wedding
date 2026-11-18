# Chung Đôi Studio — Wedding Invitation

Dashboard và thiệp cưới tiếng Việt, PHP + HTML5 + Tailwind CDN + JavaScript thuần.
Không cần Composer, Node, MySQL hay bước build. Dữ liệu thật lưu trên máy chủ bằng JSON,
không phụ thuộc LocalStorage của khách mời.


## Giao diện Song Hỷ — bản 17/11/2026

Bản này chỉnh mẫu Song Hỷ theo giao diện điện thoại: nền xanh đậm–kem, dải tiêu đề
xanh toàn chiều ngang, tên hai bạn rõ ràng, ảnh vòm lớn và nội dung có khoảng thở.
Màn mở thiệp có 18 chữ Hỷ rơi bằng CSS, watermark trang trí và hiệu ứng mở màn.
Nội dung từng phần hiện nhẹ khi cuộn; các chuyển động được giảm khi thiết bị yêu cầu
“Reduce Motion”. Khối RSVP dùng lựa chọn tham dự dễ chạm, vẫn giữ yêu cầu xe đưa đón.

Album 3D tự chuyển sau khoảng 4,8 giây khi nằm trong vùng nhìn. Ảnh kế tiếp được tải
và giải mã trước khi chuyển; thanh tiến trình biểu thị lượt chuyển. Album tạm dừng
khi ra khỏi màn hình, chuyển tab, rê chuột lên ảnh, mở ảnh lớn hoặc lấy nét vào ảnh;
tự tiếp tục khi thích hợp. Sau bấm mũi tên/vuốt, có 7 giây để xem ảnh. Nút dừng chủ động
giữ album dừng đến khi bấm phát lại. Xem trước quản trị không tự chạy album.

Ảnh bìa mẫu có kích thước 1070×1800 và các ảnh album khoảng 1200×1800, đủ chi tiết cho
khung điện thoại. Ảnh tự tải lên giữ nguyên độ phân giải; nên chọn ảnh dọc tối thiểu
1200px chiều ngang, tránh ảnh đã nén qua ứng dụng nhắn tin. Không có bộ lọc làm mờ ảnh.

Hai tệp mới cần upload cùng mã còn lại: `invitation.css` và `invitation.js`. Các URL
CSS/JavaScript có mã phiên bản để trình duyệt cập nhật giao diện khi nâng cấp.

## Bản nâng cấp Studio v2

- **Bộ sưu tập thiệp** → **Tạo thiệp mới**: chọn Song Hỷ, Ivory Maison hoặc The Vow.
  Có thể sao chép nội dung **đã lưu** của thiệp hiện tại. Mỗi thiệp có nội dung, revision,
  danh sách khách, RSVP và sổ lưu bút độc lập; tối đa 100 thiệp.
- **Bộ sưu tập thiệp** → **Tên thiệp & đường dẫn chia sẻ**: đặt tên đường dẫn bằng chữ
  thường không dấu, số và dấu `-`, dài 3–70 ký tự. Ví dụ:
  `https://tenmiencuaban.com/index.php?i=tuan-va-trinh`. Không cần URL rewrite trên cPanel.
  Đổi đường dẫn làm các link mang tên cũ hết hiệu lực, cần gửi lại link mới.
- **Link mời cá nhân**: nhập từng khách hoặc dán nhiều dòng `Tên khách | Cách xưng hô`.
  Mỗi khách có mã ngẫu nhiên riêng; tên hiển thị trên phong bì, phần kính mời và form.
  Sao chép từng link hoặc xuất toàn bộ danh sách ra CSV. Sửa tên giữ nguyên link;
  **Đổi link** tạo mã mới và thu hồi mã cũ. Tối đa 2.000 khách/thiệp, 100 khách/lần nhập.
  Link cá nhân là một lời mời có mã, không phải cơ chế xác minh danh tính: người nhận
  có thể chuyển tiếp link. Không đặt thông tin nhạy cảm trong tên hoặc lời mời.
- **Tài khoản & mật khẩu**: nhập mật khẩu hiện tại, mật khẩu mới và xác nhận. Các phiên
  khác hết hiệu lực sau khi đổi; phiên hiện tại tiếp tục hoạt động. Nếu đang dùng demo
  cục bộ, giao diện cho phép tạo mật khẩu đầu tiên. Mật khẩu quản lý bằng biến môi trường
  cần đổi ở hosting. Mật khẩu đang dùng từ bản cũ được giữ nguyên, nhưng cần đăng nhập
  lại một lần do phiên đăng nhập nay có kiểm tra phiên bản mật khẩu.
- **Giao diện & âm nhạc** → **Giao diện thiệp**: chọn mẫu, cỡ chữ 1–1.4, cỡ tên 1–1.6,
  hiệu ứng mở màn lụa/nở hoa, tự cuộn 10–60 pixel/giây, lời chúc nổi, lịch tháng và tự
  chuyển ảnh. Sau khi chọn mẫu, màu phối hợp và album 3D được áp dụng vào bản nháp;
  bấm **Lưu thay đổi** để cập nhật thiệp chính.
- **Thư viện ảnh** → **Album 3D**: ảnh giữa nổi bật, ảnh bên cạnh nghiêng theo phối cảnh;
  hỗ trợ nút chuyển, phím mũi tên, vuốt và bật/dừng tự chuyển. Bấm ảnh để xem lớn.
- Thiệp có thanh điều khiển **Tự cuộn / Gửi lời chúc / Xác nhận / Quà mừng** tùy mục
  được bật. Tự cuộn dừng khi khách chạm, lăn chuột, gõ form, mở ảnh hoặc chuyển tab;
  bấm nút để tiếp tục. Chế độ xem trước không tự cuộn hoặc phát nhạc. Tùy chọn hệ thống
  giảm chuyển động sẽ tắt tự cuộn mặc định, chuyển ảnh tự động và lời chúc nổi.
- **RSVP / xe đưa đón**: chọn sẽ tham dự, bật yêu cầu xe, nhập địa chỉ đón, số người
  đón và số điện thoại. Số người đón không vượt số người tham dự. Dữ liệu chỉ hiển thị
  trong **Khách mời & RSVP** và CSV quản trị, không đưa lên API công khai hoặc lời chúc.
  Gửi lại RSVP bằng cùng link cá nhân sẽ cập nhật phản hồi trước; link chung vẫn nhận
  các phản hồi độc lập. Đây là ghi nhận nhu cầu, chưa có tích hợp điều phối xe/SMS.
- Lịch tháng xếp đúng thứ, số ngày và đánh dấu ngày cưới bằng trái tim. Nút lưu ngày
  và ô lời nhắn trong RSVP đã được bỏ theo yêu cầu; sổ gửi lời chúc vẫn hoạt động.
- Lời chúc được duyệt xuất hiện trong sổ lưu bút và bong bóng nổi, trôi lên rồi mờ dần.
  Thiệp đang mở cập nhật lời chúc đã duyệt mỗi 45 giây; khách có thể ẩn bong bóng.

### Nâng cấp từ phiên bản trước

Sao lưu `data/`, `uploads/` và `assets/` trước khi upload mã mới. **Giữ nguyên**
`data/state.php`, `data/credentials.php`, ảnh/nhạc đã tải lên. Không thay cả thư mục
`data/` bằng bản trống. Bộ ZIP chỉ chứa mã và `.htaccess` của các thư mục dữ liệu;
không chứa thông tin quản trị hay khách thật. Bản cũ được đọc tương thích, các thiết lập
mới tự bổ sung; dữ liệu nhiều thiệp được lưu thêm trong cùng state có khóa giao dịch.
Không hạ cấp về mã cũ sau khi đã tạo nhiều thiệp. Bản nâng cấp trong workspace đã bật
album 3D cho thiệp chính và giữ nguyên nội dung, RSVP, lời chúc, mật khẩu hiện có.

Các tệp mới: `studio-api.php` (API quản lý), `studio.js` (giao diện quản lý nhiều thiệp),
`experience.js` (hiệu ứng thiệp, lịch, xe đưa đón, cuộn và 3D), `studio.css` (mẫu và bố cục).

### Kiểm thử độc lập

Chạy `python tests/run-tests.py`. Script mở PHP test trên `127.0.0.1:8091`, dùng thư mục
lưu trữ tạm bên trong `tests/`, chạy hai bộ HTTP test rồi dừng server và dọn dữ liệu tạm.
Không dùng dữ liệu hay mật khẩu quản trị thật. Cần Python 3.9+, PHP trong PATH hoặc
`D:/xampp/php/php.exe`, và cổng 8091 chưa được sử dụng. Bộ kiểm tra bao gồm lưu thiệp,
CSRF, upload, phân quyền, đường dẫn trùng, tách dữ liệu, link khách, RSVP xe đưa đón,
riêng tư dữ liệu, revision và đổi mật khẩu/thu hồi phiên. Desktop và chiều rộng 390px
đã được kiểm tra qua trình duyệt; chưa kiểm thử thiết bị iOS vật lý hoặc tải lớn.

## Chạy trên XAMPP

1. Thư mục hiện tại: `D:\xampp\htdocs\wedding`.
2. Bật Apache trong XAMPP Control Panel.
3. Mở Terminal tại thư mục dự án, chạy:

```powershell
D:\xampp\php\php.exe setup.php
```

4. Lưu mật khẩu ngẫu nhiên được in ra bằng trình quản lý mật khẩu.
5. Mở `http://localhost/wedding/admin.php`, đăng nhập bằng mật khẩu đó.
6. Thiệp khách mời: `http://localhost/wedding/`.

Không có tài khoản/mật khẩu mặc định. Khi chưa cấu hình mật khẩu, chỉ **PHP development
server chạy trên loopback** mới mở chế độ demo quản trị. Apache/XAMPP và hosting luôn
yêu cầu cấu hình mật khẩu.

Để xem thử nhanh, không cần Apache:

```powershell
.\start-local.ps1
```

Script dùng thư mục upload tạm trong dự án để tránh lỗi quyền ghi `xampp/tmp` khi chạy
trong môi trường giới hạn. Mở `http://127.0.0.1:8088/admin.php`. Dừng bằng Ctrl+C. Chế độ demo không có hiệu lực
sau khi chạy `setup.php`. Không dùng PHP development server để phục vụ Internet.

## Đưa lên cPanel

1. Dùng PHP **8.2 trở lên** được nhà cung cấp hỗ trợ, với extensions `fileinfo`, `json`,
   `session`. Mã cũng đã được kiểm tra với PHP 8.0.30 của XAMPP, nhưng bản PHP cũ không
   nên dùng trên hosting công khai. Cần Apache 2.4 / LiteSpeed có hỗ trợ `.htaccess`.
2. Upload mã nguồn và thư mục `assets/`, `data/.htaccess`, `uploads/.htaccess` vào
   `public_html/` hoặc thư mục con. Bật hiển thị tệp ẩn để không bỏ sót `.htaccess`.
   Không upload thư mục `tests/`, `previews/`, `start-local.ps1`, bản ZIP, session, log hoặc thông tin quản trị của máy local.
3. `data/` và `uploads/` cần quyền ghi bởi tài khoản PHP. Thường thư mục 0750 / 0755
   và tệp 0640 / 0644 là đủ tùy cấu hình hosting. Không đặt 0777.
4. Trong cPanel Terminal/SSH, chuyển vào thư mục vừa upload và chạy `php setup.php`.
   Lưu mật khẩu được in ra. Chương trình chỉ chạy CLI, không mở cài đặt qua web.
5. Nếu hosting không có Terminal, tạo hash trên máy local:

```powershell
D:\xampp\php\php.exe setup.php
```

   Upload **chỉ** `data/credentials.php` vừa tạo qua File Manager bảo mật của cPanel.
   Hoặc đặt biến môi trường `WEDDING_ADMIN_PASSWORD_HASH` bằng một hash do PHP
   `password_hash(..., PASSWORD_DEFAULT)` tạo. Không đưa mật khẩu thô vào mã nguồn.
6. Bật HTTPS (AutoSSL), tắt `display_errors`, bật nhật ký lỗi của hosting.
7. Đặt `upload_max_filesize = 16M`, `post_max_size = 20M` trong MultiPHP INI Editor.
   Giới hạn ứng dụng: ảnh 8 MB, nhạc 15 MB, ảnh tối đa 40 megapixel, 40 mục mỗi danh sách.
8. Truy cập `/admin.php`, kiểm tra ảnh/nhạc, lưu thiệp và gửi thử RSVP trên trang chính.
   Tệp dưới `/data/` phải trả **403** qua web. Kiểm tra trước khi nhận khách thật.
9. Chia sẻ URL HTTPS của trang thiệp. Liên kết localhost chỉ hoạt động trên máy của bạn.

Nếu dùng Nginx, cần cấu hình `location ^~ /data/ { deny all; }` tương ứng với đường dẫn
ứng dụng, chặn PHP/HTML/SVG trong uploads, tắt directory listing và chặn truy cập các
tệp nội bộ như `bootstrap.php`, `defaults.php`, ZIP, README và tests. Không dùng nguyên
bộ trên Nginx khi chưa thiết lập những quy tắc này.

## Cách chỉnh thiệp

- **Nội dung thiệp**: mở accordion; chỉnh tên, hai gia đình, lời báo tin và thứ tự hiển thị.
- **Ảnh đầu thiệp**: tải ảnh dọc JPG/PNG/WebP. Album hỗ trợ lưới, ghép ảnh, hoặc trình
  chiếu cuộn ngang; nút ← đưa ảnh lên trước. Bấm ảnh trên thiệp để xem lớn và chuyển ảnh.
- **Buổi lễ / Lịch trình / Hộp quà**: thêm hoặc xóa các mục độc lập.
- **Tiệc cưới**: ngày giờ theo Việt Nam UTC+7; 24h hoặc AM/PM; giờ đón khách; đếm ngược;
  lịch tháng; bản đồ nhúng từ địa chỉ và nút chỉ đường từ liên kết Google Maps.
- **RSVP**: form trực tiếp hoặc nút mở form; số khách tự chọn tối đa 20 hoặc giới hạn 1–20.
  Tắt RSVP để ngừng tiếp nhận phản hồi. Thông tin xác nhận chỉ quản trị viên xem được.
- **Dress code**: chọn bảng màu gợi ý hoặc chỉnh từng màu.
- **Lưu QR**: nút dưới mỗi tài khoản tải ảnh QR đã upload về thiết bị. Với đường dẫn
  ảnh HTTPS bên ngoài, ảnh mở ở tab mới để nhấn giữ/lưu bằng trình duyệt.
- **Hộp quà**: tự nhập tên ngân hàng, số tài khoản và chủ tài khoản; tải QR từ ứng dụng
  ngân hàng. QR không được tự tạo từ thông tin gõ tay. Kiểm tra QR và số tài khoản thật.
- **Giao diện & âm nhạc**: màu chủ đạo, màu giấy, nhạc MP3/OGG và âm lượng.
  Nhạc phát sau khi bấm “Mở thiệp”; có nút tạm dừng. Bản xem trước không phát tự động.
- **Khách mời & RSVP**: số người tham dự, tìm tên, lọc trạng thái, xuất CSV cho Excel.
- **Sổ lưu bút**: duyệt/ẩn từng lời chúc. Lời chúc mới mặc định không công khai.
- **Lưu thay đổi**: cập nhật thiệp trên máy chủ. Bản nháp LocalStorage chỉ giúp tránh
  mất công chỉnh trên cùng trình duyệt; khôi phục/bỏ bản nháp ở cuối form.
- **Xuất/Nhập JSON**: sao lưu cấu hình thiệp, không bao gồm RSVP, lời chúc, ảnh hay nhạc.
  Nhập JSON chỉ tạo bản nháp; cần bấm Lưu thay đổi. Nội dung nhập được kiểm tra phía PHP.
- **Chia sẻ thiệp**: sao chép liên kết sau khi lưu; khung điện thoại phản ánh bản nháp
  ngay khi gõ, trang chính chỉ phản ánh dữ liệu đã lưu.

Xóa ảnh khỏi thiệp không xóa tệp vật lý để tránh phá bản nháp/cấu hình khác. Dọn các
tệp không còn dùng trong `uploads/` qua File Manager sau khi sao lưu và đối chiếu cấu hình.

## Cấu trúc

```text
index.php        Thiệp công khai và metadata
admin.php        Đăng nhập, dashboard, RSVP và duyệt lời chúc
style.css        Giao diện dashboard, thiệp và responsive
script.js        Form, xem trước postMessage, tương tác thiệp
api.php          API cùng nguồn, phân quyền, upload và lưu dữ liệu
bootstrap.php    Session, CSRF, JSON repository, validation
defaults.php     Nội dung thiệp ban đầu
setup.php        Tạo mật khẩu quản trị (chỉ CLI)
assets/          Ảnh và nhạc mẫu, favicon
uploads/         Tệp tải lên được kiểm tra MIME và giới hạn dung lượng
data/            Dữ liệu, thông tin quản trị và session (chặn truy cập web)
tests/smoke.py   Kiểm tra HTTP cục bộ và tự hoàn nguyên dữ liệu
```

JSON repository dùng khóa `flock` và ghi tệp tạm rồi thay thế nguyên tử. Cấu hình có
revision để phát hiện hai tab lưu đè nhau. RSVP/lời chúc sửa cùng state trong một giao
dịch khóa. `data/state.php` chứa JSON phía sau tiền tố PHP `exit`, và được chặn thêm bằng
`.htaccess`. Không sửa tay khi có traffic. Sao lưu `data/` + `uploads/` + `assets/` bằng
công cụ backup hosting; bảo vệ backup vì có danh sách khách và thông tin ngân hàng.
Phiên đăng nhập được lưu riêng trong `data/sessions/`. Tắt RSVP/xóa backup theo chính
sách lưu giữ dữ liệu của bạn sau sự kiện.

API được tách khỏi UI. Để chuyển MySQL/SQLite, thay adapter `state()` trong bootstrap
bằng repository dùng transaction; giữ hợp đồng `config`, `revision`, `responses`,
`wishes`. Bản giao hiện tại **chưa bao gồm** adapter SQL. JSON phù hợp thiệp cưới quy mô
nhỏ; không dùng như nền tảng SaaS nhiều khách hàng. 10.000 RSVP và 10.000 lời chúc là
giới hạn bảo vệ dung lượng. API chống gửi lặp theo IP (5 lượt/loại/giờ); không gộp các
phản hồi cùng tên vì khách gia đình có thể trùng tên. Nhiều khách dùng chung IP có thể
bị giới hạn; điều chỉnh `rate_limit()` nếu cần. Với proxy, cấu hình IP ở web server,
không tin trực tiếp header IP từ khách.

## Bảo mật và vận hành

Mật khẩu hash bằng PHP; cookie HttpOnly, SameSite=Lax và Secure khi dùng HTTPS; session
ID đổi khi đăng nhập; CSRF cho thao tác ghi; xác thực quản trị cho lưu/upload/danh sách;
validation lược đồ, ngày, URL và giới hạn dung lượng; escape nội dung khách gửi;
honeypot và giới hạn tốc độ; CSV tránh công thức từ nội dung khách nhập. Có CSP giới hạn
nguồn script, nhưng Tailwind CDN cần inline script/style: đây không phải CSP không inline.
Chỉ dùng URL HTTPS tin cậy cho ảnh/nhạc ngoài; URL ngoài không được máy chủ tải hộ.

Đổi/quên mật khẩu: qua tài khoản hosting được ủy quyền, sao lưu rồi xóa
`data/credentials.php`, chạy lại `php setup.php`. Xóa session cũ trong `data/sessions/`
để buộc đăng nhập lại. Nếu dùng biến môi trường, thay hash ở môi trường hosting.
Không tạo trang reset mật khẩu công khai.

## Ảnh, nhạc và dịch vụ bên ngoài

Thiết kế được triển khai mới theo mẫu bạn cung cấp:
https://chungdoi.com/i/trongtuan-tuyettrinh?s=2

Ảnh và tệp nhạc mẫu được lấy từ tài nguyên công khai của đúng thiệp tham chiếu để bạn
kiểm tra thiết kế. Việc truy cập công khai không cấp quyền tái xuất bản. Bạn cần có
quyền dùng các tài nguyên này, hoặc thay bằng ảnh/nhạc do bạn sở hữu trước khi phát hành.
Nhạc mẫu: `https://cdn.chungdoi.com/music/yt-4f49acf5-24a.mp3`.
Ảnh bìa: `https://cdn.chungdoi.com/uploads/34e4317b-4f63-4fa5-a0eb-0a6405b806a9.jpg`.
Ảnh được tối ưu và lưu cục bộ; website không phụ thuộc API hay mã ứng dụng Chung Đôi.
Không có tài khoản ngân hàng nào được tự sao chép từ mẫu. Thương hiệu “Chung Đôi Studio”
trong bộ demo có thể đổi ở `admin.php`, `script.js`, `index.php` và favicon.

Tailwind tải qua CDN theo yêu cầu; CSS cốt lõi có sẵn nên bố cục vẫn hoạt động nếu CDN
không tải. Với chính sách production không dùng runtime CDN, build Tailwind ra CSS
tĩnh rồi gỡ script CDN. Google Fonts cần Internet, có serif/sans-serif dự phòng. Google
Maps nhúng không cần API key, dùng địa chỉ, và là dịch vụ bên ngoài. Hãy thông báo cho
khách về việc dùng dịch vụ bên ngoài theo chính sách riêng của website.

## Kiểm tra

```powershell
Get-ChildItem -Filter *.php | ForEach-Object { D:\xampp\php\php.exe -l $_.FullName }
node --check script.js
python tests/run-tests.py
```

Bộ kiểm tra tự mở development server và dùng dữ liệu tạm, không cần chỉnh dữ liệu thật. Kiểm tra thêm trên
hosting thật: HTTPS, đăng nhập, quyền thư mục, tải ảnh/nhạc, phát nhạc sau tương tác,
RSVP, duyệt lời chúc và trạng thái 403 của `/data/`. Chưa có kiểm thử tải lớn hoặc audit
bảo mật độc lập. Đây là bộ mã hoàn chỉnh để tự host; cần hoàn tất cấu hình hosting và
nội dung thật trước khi nhận khách.
