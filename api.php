<?php
require __DIR__.'/bootstrap.php';
require __DIR__.'/studio-api.php';
header('Cache-Control: no-store');
$action=$_GET['action']??'config';
try {
 if($_SERVER['REQUEST_METHOD']==='GET'){
  $s=state();
  if($action==='config')json_response(['config'=>localized_config($s['config']),'revision'=>$s['revision'],'wishes'=>public_wishes($s)]);
  require_admin();
  if($action==='catalog'){ $root=store_state();json_response(['cards'=>$root['cardMeta'],'current'=>card_id($root)]); }
  if($action==='invitees')json_response(['invitees'=>$s['invitees'],'publicUrl'=>public_card_url(card_id())]);
  if($action==='guests')json_response(['responses'=>$s['responses'],'wishes'=>$s['wishes']]);
  if($action==='export'){
   header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="wedding-rsvp.csv"');$f=fopen('php://output','w');fwrite($f,"\xEF\xBB\xBF");fputcsv($f,['Họ tên','Tham dự','Số người','Lời nhắn','Ngày gửi','Yêu cầu xe','Địa chỉ đón','Số người đón','Điện thoại']);
   foreach($s['responses'] as $r)fputcsv($f,array_map(fn($v)=>preg_match('/^[=+@\-\t\r]/',(string)$v)?"'".$v:$v,[$r['name'],$r['attending']?'Có':'Không',$r['count'],$r['message'],$r['createdAt'],!empty($r['transport'])?'Có':'Không',$r['pickupAddress']??'',$r['pickupCount']??0,$r['phone']??'']));exit;
  }
  json_response(['error'=>'Không tìm thấy.'],404);
 }
 if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['error'=>'Phương thức không được hỗ trợ.'],405);
 if($action==='upload' && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH']??0)>0){require_admin();throw new InvalidArgumentException('Máy chủ không nhận được tệp tải lên. Kiểm tra post_max_size, upload_max_filesize và thư mục tạm PHP trong cPanel.');}
 csrf();
 if($action==='upload'){
  require_admin();$f=$_FILES['file']??null;
  if(!$f)throw new InvalidArgumentException('Vui lòng chọn tệp tải lên.');
  $uploadErrors=[UPLOAD_ERR_INI_SIZE=>'Tệp vượt upload_max_filesize của hosting.',UPLOAD_ERR_FORM_SIZE=>'Tệp vượt giới hạn tải lên.',UPLOAD_ERR_PARTIAL=>'Tệp tải lên chưa hoàn tất. Hãy thử lại.',UPLOAD_ERR_NO_FILE=>'Chưa chọn tệp.',UPLOAD_ERR_NO_TMP_DIR=>'Hosting thiếu thư mục tạm PHP. Cấu hình upload_tmp_dir trong cPanel.',UPLOAD_ERR_CANT_WRITE=>'Hosting không ghi được tệp tạm. Kiểm tra dung lượng và quyền ghi thư mục tạm PHP.',UPLOAD_ERR_EXTENSION=>'Một extension PHP trên hosting đã chặn tải tệp.'];
  if($f['error']!==UPLOAD_ERR_OK)throw new InvalidArgumentException($uploadErrors[$f['error']]??'Tải lên thất bại.');
  if($f['size']>15*1024*1024)throw new InvalidArgumentException('Ảnh tối đa 8 MB, nhạc tối đa 15 MB.');
  // Image detection works even when shared hosting disables the fileinfo extension.
  $dim=@getimagesize($f['tmp_name']);
  if($dim){$mime=$dim['mime'];}
  else{
   if(!class_exists('finfo'))throw new InvalidArgumentException('Không đọc được ảnh JPG/PNG/WebP. Nếu tải nhạc, hãy bật extension fileinfo trong cPanel.');
   $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  }
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','audio/mpeg'=>'mp3','audio/ogg'=>'ogg'][$mime]??null;
  if(!$ext)throw new InvalidArgumentException('Chỉ nhận JPG, PNG, WebP, MP3 hoặc OGG.');
  if(str_starts_with($mime,'image/') && ($f['size']>8*1024*1024 || !$dim || $dim[0]*$dim[1]>40000000))throw new InvalidArgumentException('Ảnh tối đa 8 MB và 40 megapixel.');
  if(!is_dir(__DIR__.'/uploads')&&!@mkdir(__DIR__.'/uploads',0755,true))throw new InvalidArgumentException('Không tạo được uploads. Hãy tạo thư mục uploads trong thư mục website, đặt quyền 0755 và đúng chủ sở hữu tài khoản cPanel.');
  if(!is_writable(__DIR__.'/uploads'))throw new InvalidArgumentException('Thư mục uploads chưa có quyền ghi cho PHP. Chạy bash tools/fix-permissions.sh hoặc nhờ hosting sửa quyền/chủ sở hữu của uploads.');
  $path='uploads/'.bin2hex(random_bytes(16)).'.'.$ext;
  if(!@move_uploaded_file($f['tmp_name'],__DIR__.'/'.$path))throw new InvalidArgumentException('Không lưu được tệp vào uploads. Kiểm tra dung lượng còn trống, quota tài khoản và quyền/chủ sở hữu uploads trên hosting.');
  if(!@chmod(__DIR__.'/'.$path,0644))error_log('Uploaded file saved, but could not normalize its permissions.');
  json_response(['url'=>$path]);
 }
 if((int)($_SERVER['CONTENT_LENGTH']??0)>500000)throw new InvalidArgumentException('Dữ liệu quá lớn.');
 $in=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
 if(!is_array($in))throw new InvalidArgumentException('Dữ liệu không hợp lệ.');
 studio_action($action,$in);
 if($action==='validate'){
  require_admin();json_response(['config'=>validate_config($in['config']??[])]);
 }
 if($action==='save'){
  require_admin();$config=validate_config($in['config']??[]);
  $s=state(function($s)use($in,$config){if(($in['revision']??-1)!==$s['revision']){http_response_code(409);throw new InvalidArgumentException('Thiệp đã được cập nhật ở cửa sổ khác. Hãy xuất bản nháp JSON, rồi tải lại trang.');}$s['config']=$config;$s['revision']++;return $s;});
  json_response(['revision'=>$s['revision'],'message'=>'Đã lưu thiệp thành công']);
 }
 if($action==='moderate'){
  require_admin();state(function($s)use($in){foreach($s['wishes'] as &$w)if($w['id']===($in['id']??''))$w['approved']=($in['approved']??false)===true;return $s;});json_response(['ok'=>true]);
 }
 if(in_array($action,['rsvp','wish'],true)){
  if(!empty($in['website']))throw new InvalidArgumentException('Yêu cầu không hợp lệ.');
  $name=clean_text($in['name']??'',160);$message=clean_text($in['message']??'',2000);
  if($name===''||($action==='wish'&&$message===''))throw new InvalidArgumentException('Vui lòng nhập tên và nội dung.');
  state(function($s)use($in,$name,$message,$action){
   if(!$s['config'][$action==='rsvp'?'rsvp':'guestbook']['enabled'])throw new InvalidArgumentException('Mục này đã đóng.');
   rate_limit($s,$action);
   $guest=guest_for($s);
   $entry=['id'=>bin2hex(random_bytes(12)),'name'=>$guest['name']??$name,'guestId'=>$guest['id']??null,'message'=>$message,'createdAt'=>date(DATE_ATOM)];
   if($action==='rsvp'){
    if(!is_bool($in['attending']??null))throw new InvalidArgumentException('Vui lòng chọn xác nhận tham dự.');
    $count=filter_var($in['count']??1,FILTER_VALIDATE_INT);$max=$s['config']['rsvp']['countMode']==='limit'?$s['config']['rsvp']['maxGuests']:20;
    if($count===false||$count<1||$count>$max)throw new InvalidArgumentException('Số khách phải từ 1 đến '.$max.'.');
    if(count($s['responses'])>=10000)throw new InvalidArgumentException('Danh sách đã đầy. Vui lòng liên hệ gia đình.');
    $transport=$in['attending']&&($in['transport']??false)===true;
    $address=$transport?clean_text($in['pickupAddress']??'',1000):'';$phone=$transport?clean_text($in['phone']??'',40):'';
    $pickup=$transport?filter_var($in['pickupCount']??null,FILTER_VALIDATE_INT):0;
    if($transport&&($address===''||!preg_match('/^[+0-9() .-]{8,25}$/D',$phone)||$pickup===false||$pickup<1||$pickup>$count))throw new InvalidArgumentException('Điền địa chỉ đón, số điện thoại hợp lệ và số người đón không vượt số người tham dự.');
    $entry+=['attending'=>$in['attending'],'count'=>$in['attending']?$count:0,'transport'=>$transport,'pickupAddress'=>$address,'pickupCount'=>$pickup,'phone'=>$phone];
    $updated=false;if($guest)foreach($s['responses'] as $i=>$r)if(($r['guestId']??null)===$guest['id']){$entry['id']=$r['id'];$s['responses'][$i]=$entry;$updated=true;break;}
    if(!$updated)$s['responses'][]=$entry;
   }else{if(count($s['wishes'])>=10000)throw new InvalidArgumentException('Sổ lưu bút đã đầy.');$entry['approved']=false;$s['wishes'][]=$entry;}
   return $s;
  });json_response(['message'=>$action==='wish'?'Cảm ơn lời chúc của bạn! Lời chúc sẽ hiển thị sau khi được duyệt.':'Cảm ơn bạn đã xác nhận. Hẹn gặp bạn trong ngày vui!']);
 }
 json_response(['error'=>'Không tìm thấy.'],404);
}catch(InvalidArgumentException|JsonException $ex){json_response(['error'=>$ex->getMessage()],http_response_code()===409?409:422);}
catch(Throwable $ex){error_log((string)$ex);json_response(['error'=>'Không thể xử lý dữ liệu. Kiểm tra quyền ghi và nhật ký máy chủ.'],500);}
