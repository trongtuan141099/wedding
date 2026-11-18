"""Real image uploads on a PHP server without fileinfo; use only temporary data."""
import http.cookiejar, json, os, pathlib, re, shutil, socket, subprocess, tempfile, time, urllib.error, urllib.request

ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP=shutil.which('php') or 'D:/xampp/php/php.exe'
with socket.socket() as probe:
    probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
base=f'http://127.0.0.1:{port}'
with tempfile.TemporaryDirectory(prefix='test-runtime-upload-',dir=ROOT/'tests') as temporary:
    data=pathlib.Path(temporary);(data/'tmp').mkdir()
    env=os.environ.copy();env.update(WEDDING_DATA_DIR=str(data),WEDDING_DEMO_MODE='1')
    env.pop('WEDDING_ADMIN_PASSWORD_HASH',None)
    uploaded=None
    with (data/'server.log').open('w') as log:
        process=subprocess.Popen([PHP,'-n','-d','upload_tmp_dir='+str(data/'tmp'),'-d','upload_max_filesize=16M','-d','post_max_size=20M','-S',f'127.0.0.1:{port}','-t',str(ROOT)],cwd=ROOT,env=env,stdout=log,stderr=log,creationflags=getattr(subprocess,'CREATE_NO_WINDOW',0))
        try:
            client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            for _ in range(50):
                try:
                    html=client.open(base+'/admin.php',timeout=.4).read().decode();break
                except OSError:time.sleep(.1)
            else:raise AssertionError('Upload compatibility server unavailable')
            token=re.search(r'name="csrf-token" content="([^"]+)"',html).group(1)
            def upload(content):
                boundary='WeddingUploadCompatibility'
                body=(f'--{boundary}\r\nContent-Disposition: form-data; name="file"; filename="image.png"\r\nContent-Type: image/png\r\n\r\n').encode()+content+f'\r\n--{boundary}--\r\n'.encode()
                req=urllib.request.Request(base+'/api.php?action=upload',data=body,headers={'X-CSRF-Token':token,'Content-Type':'multipart/form-data; boundary='+boundary})
                try:
                    with client.open(req) as response:return response.status,json.load(response)
                except urllib.error.HTTPError as error:return error.code,json.load(error)
            assert subprocess.check_output([PHP,'-n','-r',"echo class_exists('finfo')?'yes':'no';"])==b'no'
            status,result=upload((ROOT/'assets/cover.jpg').read_bytes())
            assert status==200,result
            uploaded=ROOT/result['url'];assert uploaded.is_file() and uploaded.suffix=='.jpg'
            assert client.open(base+'/'+result['url']).status==200
            status,result=upload(b'<?php echo "not an image";')
            assert status==422 and 'fileinfo' in result['error']
            req=urllib.request.Request(base+'/api.php?action=upload',data=b'body rejected by PHP',headers={'X-CSRF-Token':token,'Content-Type':'application/octet-stream'})
            try:client.open(req);raise AssertionError('Missing multipart file accepted')
            except urllib.error.HTTPError as error:
                assert error.code==422 and 'post_max_size' in json.load(error)['error']
        finally:
            process.terminate()
            try:process.wait(timeout=5)
            except subprocess.TimeoutExpired:process.kill();process.wait()
            if uploaded is not None:uploaded.unlink(missing_ok=True)
print('PASS: image upload without fileinfo, spoofed image rejection, actionable missing upload error.')
