<?php
/** Server-rendered Open Graph data: crawlers never need JavaScript or a guest token. */
function social_limit(string $text,int $limit): string {
 $chars=preg_split('//u',$text,-1,PREG_SPLIT_NO_EMPTY);return $chars?implode('',array_slice($chars,0,$limit)):$text;
}
function social_base(array $config): string {
 if(!empty($config['sharing']['baseUrl']))return rtrim($config['sharing']['baseUrl'],'/');
 $host=$_SERVER['HTTP_HOST']??'localhost';
 if(!preg_match('/^(?:[a-z0-9.-]+|\[[a-f0-9:]+\])(?::[0-9]{1,5})?$/iD',$host))$host='localhost';
 $secure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['REQUEST_SCHEME']??'')==='https';
 $path=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/index.php'));
 return ($secure?'https':'http').'://'.$host.($path==='/'||$path==='.'?'':rtrim($path,'/'));
}
function social_absolute(string $path,array $config): string {return preg_match('~^https://~i',$path)?$path:social_base($config).'/'.ltrim($path,'/');}
function social_local_image(string $src): ?array {
 if(!preg_match('~^(assets|uploads)/[a-zA-Z0-9_.-]+$~D',$src))return null;
 $path=realpath(__DIR__.'/'.$src);$root=realpath(__DIR__);
 if(!$path||!$root||!str_starts_with(str_replace('\\','/',strtolower($path)),str_replace('\\','/',strtolower($root)).'/'))return null;
 $size=@getimagesize($path);
 if(!$size||!in_array($size['mime'],['image/jpeg','image/png','image/webp'],true))return null;
 return ['path'=>$path,'width'=>$size[0],'height'=>$size[1],'mime'=>$size['mime']];
}
function social_source(array $config): array {
 $candidates=[$config['sharing']['image']??'',$config['gallery']['items'][0]??'',$config['hero']['image']??'','assets/cover.jpg'];
 foreach($candidates as $src){if(!$src)continue;$local=social_local_image($src);if($local)return ['src'=>$src,'local'=>$local];if(filter_var($src,FILTER_VALIDATE_URL)&&parse_url($src,PHP_URL_SCHEME)==='https')return ['src'=>$src,'local'=>null];}
 return ['src'=>'assets/cover.jpg','local'=>null];
}
function social_can_render(array $source): bool {
 $local=$source['local'];if(!$local||$local['width']*$local['height']>12000000)return false;
 $decoder=['image/jpeg'=>'imagecreatefromjpeg','image/png'=>'imagecreatefrompng','image/webp'=>'imagecreatefromwebp'][$local['mime']];
 return function_exists('imagecreatetruecolor')&&function_exists('imagejpeg')&&function_exists('imagettftext')&&function_exists('imagettfbbox')&&function_exists($decoder)&&is_readable(__DIR__.'/assets/fonts/PlayfairDisplay.ttf');
}
function social_data(array $config,string $id): array {
 $config=localized_config($config);$jp=wedding_language()==='jp';
 $b=$config['basic'];$order=$b['order']==='bride'?['bride','groom']:['groom','bride'];
 $names=implode(' ♥ ',array_map(fn($side)=>$b[$side.'Short'],$order));
 $title=social_limit($config['sharing']['title']?:$names,160);$r=$config['reception'];$date='';
 if($r['date']){$dt=DateTimeImmutable::createFromFormat('!Y-m-d',$r['date']);if($dt){$week=$jp?['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日']:['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];$date=$jp?$dt->format('Y年n月j日').'（'.$week[(int)$dt->format('w')].'）':$week[(int)$dt->format('w')].', '.$dt->format('d/m/Y');}}
 $details=array_filter([$date,$r['time'],$r['venue'],$r['address']]);
 $description=social_limit($config['sharing']['description']?:($jp?str_replace(' ♥ ',' & ',$names).'の'.$r['type'].'にご招待いたします。':'Thân mời quý khách đến dự '.mb_strtolower_fallback($r['type']).' của '.str_replace(' ♥ ',' & ',$names)).($details?' | '.implode(' | ',$details):''),500);
 $root=store_state();$slug=$root['cardMeta'][$id]['slug'];$source=social_source($config);
 $version=substr(hash('sha256',json_encode(['social-v4-language',wedding_language(),$names,$title,$description,$r['date'],$source['src'],$config['sharing']['imagePosition'],$source['local']?filemtime($source['local']['path']):0],JSON_UNESCAPED_UNICODE)),0,16);
 $render=social_can_render($source);
 return ['title'=>$title,'names'=>$names,'description'=>$description,'date'=>$date,'canonical'=>social_absolute('index.php?i='.rawurlencode($slug).($jp?'&lang=jp':''),$config),
  'image'=>$render?social_absolute('social-image.php?i='.rawurlencode($slug).($jp?'&lang=jp':'').'&v='.$version,$config):social_absolute($source['src'],$config),
  'width'=>$render?1200:($source['local']['width']??0),'height'=>$render?630:($source['local']['height']??0),'mime'=>$render?'image/jpeg':($source['local']['mime']??''),'version'=>$version,'source'=>$source];
}
function mb_strtolower_fallback(string $value): string {return function_exists('mb_strtolower')?mb_strtolower($value,'UTF-8'):$value;}
