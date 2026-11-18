"""HTTP integration checks against a local PHP development server.
Run: python tests/smoke.py http://127.0.0.1:8088
Uses disposable submissions and restores the exact prior state file at the end.
Never run this against a production website.
"""
import copy, http.cookiejar, json, os, pathlib, re, secrets, shutil, subprocess, sys, urllib.error, urllib.parse, urllib.request

BASE = (sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8088').rstrip('/')
if not re.fullmatch(r'http://(?:127\.0\.0\.1|localhost):\d+', BASE):
    raise SystemExit('Only a localhost development server is supported.')
ROOT = pathlib.Path(__file__).resolve().parents[1]
if not os.environ.get('WEDDING_TEST_DATA_DIR'):
    raise SystemExit('Use python tests/run-tests.py to run with isolated disposable storage.')
DATA = pathlib.Path(os.environ['WEDDING_TEST_DATA_DIR']).resolve()
assert DATA.is_relative_to((ROOT / 'tests').resolve())
STATE = DATA / 'state.php'
assert STATE.resolve().parent == DATA
previous = STATE.read_bytes() if STATE.exists() else None
credentials = DATA / 'credentials.php'
temporary_credentials = False
uploaded = None
cookies = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookies))
page = client.open(BASE + '/admin.php').read().decode()
token = re.search(r'name="csrf-token" content="([^"]+)"', page).group(1)

def request(action, payload=None, csrf=token):
    data = None if payload is None else json.dumps(payload).encode()
    req = urllib.request.Request(BASE + '/api.php?action=' + action, data=data,
        headers={'Content-Type': 'application/json', 'X-CSRF-Token': csrf})
    try:
        with client.open(req) as response:
            return response.status, json.loads(response.read())
    except urllib.error.HTTPError as error:
        return error.code, json.loads(error.read())

try:
    assert 'Warning' not in page and 'form-sections' in page
    code, initial = request('config')
    assert code == 200
    c = initial['config']
    revision = initial['revision']
    assert request('save', {'config': c, 'revision': revision}, csrf='invalid')[0] == 403
    assert request('validate', {'config': c})[0] == 200
    invalid = copy.deepcopy(c)
    invalid['hero']['image'] = 'javascript:alert(1)'
    assert request('validate', {'config': invalid})[0] == 422
    invalid = copy.deepcopy(c)
    invalid['reception']['date'] = '2027-02-30'
    assert request('validate', {'config': invalid})[0] == 422
    c['rsvp']['enabled'] = True
    c['guestbook']['enabled'] = True
    code, saved = request('save', {'config': c, 'revision': revision})
    assert code == 200, saved
    assert saved['revision'] == revision + 1
    assert request('save', {'config': c, 'revision': revision})[0] == 409
    assert request('rsvp', {'name': 'Integration test guest', 'attending': True, 'count': 2, 'message': 'Disposable test'})[0] == 200
    assert request('rsvp', {'name': 'Invalid guest', 'attending': True, 'count': 999})[0] == 422
    assert request('wish', {'name': 'Integration test', 'message': '<script>test</script> Congratulations!'})[0] == 200
    _, guests = request('guests')
    wish = guests['wishes'][-1]
    assert not wish['approved']
    _, public = request('config')
    assert not any(w['message'] == wish['message'] for w in public['wishes'])
    assert request('moderate', {'id': wish['id'], 'approved': True})[0] == 200
    _, public = request('config')
    assert any(w['message'] == wish['message'] for w in public['wishes'])
    # Test credential enforcement without putting a credential into the app.
    assert request('rsvp', {'name': 'Bot', 'attending': True, 'count': 1, 'website': 'spam'})[0] == 422
    csv = client.open(BASE + '/api.php?action=export').read().decode('utf-8-sig')
    assert 'Integration test guest' in csv
    # A real multipart upload exercises fileinfo, size validation and move_uploaded_file.
    boundary = 'WeddingSmoke' + secrets.token_hex(12)
    image = (ROOT / 'assets' / 'cover.jpg').read_bytes()
    multipart = ('--' + boundary + '\r\nContent-Disposition: form-data; name="file"; filename="test.jpg"\r\nContent-Type: image/jpeg\r\n\r\n').encode() + image + ('\r\n--' + boundary + '--\r\n').encode()
    req = urllib.request.Request(BASE + '/api.php?action=upload', data=multipart, headers={'X-CSRF-Token': token, 'Content-Type': 'multipart/form-data; boundary=' + boundary})
    uploaded_path = json.loads(client.open(req).read())['url']
    uploaded = ROOT / uploaded_path
    assert uploaded.resolve().parent == (ROOT / 'uploads').resolve()
    assert uploaded.exists()
    assert client.open(BASE + '/' + uploaded_path).status == 200
    # Configure a temporary administrator and verify unauthenticated denial + login.
    if not credentials.exists():
        php = shutil.which('php') or 'D:/xampp/php/php.exe'
        password = secrets.token_hex(20)
        hashed = subprocess.check_output([php, '-r', 'echo password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT);'], input=(password + '\n').encode()).decode()
        credentials.write_text("<?php return '" + hashed + "';\n")
        temporary_credentials = True
        assert request('guests')[0] == 401
        assert request('save', {'config': c, 'revision': saved['revision']})[0] == 401
        login_data = urllib.parse.urlencode({'csrf': token, 'password': password}).encode()
        login = client.open(urllib.request.Request(BASE + '/admin.php', data=login_data)).read().decode()
        assert 'form-sections' in login
        assert request('guests')[0] == 200
        logout_data = urllib.parse.urlencode({'csrf': token, 'logout': '1'}).encode()
        client.open(urllib.request.Request(BASE + '/admin.php', data=logout_data)).read()
        assert request('guests')[0] == 401
    print('PASS: rendering, CSRF, schema/URL/date validation, save, revision conflicts, RSVP bounds, wish moderation, honeypot, CSV, upload, auth/login/logout')
finally:
    if temporary_credentials:
        credentials.unlink()
    if uploaded is not None:
        uploaded.unlink(missing_ok=True)
    if previous is None:
        STATE.unlink(missing_ok=True)
    else:
        STATE.write_bytes(previous)
