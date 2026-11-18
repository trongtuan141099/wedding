<?php
require __DIR__.'/bootstrap.php';
require __DIR__.'/social.php';
try{
 $s=state();$id=card_id();$meta=social_data($s['config'],$id);$source=$meta['source'];
 if(!social_can_render($source)){header('Location: '.social_absolute($source['src'],$s['config']),true,302);exit;}
 $etag='"'.$meta['version'].'"';header('Cache-Control: public, max-age=86400');header('ETag: '.$etag);
 if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);exit;}
 $local=$source['local'];$decoder=['image/jpeg'=>'imagecreatefromjpeg','image/png'=>'imagecreatefrompng','image/webp'=>'imagecreatefromwebp'][$local['mime']];
 $photo=$decoder($local['path']);$canvas=imagecreatetruecolor(1200,630);
 if(!$photo||!$canvas)throw new RuntimeException('Cannot render share image');
 $sw=$local['width'];$sh=$local['height'];$ratio=1200/630;
 if($sw/$sh>$ratio){$cropH=$sh;$cropW=(int)round($sh*$ratio);$x=(int)(($sw-$cropW)/2);$y=0;}
 else{$cropW=$sw;$cropH=(int)round($sw/$ratio);$x=0;$y=(int)(($sh-$cropH)*$s['config']['sharing']['imagePosition']/100);}
 imagecopyresampled($canvas,$photo,0,0,$x,$y,1200,630,$cropW,$cropH);imagedestroy($photo);
 imagealphablending($canvas,true);
 for($y=140;$y<630;$y++){$opacity=(int)round(112*(1-($y-140)/490));$color=imagecolorallocatealpha($canvas,8,19,11,max(15,min(127,$opacity)));imageline($canvas,0,$y,1199,$y,$color);}
 $font=__DIR__.'/assets/fonts/PlayfairDisplay.ttf';$white=imagecolorallocate($canvas,255,252,241);$cream=imagecolorallocate($canvas,224,232,210);
 $title=social_limit($meta['names'],100);$size=47;
 while($size>23){$box=imagettfbbox($size,0,$font,$title);if($box[2]-$box[0]<=1060)break;$size--;}
 $box=imagettfbbox($size,0,$font,$title);$left=(int)((1200-($box[2]-$box[0]))/2);
 imagettftext($canvas,$size,0,$left,485,$white,$font,$title);
 $date=$meta['date'];$box=imagettfbbox(24,0,$font,$date);imagettftext($canvas,24,0,(int)((1200-($box[2]-$box[0]))/2),538,$cream,$font,$date);
 $line=imagecolorallocatealpha($canvas,239,238,210,65);imageline($canvas,500,566,700,566,$line);
 header('Content-Type: image/jpeg');header('Content-Disposition: inline; filename="wedding-preview.jpg"');
 imagejpeg($canvas,null,90);imagedestroy($canvas);
}catch(InvalidArgumentException $ex){http_response_code(404);exit;}
catch(Throwable $ex){error_log((string)$ex);if(isset($source)){header('Location: '.social_absolute($source['src'],$s['config']),true,302);exit;}http_response_code(500);}
