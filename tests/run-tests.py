"""Start an isolated PHP server, run all suites, and remove only its test data."""
import os, pathlib, shutil, socket, subprocess, tempfile, time, urllib.request
ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP=shutil.which('php') or 'D:/xampp/php/php.exe'
BASE='http://127.0.0.1:8091'
with socket.socket() as probe:
    try:probe.bind(('127.0.0.1',8091))
    except OSError:raise SystemExit('Port 8091 is in use. Stop the QA server before running tests.')
with tempfile.TemporaryDirectory(prefix='test-runtime-',dir=ROOT/'tests') as temp:
    data=pathlib.Path(temp).resolve()
    assert data.is_relative_to((ROOT/'tests').resolve())
    (data/'tmp').mkdir()
    env=os.environ.copy();env['WEDDING_DATA_DIR']=str(data);env['WEDDING_TEST_DATA_DIR']=str(data);env['WEDDING_DEMO_MODE']='1';env.pop('WEDDING_ADMIN_PASSWORD_HASH',None)
    with open(data/'server.log','w') as log:
        process=subprocess.Popen([PHP,'-d','upload_tmp_dir='+str(data/'tmp'),'-d','upload_max_filesize=16M','-d','post_max_size=20M','-S','127.0.0.1:8091','-t',str(ROOT)],cwd=ROOT,env=env,stdout=log,stderr=log,creationflags=getattr(subprocess,'CREATE_NO_WINDOW',0))
        try:
            for _ in range(50):
                if process.poll() is not None:raise RuntimeError('PHP QA server failed to start.')
                try:urllib.request.urlopen(BASE+'/index.php',timeout=.3).close();break
                except OSError:time.sleep(.1)
            else:raise RuntimeError('PHP QA server did not become ready.')
            for suite in ['smoke.py','sharing-smoke.py','language-smoke.py','studio-smoke.py','template-smoke.py','upload-compat-smoke.py']:
                subprocess.run(['python',str(ROOT/'tests'/suite),BASE],cwd=ROOT,env=env,check=True)
        finally:
            process.terminate()
            try:process.wait(timeout=5)
            except subprocess.TimeoutExpired:process.kill();process.wait()
print('PASS: all suites completed; original data and administrator credentials were untouched.')
