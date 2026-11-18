"""Isolated HTTP tests for browser onboarding, motion settings and crawler metadata."""
import http.cookiejar, io, json, os, pathlib, re, secrets, sys, urllib.error, urllib.parse, urllib.request
from html import unescape
from PIL import Image
ROOT=pathlib.Path(__file__).resolve().parents[1]
DATA=pathlib.Path(os.environ['WEDDING_TEST_DATA_DIR']).resolve()
assert DATA.is_relative_to((ROOT/'tests').resolve())
BASE=sys.argv[1]
credentials=DATA/'credentials.php';state=DATA/'state.php'
assert not credentials.exists(), 'Run this suite before tests that create an administrator.'
previous=state.read_bytes() if state.exists() else None
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
page=client.open(BASE+'/admin.php?initialize=1').read().decode()
assert 'Tạo mật khẩu & mở quản trị' in page
token=re.search(r'name="csrf-token" content="([^"]+)"',page).group(1)
password=secrets.token_hex(16)
def post_form(data,csrf=token):
    data={'csrf':csrf,**data}
    return client.open(urllib.request.Request(BASE+'/admin.php?initialize=1',data=urllib.parse.urlencode(data).encode())).read().decode()
def call(action,data=None):
    req=urllib.request.Request(BASE+'/api.php?action='+action,data=None if data is None else json.dumps(data).encode(),headers={'Content-Type':'application/json','X-CSRF-Token':token})
    try:
        with client.open(req) as r:return r.status,json.loads(r.read())
    except urllib.error.HTTPError as e:return e.code,json.loads(e.read())
def tags(html):
    return {key:unescape(value) for key,value in re.findall(r'<meta (?:property|name)="([^"]+)" content="([^"]*)"',html)}
try:
    try:post_form({'initialize':'1','newPassword':password,'confirmPassword':password},'invalid');raise AssertionError('CSRF accepted')
    except urllib.error.HTTPError as e:assert e.code==403
    invalid=post_form({'initialize':'1','newPassword':'short','confirmPassword':'short'})
    assert 'Mật khẩu cần từ 12 đến 72 byte' in invalid and not credentials.exists()
    good=post_form({'initialize':'1','newPassword':password,'confirmPassword':password})
    assert credentials.exists() and 'form-sections' in good
    assert call('catalog')[0]==200
    # Initialization cannot replace the password after the account exists.
    again=post_form({'initialize':'1','newPassword':'x'*20,'confirmPassword':'x'*20})
    assert call('password',{'currentPassword':password,'newPassword':password+'n','confirmPassword':password+'n'})[0]==200
    _,s=call('config');c=s['config']
    c['experience'].update(scrollSpeed=5,albumInterval=2.5,albumTransition=.6)
    c['sharing'].update(title='Minh & An — Ngày chung đôi',description='Trân trọng kính mời dự lễ cưới.',image='assets/photo-1.jpg',baseUrl=BASE)
    assert call('save',{'config':c,'revision':s['revision']})[0]==200
    _,saved=call('config')
    assert saved['config']['experience']['albumInterval']==2.5
    invalid=json.loads(json.dumps(c));invalid['sharing']['baseUrl']='https://user@example.com'
    assert call('validate',{'config':invalid})[0]==422
    html=client.open(urllib.request.Request(BASE+'/index.php',headers={'User-Agent':'facebookexternalhit/1.1'})).read().decode()
    meta=tags(html)
    assert meta['og:title']==c['sharing']['title'] and meta['og:description']==c['sharing']['description']
    assert meta['og:image'].startswith(BASE+'/social-image.php?i=')
    assert meta['og:image:width']=='1200' and meta['og:image:height']=='630'
    assert meta['twitter:card']=='summary_large_image'
    with client.open(meta['og:image']) as r:
        image=r.read();assert r.headers.get_content_type()=='image/jpeg'
    with Image.open(io.BytesIO(image)) as im:assert im.size==(1200,630)
    # Private guest labels and tokens are not put in social metadata.
    _,guest=call('invitee-save',{'name':'PRIVATE GUEST','salutation':'Bạn'})
    token_guest=guest['guests'][0]['token']
    personal=client.open(BASE+'/index.php?guest='+token_guest).read().decode()
    assert 'PRIVATE GUEST' not in personal.split('</head>')[0] and token_guest not in personal.split('</head>')[0]
    # External image URLs are referenced directly; the server does not fetch them.
    c['sharing']['image']='https://example.invalid/social-test.jpg'
    assert call('save',{'config':c,'revision':saved['revision']})[0]==200
    remote=tags(client.open(BASE+'/index.php').read().decode())
    assert remote['og:image']=='https://example.invalid/social-test.jpg'
    print('PASS: web first-run registration, CSRF, one-time credential creation, password change, motion persistence, safe public URL validation, server-rendered OG metadata, 1200x630 JPEG, private guest exclusion and external-image fallback.')
finally:
    credentials.unlink(missing_ok=True)
    if previous is None:state.unlink(missing_ok=True)
    else:state.write_bytes(previous)
