<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('DATA_DIR',getenv('WEDDING_DATA_DIR')?:__DIR__.'/data');
const PREFIX = "<?php exit; ?>\n";
if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR,0750,true)) throw new RuntimeException('Không thể tạo thư mục data.');
if (!is_dir(DATA_DIR.'/sessions') && !mkdir(DATA_DIR.'/sessions',0700,true)) throw new RuntimeException('Không thể tạo thư mục phiên đăng nhập.');
session_save_path(DATA_DIR.'/sessions');
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']);
session_start();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' https: data: blob:; media-src 'self' https: blob:; frame-src 'self' https://www.google.com; connect-src 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
function defaults(): array { return require __DIR__.'/defaults.php'; }
function initial_state(): array {
 $seed=require __DIR__.'/public-template.php';$seed['config']=upgrade_config($seed['config']);
 foreach($seed['cards'] as &$card)$card['config']=upgrade_config($card['config']);unset($card);
 return $seed;
}
/** One lock protects read/modify/write, including concurrent public submissions. */
function store_state(?callable $mutate=null): array {
 $lock=fopen(DATA_DIR.'/state.lock.php','c+'); if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('Storage lock failed');
 try {
  $file=DATA_DIR.'/state.php';
  $s=is_file($file)?json_decode(substr((string)file_get_contents($file),strlen(PREFIX)),true,512,JSON_THROW_ON_ERROR):initial_state();
  $s['cardMeta']??=['main'=>['title'=>'Thiệp cưới của chúng mình','slug'=>'trongtuan-tuyettrinh']];
  $s['cards']??=[];
  $s['invitees']??=[];
  $s['config']=upgrade_config($s['config']);
  if ($mutate) {
   $s=$mutate($s); $tmp=tempnam(DATA_DIR,'state-');
   if (!$tmp || file_put_contents($tmp,PREFIX.json_encode($s,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR))===false) throw new RuntimeException('Storage write failed');
   chmod($tmp,0640); if(!rename($tmp,$file)) { @unlink($tmp); throw new RuntimeException('Storage replace failed'); }
  }
  return $s;
 } finally {flock($lock,LOCK_UN);fclose($lock);}
}
/** Add new settings without replacing existing photos, guests or invitations. */
function upgrade_config(array $config): array {
 foreach(defaults() as $key=>$fields){if(isset($config[$key])&&!is_array($config[$key]))throw new InvalidArgumentException('Mục cấu hình không hợp lệ.');$config[$key]=array_replace($fields,$config[$key]??[]);}
 return $config;
}
function card_id(?array $root=null): string {
 $root??=store_state();
 if(isset($_GET['i'])){foreach($root['cardMeta'] as $id=>$meta)if($meta['slug']===$_GET['i'])return (string)$id;throw new InvalidArgumentException('Không tìm thấy thiệp.');}
 $id=(string)($_GET['card']??'main');
 if(!isset($root['cardMeta'][$id]))throw new InvalidArgumentException('Không tìm thấy thiệp.');return $id;
}
function state(?callable $mutate=null): array {
 $root=store_state();$id=card_id($root);
 $select=function($r)use($id){$s=$id==='main'?$r:$r['cards'][$id];$s['config']=upgrade_config($s['config']);$s['rates']=$r['rates'];$s['invitees']??=[];return $s;};
 if(!$mutate)return $select($root);
 $result=null;
 store_state(function($r)use($id,$select,$mutate,&$result){
  $result=$mutate($select($r));$r['rates']=$result['rates'];
  foreach(['config','revision','responses','wishes','invitees'] as $k){if($id==='main')$r[$k]=$result[$k];else $r['cards'][$id][$k]=$result[$k];}return $r;
 });return $result;
}
function public_wishes(array $s): array {return array_values(array_map(fn($w)=>['name'=>$w['name'],'message'=>$w['message']],array_filter($s['wishes'],fn($w)=>$w['approved'])));}
function guest_for(array $s): ?array {
 $token=(string)($_GET['guest']??'');if($token==='')return null;
 foreach($s['invitees'] as $guest)if(hash_equals($guest['token'],$token))return $guest;
 throw new InvalidArgumentException('Liên kết khách mời không còn hợp lệ.');
}
function public_card_url(string $id): string {$meta=store_state()['cardMeta'][$id];return 'index.php?i='.rawurlencode($meta['slug']);}
function e(string $s): string { return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function json_response(array $v,int $code=200): void {http_response_code($code);header('Content-Type: application/json; charset=utf-8');echo json_encode($v,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);exit;}
function token(): string {return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function csrf(): void {if(!hash_equals(token(),$_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['csrf']??'')) json_response(['error'=>'Phiên đã hết hạn. Vui lòng tải lại trang.'],403);}
function password_hash_config(): string {return getenv('WEDDING_ADMIN_PASSWORD_HASH') ?: (is_file(DATA_DIR.'/credentials.php') ? (string)(require DATA_DIR.'/credentials.php') : '');}
function local_demo(): bool {return getenv('WEDDING_DEMO_MODE')==='1' && PHP_SAPI==='cli-server' && in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true) && password_hash_config()==='';}
function authenticated(): bool {return local_demo() || (($_SESSION['admin']??false)===true && hash_equals(hash('sha256',password_hash_config()),$_SESSION['authHash']??''));}
function require_admin(): void {if(!authenticated()) json_response(['error'=>'Vui lòng đăng nhập.'],401);}
function clean_text(mixed $v,int $max=2000): string {if(!is_string($v)||strlen($v)>$max) throw new InvalidArgumentException('Nội dung không hợp lệ hoặc quá dài.');return trim($v);}
function asset_url(string $v): bool {return $v==='' || preg_match('~^(assets|uploads)/[a-zA-Z0-9_.-]+$~D',$v) || (filter_var($v,FILTER_VALIDATE_URL) && parse_url($v,PHP_URL_SCHEME)==='https');}
/** Keep only the documented schema; enforce list limits, types and URL protocols. */
function validate_config(array $input): array {
 $input=upgrade_config($input);
 $out=[];
 foreach(defaults() as $section=>$fields){
  if(!isset($input[$section])||!is_array($input[$section]))throw new InvalidArgumentException('Thiếu mục '.$section);
  foreach($fields as $key=>$default){
   $v=$input[$section][$key]??null;
   if(is_bool($default)){if(!is_bool($v))throw new InvalidArgumentException('Công tắc không hợp lệ.');}
   elseif(is_int($default)||is_float($default)){if(!is_numeric($v))throw new InvalidArgumentException('Số không hợp lệ.');$v=(float)$v;}
   elseif(is_array($default)){
    if(!is_array($v)||array_values($v)!==$v||count($v)>40)throw new InvalidArgumentException('Danh sách tối đa 40 mục.');
    $v=array_map(function($row)use($section){
     if(in_array($section,['gallery','dresscode'],true))return clean_text($row,1000);
     $keys= match($section){'ceremonies'=>['title','date','time','venue','address','mapUrl'],'timeline'=>['time','title','description'],'banks'=>['label','bank','number','holder','qr'],default=>[]};
     if(!is_array($row))throw new InvalidArgumentException('Mục không hợp lệ.');$clean=[];
     foreach($keys as $k){$clean[$k]=clean_text($row[$k]??'');if(in_array($k,['qr','mapUrl'],true)&&!asset_url($clean[$k]))throw new InvalidArgumentException('Chỉ hỗ trợ URL HTTPS hoặc tệp đã tải lên.');}
     return $clean;
    },$v);
   } else {$v=clean_text($v);}
   if(in_array($key,['image','src','mapUrl'],true)&&!asset_url($v))throw new InvalidArgumentException('Đường dẫn tệp phải là HTTPS hoặc tệp đã tải lên.');
   $out[$section][$key]=$v;
  }
 }
 foreach(['color','paper'] as $k)if(!preg_match('/^#[a-f0-9]{6}$/i',$out['theme'][$k]))throw new InvalidArgumentException('Màu không hợp lệ.');
 foreach($out['dresscode']['colors'] as $c)if(!preg_match('/^#[a-f0-9]{6}$/i',$c))throw new InvalidArgumentException('Màu dress code không hợp lệ.');
 foreach($out['gallery']['items'] as $u)if(!asset_url($u))throw new InvalidArgumentException('Ảnh không hợp lệ.');
 $out['rsvp']['maxGuests']=max(1,min(20,(int)$out['rsvp']['maxGuests']));
 $out['music']['volume']=max(0,min(1,(float)$out['music']['volume']));
 $out['theme']['textScale']=max(1,min(1.4,(float)$out['theme']['textScale']));
 $out['theme']['nameScale']=max(1,min(1.6,(float)$out['theme']['nameScale']));
 $out['experience']['scrollSpeed']=max(5,min(80,(int)$out['experience']['scrollSpeed']));
 $out['experience']['albumInterval']=max(2,min(15,(float)$out['experience']['albumInterval']));
 $out['experience']['albumTransition']=max(.4,min(2,(float)$out['experience']['albumTransition']));
 $out['sharing']['imagePosition']=max(0,min(100,(int)$out['sharing']['imagePosition']));
 if(!asset_url($out['sharing']['image']))throw new InvalidArgumentException('Ảnh chia sẻ cần là HTTPS hoặc tệp đã tải lên.');
 if($out['sharing']['baseUrl']!==''){
  $url=parse_url($out['sharing']['baseUrl']);
  if(!$url||!filter_var($out['sharing']['baseUrl'],FILTER_VALIDATE_URL)||!isset($url['host'])||isset($url['user'])||isset($url['pass'])||isset($url['query'])||isset($url['fragment'])||(($url['scheme']??'')!=='https'&&!(($url['scheme']??'')==='http'&&in_array($url['host'],['localhost','127.0.0.1','[::1]'],true))))throw new InvalidArgumentException('Địa chỉ website phải là URL HTTPS, không chứa query hoặc thông tin đăng nhập.');
  $out['sharing']['baseUrl']=rtrim($out['sharing']['baseUrl'],'/');
 }
 foreach([['theme','template',['heritage','editorial','ivory']],['experience','opening',['curtain','bloom']],['basic','order',['groom','bride']],['gallery','layout',['grid','collage','slider','3d']],['rsvp','mode',['inline','button']],['rsvp','countMode',['guest','limit']],['reception','format',['24','12']]] as [$a,$b,$values])if(!in_array($out[$a][$b],$values,true))throw new InvalidArgumentException('Lựa chọn không hợp lệ.');
 foreach(array_merge([$out['reception']],$out['ceremonies']['items']) as $event){
  if($event['date']!=='' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$event['date'])||!($dt=DateTimeImmutable::createFromFormat('!Y-m-d',$event['date']))||$dt->format('Y-m-d')!==$event['date']))throw new InvalidArgumentException('Ngày không hợp lệ.');
  if($event['time']!==''&&!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',$event['time']))throw new InvalidArgumentException('Giờ không hợp lệ.');
 }
 return $out;
}
function rate_limit(array &$s,string $kind,int $limit=5): void {
 $now=time();$s['rates']=array_filter($s['rates'],fn($r)=>$r['at']>$now-3600);
 $key=hash('sha256',($_SERVER['REMOTE_ADDR']??'').$kind);$r=$s['rates'][$key]??['at'=>$now,'n'=>0];
 if($r['n']>=$limit)throw new InvalidArgumentException('Bạn thao tác quá nhanh. Vui lòng thử lại sau một giờ.');
 $r['n']++;$s['rates'][$key]=$r;
}
