<?php
/** Explicit deployment step: update public content while preserving production guest data. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/bootstrap.php';
$template=initial_state();
store_state(function($root)use($template){
 // Check every slug before writing; a conflict must never break existing guest links.
 foreach($template['cardMeta'] as $id=>$meta){
  if(isset($root['cardMeta'][$id]))continue;
  foreach($root['cardMeta'] as $existing)if($existing['slug']===$meta['slug'])throw new RuntimeException('Template slug conflicts with an existing invitation. No changes saved.');
 }
 $file=DATA_DIR.'/state.php';
 if(is_file($file)){
  $dir=DATA_DIR.'/backups';if(!is_dir($dir)&&!mkdir($dir,0700))throw new RuntimeException('Cannot create backup directory.');
  $backup=$dir.'/before-template-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.php';
  if(!copy($file,$backup))throw new RuntimeException('Backup failed. No changes saved.');chmod($backup,0600);
 }
 foreach($template['cardMeta'] as $id=>$meta){
  $config=validate_config($id==='main'?$template['config']:$template['cards'][$id]['config']);
  if($id==='main'){$root['config']=$config;$root['revision']++;}
  elseif(isset($root['cards'][$id])){$root['cards'][$id]['config']=$config;$root['cards'][$id]['revision']++;}
  else{$root['cards'][$id]=$template['cards'][$id];}
  if(isset($root['cardMeta'][$id]))$root['cardMeta'][$id]['title']=$meta['title'];
  else $root['cardMeta'][$id]=$meta;
 }
 return $root;
});
echo "Public invitation content synchronized. Existing slugs, guests, RSVP, wishes and passwords preserved.\n";
