<?php
/** Public language is selected per URL; stored Vietnamese content remains untouched. */
function wedding_language(): string {return ($_GET['lang']??'vn')==='jp'?'jp':'vn';}
function localized_config(array $config): array {
 $config=upgrade_config($config);if(wedding_language()!=='jp')return $config;
 $jp=$config['japanese'];
 foreach(['groom','bride','groomShort','brideShort'] as $key)if($jp[$key]!=='')$config['basic'][$key]=$jp[$key];
 $config['basic']['greeting']=$jp['greeting'];$config['hero']['caption']=$jp['heroCaption'];
 foreach(array_keys($config['wording']) as $key)$config['wording'][$key]=$jp[$key];
 $config['announcement']['text']=$jp['announcement'];$config['ceremonies']['title']=$jp['ceremoniesTitle'];
 $config['reception']['type']=$jp['receptionType'];$config['reception']['title']=$jp['receptionTitle'];
 foreach(['venue','address'] as $key)if($jp[$key]!=='')$config['reception'][$key]=$jp[$key];
 foreach(['groom','bride'] as $side){
  $config['family'][$side.'Title']='ご両親';
  if($jp[$side.'Address']!=='')$config['family'][$side.'Address']=$jp[$side.'Address'];
 }
 $ranks=['Thứ Nam'=>'次男','Trưởng Nam'=>'長男','Út Nam'=>'末っ子','Thứ Nữ'=>'次女','Trưởng Nữ'=>'長女','Út Nữ'=>'末っ子'];
 foreach(['groomRank','brideRank'] as $key)$config['basic'][$key]=$ranks[$config['basic'][$key]]??$config['basic'][$key];
 $config['rsvp']['description']=$jp['rsvpDescription'];$config['dresscode']['description']=$jp['dresscodeDescription'];
 $config['guestbook']['title']=$jp['guestbookTitle'];
 $known=['Đón khách'=>'受付','Lễ thành hôn'=>'挙式','Khai tiệc'=>'開宴','Nâng ly chúc mừng hạnh phúc'=>'ふたりの幸せを祝って乾杯','Lưu giữ những khoảnh khắc cùng nhau'=>'大切なひとときをご一緒に','Lời hẹn ước cho một hành trình mới'=>'新しい人生への誓い','Hộp quà chú rể'=>'新郎へのお祝い','Hộp quà cô dâu'=>'新婦へのお祝い'];
 foreach(['timeline','ceremonies','banks'] as $section)foreach($config[$section]['items'] as &$item){
  foreach(['title','description','venue','address','label'] as $key)if(array_key_exists($key,$item))$item[$key]=($item[$key.'Jp']??'')!==''?$item[$key.'Jp']:($known[$item[$key]]??$item[$key]);
 }unset($item);
 $config['sharing']['title']=$jp['sharingTitle'];$config['sharing']['description']=$jp['sharingDescription'];
 return $config;
}
function localized_response(array $response): array {
 if(wedding_language()!=='jp')return $response;
 $messages=['Số người đón phải là số nguyên lớn hơn hoặc bằng 1.'=>'送迎人数は1以上の整数で入力してください。','Điền địa chỉ đón và số điện thoại liên lạc hợp lệ.'=>'お迎え場所と有効な電話番号を入力してください。','Cảm ơn lời chúc của bạn! Lời chúc sẽ hiển thị sau khi được duyệt.'=>'温かいメッセージをありがとうございます。承認後に表示されます。','Cảm ơn bạn đã xác nhận. Hẹn gặp bạn trong ngày vui!'=>'ご回答ありがとうございます。当日お会いできることを楽しみにしています。','Phiên đã hết hạn. Vui lòng tải lại trang.'=>'セッションの有効期限が切れました。ページを再読み込みしてください。','Bạn thao tác quá nhanh. Vui lòng thử lại sau một giờ.'=>'送信回数の上限に達しました。1時間後にもう一度お試しください。','Mục này đã đóng.'=>'現在、受付を終了しています。','Điền địa chỉ đón, số điện thoại hợp lệ và số người đón không vượt số người tham dự.'=>'お迎え場所と有効な電話番号を入力してください。送迎人数は出席人数以下にしてください。'];
 foreach(['message','error'] as $key)if(isset($response[$key]))$response[$key]=$messages[$response[$key]]??($key==='error'?'送信できませんでした。入力内容を確認して、もう一度お試しください。':$response[$key]);
 return $response;
}
