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
 $names=explode(' ♥ ',$meta['names'],2);$names=array_map(fn($name)=>social_limit($name,50),$names);$size=47;
 do{$boxes=array_map(fn($name)=>imagettfbbox($size,0,$font,$name),$names);$widths=array_map(fn($box)=>$box[2]-$box[0],$boxes);$total=array_sum($widths)+58;if($total<=1060||$size<=23)break;$size--;}while(true);
 $left=(int)((1200-$total)/2);imagettftext($canvas,$size,0,$left,485,$white,$font,$names[0]);
 // Draw a heart instead of relying on a symbol glyph absent in some serif fonts.
 $cx=$left+$widths[0]+29;$cy=463;
 imagefilledellipse($canvas,$cx-8,$cy-6,23,23,$white);imagefilledellipse($canvas,$cx+8,$cy-6,23,23,$white);
 imagefilledpolygon($canvas,[$cx-19,$cy-2,$cx+19,$cy-2,$cx,$cy+20],3,$white);
 imagettftext($canvas,$size,0,$left+$widths[0]+58,485,$white,$font,$names[1]??'');
 $date=$meta['date'];$box=imagettfbbox(24,0,$font,$date);imagettftext($canvas,24,0,(int)((1200-($box[2]-$box[0]))/2),538,$cream,$font,$date);
 $line=imagecolorallocatealpha($canvas,239,238,210,65);imageline($canvas,500,566,700,566,$line);
 header('Content-Type: image/jpeg');header('Content-Disposition: inline; filename="wedding-preview.jpg"');
 imagejpeg($canvas,null,90);imagedestroy($canvas);
}catch(InvalidArgumentException $ex){http_response_code(404);exit;}
catch(Throwable $ex){error_log((string)$ex);if(isset($source)){header('Location: '.social_absolute($source['src'],$s['config']),true,302);exit;}http_response_code(500);}
