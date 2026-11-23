<?php
require __DIR__.'/bootstrap.php';
require __DIR__.'/social.php';
try{$s=state();$cardId=card_id();$guest=guest_for($s);}catch(InvalidArgumentException $ex){http_response_code(404);exit('Liên kết thiệp hoặc khách mời không tồn tại.');}
$c=localized_config($s['config']);$lang=wedding_language();$preview=isset($_GET['preview'])&&authenticated();$wishes=public_wishes($s);$social=social_data($c,$cardId);
header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');if($guest)header('X-Robots-Tag: noindex, nofollow');
?><!DOCTYPE html>
<html lang="<?=$lang==='jp'?'ja':'vi'?>" prefix="og: https://ogp.me/ns#"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="<?=e($c['theme']['color'])?>"><meta name="csrf-token" content="<?=e(token())?>">
<title><?=e($social['title'])?> — <?=$lang==='jp'?'結婚式のご招待':'Thiệp mời'?></title>
<meta name="description" content="<?=e($social['description'])?>">
<link rel="canonical" href="<?=e($social['canonical'])?>">
<meta property="og:type" content="website"><meta property="og:locale" content="<?=$lang==='jp'?'ja_JP':'vi_VN'?>">
<meta property="og:site_name" content="<?=$lang==='jp'?'結婚式のご招待':'Thiệp cưới'?>"><meta property="og:title" content="<?=e($social['title'])?>">
<meta property="og:description" content="<?=e($social['description'])?>"><meta property="og:url" content="<?=e($social['canonical'])?>">
<meta property="og:image" content="<?=e($social['image'])?>">
<?php if(str_starts_with($social['image'],'https://')): ?><meta property="og:image:secure_url" content="<?=e($social['image'])?>"><?php endif; ?>
<?php if($social['width']): ?><meta property="og:image:width" content="<?=$social['width']?>"><meta property="og:image:height" content="<?=$social['height']?>"><?php endif; ?>
<?php if($social['mime']): ?><meta property="og:image:type" content="<?=e($social['mime'])?>"><?php endif; ?>
<meta property="og:image:alt" content="<?=e(($lang==='jp'?'ウェディングフォト ':'Ảnh cưới ').$social['names'])?>">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?=e($social['title'])?>">
<meta name="twitter:description" content="<?=e($social['description'])?>"><meta name="twitter:image" content="<?=e($social['image'])?>">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20261123"><link rel="stylesheet" href="studio.css?v=20261123"><link rel="stylesheet" href="invitation.css?v=20261123">
<?php if($lang==='jp'): ?><link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;500;600&display=swap" rel="stylesheet"><?php endif; ?>
</head><body class="invitation-body lang-<?=e($lang)?> <?=$preview?'is-preview':''?>">
<noscript><?=$lang==='jp'?'招待状の閲覧と出欠のご回答にはJavaScriptを有効にしてください。':'Vui lòng bật JavaScript để xem thiệp và xác nhận tham dự.'?></noscript>
<nav class="language-switch" aria-label="<?=$lang==='jp'?'言語':'Ngôn ngữ'?>"><?php foreach(['vn'=>'Tiếng Việt','jp'=>'日本語'] as $code=>$label): $query=[];foreach(['i','card','guest','preview'] as $key)if(isset($_GET[$key])&&is_string($_GET[$key]))$query[$key]=$_GET[$key];$query['lang']=$code; ?><a href="index.php?<?=e(http_build_query($query))?>" data-language="<?=e($code)?>" <?=$lang===$code?'aria-current="true"':''?>><?=e($label)?></a><?php endforeach; ?></nav>
<main id="invitation"></main><div id="toast" role="status" aria-live="polite"></div>
<script type="application/json" id="boot"><?=json_encode(['mode'=>'invitation','language'=>$lang,'preview'=>$preview,'config'=>$c,'wishes'=>$wishes,'cardId'=>$cardId,'guest'=>$guest?['name'=>$guest['name'],'salutation'=>$guest['salutation']]:null],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?></script>
<script src="i18n.js?v=20261123" defer></script><script src="script.js?v=20261123" defer></script><script src="invitation.js?v=20261123" defer></script><script src="experience.js?v=20261123" defer></script>
</body></html>
