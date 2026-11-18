<?php
require __DIR__.'/bootstrap.php';
require __DIR__.'/social.php';
try{$s=state();$cardId=card_id();$guest=guest_for($s);}catch(InvalidArgumentException $ex){http_response_code(404);exit('Liên kết thiệp hoặc khách mời không tồn tại.');}
$c=$s['config'];$preview=isset($_GET['preview'])&&authenticated();$wishes=public_wishes($s);$social=social_data($c,$cardId);
header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');if($guest)header('X-Robots-Tag: noindex, nofollow');
?><!DOCTYPE html>
<html lang="vi" prefix="og: https://ogp.me/ns#"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="<?=e($c['theme']['color'])?>"><meta name="csrf-token" content="<?=e(token())?>">
<title><?=e($social['title'])?> — Thiệp mời</title>
<meta name="description" content="<?=e($social['description'])?>">
<link rel="canonical" href="<?=e($social['canonical'])?>">
<meta property="og:type" content="website"><meta property="og:locale" content="vi_VN">
<meta property="og:site_name" content="Thiệp cưới"><meta property="og:title" content="<?=e($social['title'])?>">
<meta property="og:description" content="<?=e($social['description'])?>"><meta property="og:url" content="<?=e($social['canonical'])?>">
<meta property="og:image" content="<?=e($social['image'])?>">
<?php if(str_starts_with($social['image'],'https://')): ?><meta property="og:image:secure_url" content="<?=e($social['image'])?>"><?php endif; ?>
<?php if($social['width']): ?><meta property="og:image:width" content="<?=$social['width']?>"><meta property="og:image:height" content="<?=$social['height']?>"><?php endif; ?>
<?php if($social['mime']): ?><meta property="og:image:type" content="<?=e($social['mime'])?>"><?php endif; ?>
<meta property="og:image:alt" content="<?=e('Ảnh cưới '.$social['names'])?>">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?=e($social['title'])?>">
<meta name="twitter:description" content="<?=e($social['description'])?>"><meta name="twitter:image" content="<?=e($social['image'])?>">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20261119"><link rel="stylesheet" href="studio.css?v=20261119"><link rel="stylesheet" href="invitation.css?v=20261119">
</head><body class="invitation-body <?=$preview?'is-preview':''?>">
<noscript>Vui lòng bật JavaScript để xem thiệp và xác nhận tham dự. Trân trọng kính mời bạn đến dự ngày vui của <?=e($c['basic']['groomShort'].' & '.$c['basic']['brideShort'])?>.</noscript>
<main id="invitation"></main><div id="toast" role="status" aria-live="polite"></div>
<script type="application/json" id="boot"><?=json_encode(['mode'=>'invitation','preview'=>$preview,'config'=>$c,'wishes'=>$wishes,'cardId'=>$cardId,'guest'=>$guest?['name'=>$guest['name'],'salutation'=>$guest['salutation']]:null],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?></script>
<script src="script.js?v=20261119" defer></script><script src="invitation.js?v=20261119" defer></script><script src="experience.js?v=20261119" defer></script>
</body></html>
