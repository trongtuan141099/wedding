<?php
/** Run from SSH/Terminal: php setup.php. Never exposes setup through the web. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$dir=__DIR__.'/data';if(!is_dir($dir))mkdir($dir,0750,true);
if(is_file($dir.'/credentials.php')){fwrite(STDERR,"Admin already configured. Remove data/credentials.php through your hosting account to reset.\n");exit(1);}
$password=bin2hex(random_bytes(12));
file_put_contents($dir.'/credentials.php',"<?php return ".var_export(password_hash($password,PASSWORD_DEFAULT),true).";\n",LOCK_EX);chmod($dir.'/credentials.php',0600);
echo "Admin created. Save this password in a password manager:\n".$password."\nLogin: admin.php\n";
