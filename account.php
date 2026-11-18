<?php
/** Credential writes are serialized; first-run registration is allowed exactly once. */
function save_admin_password(string $password,string $confirmation,string $current='',bool $firstRun=false): void {
 if(getenv('WEDDING_ADMIN_PASSWORD_HASH'))throw new InvalidArgumentException('Mật khẩu đang được cấu hình bằng biến môi trường của hosting.');
 if(strlen($password)<12||strlen($password)>72)throw new InvalidArgumentException('Mật khẩu cần từ 12 đến 72 byte.');
 if($password!==$confirmation)throw new InvalidArgumentException('Hai mật khẩu mới không khớp.');
 $lock=fopen(DATA_DIR.'/credentials.lock.php','c+');
 if(!$lock||!flock($lock,LOCK_EX))throw new RuntimeException('Không thể khóa tệp thông tin quản trị.');
 try{
  $existing=password_hash_config();
  if($firstRun){if($existing!=='')throw new InvalidArgumentException('Quản trị đã được tạo. Vui lòng đăng nhập.');}
  elseif($existing!==''&&!password_verify($current,$existing))throw new InvalidArgumentException('Mật khẩu hiện tại không đúng.');
  $hash=password_hash($password,PASSWORD_DEFAULT);$tmp=tempnam(DATA_DIR,'credential-');
  if(!$tmp||file_put_contents($tmp,'<?php return '.var_export($hash,true).';')===false)throw new RuntimeException('Không thể lưu mật khẩu. Kiểm tra quyền ghi thư mục data.');
  chmod($tmp,0600);
  if(!rename($tmp,DATA_DIR.'/credentials.php')){@unlink($tmp);throw new RuntimeException('Không thể thay thế tệp thông tin quản trị.');}
  if(function_exists('opcache_invalidate'))opcache_invalidate(DATA_DIR.'/credentials.php',true);
  session_regenerate_id(true);$_SESSION['admin']=true;$_SESSION['authHash']=hash('sha256',$hash);
 }finally{flock($lock,LOCK_UN);fclose($lock);}
}
