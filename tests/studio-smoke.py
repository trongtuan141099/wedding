"""Run against an ISOLATED local demo server configured with WEDDING_DATA_DIR.
python tests/studio-smoke.py http://127.0.0.1:8091
Creates only disposable test cards. Does not edit the main invitation.
"""
import copy, http.cookiejar, json, re, secrets, sys, urllib.error, urllib.parse, urllib.request
BASE=sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8091'
assert re.fullmatch(r'http://127\.0\.0\.1:8091',BASE), 'Use the isolated QA server on port 8091.'
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
page=client.open(BASE+'/admin.php').read().decode()
assert 'form-sections' in page, 'QA server must start without credentials.'
csrf=re.search(r'name="csrf-token" content="([^"]+)"',page).group(1)
def call(action,data=None,card='main',guest=None,session=client,token=None):
    query=urllib.parse.urlencode({'action':action,'card':card,**({'guest':guest} if guest else {})})
    req=urllib.request.Request(BASE+'/api.php?'+query,data=None if data is None else json.dumps(data).encode(),headers={'Content-Type':'application/json','X-CSRF-Token':token or csrf})
    try:
        with session.open(req) as r:return r.status,json.loads(r.read())
    except urllib.error.HTTPError as e:return e.code,json.loads(e.read())
suffix=secrets.token_hex(4)
_,before=call('config')
code,new=call('card-create',{'title':'QA Ivory '+suffix,'slug':'qa-ivory-'+suffix,'template':'ivory'})
assert code==200,new
card=new['id']
_,cfg=call('config',card=card)
assert cfg['config']['theme']['template']=='ivory'
assert cfg['config']['gallery']['layout']=='3d'
assert call('card-create',{'title':'Duplicate slug','slug':new['slug'],'template':'ivory'})[0]==422
assert call('card-settings',{'title':'QA Ivory','slug':'../unsafe'},card)[0]==422
code,inv=call('invitee-save',{'name':'Nguyễn Minh Anh','salutation':'Chị'},card)
assert code==200,inv
g=inv['guests'][0]
url=BASE+'/index.php?'+urllib.parse.urlencode({'i':new['slug'],'guest':g['token']})
html=client.open(url).read().decode()
assert 'Nguyễn Minh Anh' in html and new['slug'] in html
assert g['token'] not in html.split('</head>',1)[0]  # Share metadata never leaks personal tokens.
assert 'pickupAddress' not in html  # No private RSVP payload in initial HTML.
ride={'name':'Spoofed name','attending':True,'count':3,'message':'Chúc mừng','transport':True,'pickupAddress':'12 Đường Hoa, Hà Nội','pickupCount':2,'phone':'0912345678'}
bad=copy.deepcopy(ride);bad['pickupCount']=4
assert call('rsvp',bad,card,g['token'])[0]==422
bad=copy.deepcopy(ride);bad['phone']='invalid'
assert call('rsvp',bad,card,g['token'])[0]==422
assert call('rsvp',ride,card,g['token'])[0]==200
ride['pickupCount']=3
assert call('rsvp',ride,card,g['token'])[0]==200
_,data=call('guests',card=card)
assert len(data['responses'])==1
r=data['responses'][0]
assert r['name']==g['name'] and r['pickupCount']==3 and r['phone']=='0912345678'
assert call('guests')[1]['responses']==[]
assert '0912345678' not in json.dumps(call('config',card=card)[1])
assert call('rsvp',ride,'main',g['token'])[0]==422
assert call('invitee-save',{'id':g['id'],'name':'Updated','salutation':'Gia đình'},card)[0]==200
assert call('invitee-rotate',{'id':g['id']},card)[0]==200
assert call('rsvp',ride,card,g['token'])[0]==422
code,_=call('card-settings',{'title':'QA Ivory updated','slug':'qa-renamed-'+suffix},card)
assert code==200
try:client.open(url);raise AssertionError('Old slug should be 404')
except urllib.error.HTTPError as e:assert e.code==404
_,current=call('config',card=card)
revision=current['revision']
current['config']['theme']['template']='editorial'
assert call('save',{'config':current['config'],'revision':revision},card)[0]==200
assert call('save',{'config':current['config'],'revision':revision},card)[0]==409
assert call('config')[1]['revision']==before['revision']
# Password creation/change + invalidation of other authenticated sessions.
password=secrets.token_hex(15)
assert call('password',{'newPassword':'short','confirmPassword':'short'})[0]==422
assert call('password',{'newPassword':password,'confirmPassword':password})[0]==200
anonymous=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
assert call('catalog',session=anonymous)[0]==401
login=anonymous.open(BASE+'/admin.php').read().decode();other_csrf=re.search(r'name="csrf-token" content="([^"]+)"',login).group(1)
assert 'Chào mừng trở lại' in login
post=urllib.parse.urlencode({'csrf':other_csrf,'password':password}).encode()
assert 'form-sections' in anonymous.open(urllib.request.Request(BASE+'/admin.php',data=post)).read().decode()
assert call('catalog',session=anonymous)[0]==200
assert call('password',{'currentPassword':'wrong','newPassword':password+'new','confirmPassword':password+'new'})[0]==422
next_password=secrets.token_hex(15)
assert call('password',{'currentPassword':password,'newPassword':next_password,'confirmPassword':next_password})[0]==200
assert call('catalog',session=anonymous)[0]==401
assert call('catalog')[0]==200
print('PASS: card isolation, unique/custom slugs, templates, personalized tokens, guest edits/rotation, transport validation/storage/privacy, duplicate RSVP update, revisions, password creation/change and session revocation.')
print('QA card:',card)
