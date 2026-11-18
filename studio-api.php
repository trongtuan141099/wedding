<?php
require_once __DIR__.'/account.php';
/** Studio-wide mutations and invitation-list management. All actions require admin. */
function studio_action(string $action,array $in): void {
 if(!in_array($action,['card-create','card-settings','invitee-save','invitee-bulk','invitee-rotate','password'],true))return;
 require_admin();
 if($action==='password'){
  state(function($s){rate_limit($s,'password',15);return $s;});
  save_admin_password((string)($in['newPassword']??''),(string)($in['confirmPassword']??''),(string)($in['currentPassword']??''));
  json_response(['message'=>'Đã đổi mật khẩu. Các phiên đăng nhập khác sẽ hết hiệu lực.']);
 }
 if($action==='card-create'||$action==='card-settings'){
  $title=clean_text($in['title']??'',160);$slug=clean_text($in['slug']??'',70);
  if($title===''||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$slug)||strlen($slug)<3)throw new InvalidArgumentException('Nhập tên thiệp và đường dẫn 3–70 ký tự: chữ thường a–z, số, dấu gạch ngang.');
  $id=$action==='card-create'?'card-'.bin2hex(random_bytes(6)):card_id();
  store_state(function($root)use($action,$in,$title,$slug,$id){
   foreach($root['cardMeta'] as $other=>$meta)if($other!==$id&&$meta['slug']===$slug)throw new InvalidArgumentException('Đường dẫn này đã được dùng. Vui lòng chọn tên khác.');
   if($action==='card-create'){
    if(count($root['cardMeta'])>=100)throw new InvalidArgumentException('Tối đa 100 thiệp.');
    $c=defaults();$template=$in['template']??'ivory';
    if(!in_array($template,['heritage','editorial','ivory'],true))throw new InvalidArgumentException('Mẫu không hợp lệ.');
    if(($in['duplicate']??false)===true){$source=card_id($root);$c=$source==='main'?$root['config']:$root['cards'][$source]['config'];}
    else{$c['basic']['groom']='Tên chú rể';$c['basic']['bride']='Tên cô dâu';$c['basic']['groomShort']='Chú Rể';$c['basic']['brideShort']='Cô Dâu';foreach($c['family'] as $k=>&$v)if(str_contains($k,'Father')||str_contains($k,'Mother')||str_contains($k,'Address'))$v='';unset($v);}
    $c=upgrade_config($c);$c['theme']['template']=$template;$c['gallery']['layout']='3d';
    if($template==='ivory'){$c['theme']['color']='#736044';$c['theme']['paper']='#fffcf5';}
    if($template==='editorial'){$c['theme']['color']='#482c35';$c['theme']['paper']='#fbf4ee';}
    $root['cards'][$id]=['config'=>$c,'revision'=>0,'responses'=>[],'wishes'=>[],'invitees'=>[]];
   }
   $root['cardMeta'][$id]=['title'=>$title,'slug'=>$slug];return $root;
  });json_response(['id'=>$id,'title'=>$title,'slug'=>$slug,'message'=>'Đã lưu thông tin thiệp.']);
 }
 if($action==='invitee-save'||$action==='invitee-bulk'){
  $rows=$action==='invitee-bulk'?($in['guests']??[]):[$in];
  if(!is_array($rows)||count($rows)>100||!$rows)throw new InvalidArgumentException('Mỗi lần thêm 1–100 khách.');
  $result=[];state(function($s)use($rows,&$result){
   foreach($rows as $row){
    if(!is_array($row))throw new InvalidArgumentException('Khách mời không hợp lệ.');
    $name=clean_text($row['name']??'',160);$salutation=clean_text($row['salutation']??'Bạn',100);
    if($name==='')throw new InvalidArgumentException('Vui lòng nhập tên khách mời.');
    $index=null;if(!empty($row['id'])){foreach($s['invitees'] as $i=>$g)if($g['id']===$row['id'])$index=$i;if($index===null)throw new InvalidArgumentException('Không tìm thấy khách mời.');}
    if($index===null){if(count($s['invitees'])>=2000)throw new InvalidArgumentException('Tối đa 2.000 khách mỗi thiệp.');$guest=['id'=>bin2hex(random_bytes(8)),'token'=>bin2hex(random_bytes(18)),'name'=>$name,'salutation'=>$salutation];$s['invitees'][]=$guest;}
    else{$s['invitees'][$index]['name']=$name;$s['invitees'][$index]['salutation']=$salutation;$guest=$s['invitees'][$index];}
    $result[]=$guest;
   }return $s;
  });json_response(['guests'=>$result,'message'=>'Đã lưu khách mời.']);
 }
 if($action==='invitee-rotate'){
  state(function($s)use($in){$found=false;foreach($s['invitees'] as &$g)if($g['id']===($in['id']??'')){$g['token']=bin2hex(random_bytes(18));$found=true;}if(!$found)throw new InvalidArgumentException('Không tìm thấy khách mời.');return $s;});json_response(['message'=>'Đã tạo link mới; link cũ hết hiệu lực.']);
 }
}
