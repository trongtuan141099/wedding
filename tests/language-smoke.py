"""Public language URLs, Japanese overrides, privacy and submissions with isolated data."""
import http.cookiejar, json, os, pathlib, re, sys, urllib.error, urllib.parse, urllib.request
from html import unescape
BASE=sys.argv[1]
DATA=pathlib.Path(os.environ['WEDDING_TEST_DATA_DIR']).resolve()
ROOT=pathlib.Path(__file__).resolve().parents[1]
assert DATA.is_relative_to((ROOT/'tests').resolve())
state=DATA/'state.php';previous=state.read_bytes() if state.exists() else None
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
admin=client.open(BASE+'/admin.php').read().decode()
token=re.search(r'name="csrf-token" content="([^"]+)"',admin).group(1)
def call(action,data=None,extra=''):
    request=urllib.request.Request(BASE+'/api.php?action='+action+extra,data=None if data is None else json.dumps(data).encode(),headers={'Content-Type':'application/json','X-CSRF-Token':token})
    try:
        with client.open(request) as response:return response.status,json.load(response)
    except urllib.error.HTTPError as error:return error.code,json.load(error)
def boot(html):return json.loads(re.search(r'<script[^>]+id="boot"[^>]*>(.*?)</script>',html,re.S).group(1))
def tags(html):return {k:unescape(v) for k,v in re.findall(r'<meta (?:property|name)="([^"]+)" content="([^"]*)"',html)}
try:
    assert 'id="share-language"' in admin
    _,original=call('config');config=original['config']
    config['japanese'].update(groomShort='太郎',brideShort='花子',venue='披露宴会場',rsvpDescription='ぜひご出席ください。',envelopeTitle='大切な皆さまへ',sharingTitle='太郎と花子の結婚式',sharingDescription='結婚披露宴へのご招待')
    config['timeline']['items'][0]['titleJp']='受付開始'
    assert call('save',{'config':config,'revision':original['revision']})[0]==200
    vn=client.open(BASE+'/index.php?lang=vn').read().decode()
    jp=client.open(BASE+'/index.php?lang=jp').read().decode()
    assert '<html lang="vi"' in vn and '<html lang="ja"' in jp
    assert boot(vn)['config']['basic']['groomShort']==config['basic']['groomShort']
    japanese=boot(jp)
    assert japanese['language']=='jp' and japanese['config']['basic']['groomShort']=='太郎'
    assert japanese['config']['wording']['envelopeTitle']=='大切な皆さまへ'
    assert japanese['config']['timeline']['items'][0]['title']=='受付開始'
    assert japanese['config']['reception']['date']==config['reception']['date']
    assert tags(jp)['og:locale']=='ja_JP' and tags(jp)['og:title']=='太郎と花子の結婚式'
    assert 'lang=jp' in tags(jp)['og:url'] and 'lang=jp' in tags(jp)['og:image']
    assert tags(vn)['og:locale']=='vi_VN' and 'Noto+Serif+JP' in jp
    assert boot(client.open(BASE+'/index.php?lang=unknown').read().decode())['language']=='vn'
    _,guest=call('invitee-save',{'name':'PRIVATE JP GUEST','salutation':'Bạn'})
    guest_token=guest['guests'][0]['token']
    personal=client.open(BASE+'/index.php?lang=jp&guest='+guest_token).read().decode()
    assert boot(personal)['guest']['name']=='PRIVATE JP GUEST'
    assert guest_token not in personal.split('</head>')[0] and 'PRIVATE JP GUEST' not in personal.split('</head>')[0]
    assert 'guest='+guest_token in unescape(personal) and 'lang=vn' in unescape(personal)
    status,result=call('rsvp',{'name':'PRIVATE JP GUEST','attending':True,'count':1},'&lang=jp&guest='+guest_token)
    assert status==200 and 'ありがとうございます' in result['message']
    status,result=call('rsvp',{'name':'Guest','attending':'invalid','count':1},'&lang=jp')
    assert status==422 and '入力内容' in result['error']
    _,saved=call('config')
    assert saved['config']['basic']==config['basic'], 'Japanese request overwrote Vietnamese content'
    _,guests=call('guests')
    assert any(row['name']=='PRIVATE JP GUEST' for row in guests['responses'])
    print('PASS: vn/jp URLs, Japanese overrides/metadata, names, calendar date, guest privacy, RSVP language and Vietnamese data preserved.')
finally:
    if previous is None:state.unlink(missing_ok=True)
    else:state.write_bytes(previous)
