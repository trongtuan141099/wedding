"""Positive integer pickup counts and per-card administrator deletion of wishes/RSVP."""
import http.cookiejar, json, os, pathlib, re, secrets, sys, urllib.error, urllib.request
BASE=sys.argv[1];ROOT=pathlib.Path(__file__).resolve().parents[1]
DATA=pathlib.Path(os.environ['WEDDING_TEST_DATA_DIR']).resolve()
assert DATA.is_relative_to((ROOT/'tests').resolve())
state=DATA/'state.php';previous=state.read_bytes() if state.exists() else None
credentials=DATA/'credentials.php';assert not credentials.exists()
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
html=client.open(BASE+'/admin.php').read().decode();token=re.search(r'name="csrf-token" content="([^"]+)"',html).group(1)
def call(action,data=None,card='main',csrf=token,session=client):
    request=urllib.request.Request(BASE+'/api.php?action='+action+'&card='+card,data=None if data is None else json.dumps(data).encode(),headers={'Content-Type':'application/json','X-CSRF-Token':csrf})
    try:
        with session.open(request) as response:return response.status,json.load(response)
    except urllib.error.HTTPError as error:return error.code,json.load(error)
try:
    assert '<th>Thao tác</th>' in html
    ride={'name':'Pickup integer test','attending':True,'count':1,'transport':True,'pickupAddress':'Test pickup address','phone':'0912345678','pickupCount':2}
    assert call('rsvp',ride)[0]==200, 'Pickup count incorrectly restricted to attendance count 1'
    for invalid in [0,-1,1.5,True,'','2e0',None]:
        status,result=call('rsvp',{**ride,'pickupCount':invalid})
        assert status==422 and 'số nguyên' in result['error'], (invalid,result)
    assert call('wish',{'name':'Removal test','message':'Temporary approved wish'})[0]==200
    assert call('wish',{'name':'Keep test','message':'Keep this wish'})[0]==200
    _,guests=call('guests');response=next(row for row in guests['responses'] if row['name']==ride['name']);wish=next(row for row in guests['wishes'] if row['name']=='Removal test');keep=next(row for row in guests['wishes'] if row['name']=='Keep test')
    assert response['pickupCount']==2 and response['count']==1
    assert call('moderate',{'id':wish['id'],'approved':True})[0]==200
    assert any(row['name']=='Removal test' for row in call('config')[1]['wishes'])
    _,created=call('card-create',{'title':'Deletion isolation','slug':'delete-test-'+secrets.token_hex(4),'template':'ivory'})
    other=created['id']
    for action,record in [('wish-delete',wish),('rsvp-delete',response)]:
        assert call(action,{'id':record['id']},csrf='invalid')[0]==403
        assert call(action,{'id':record['id']},card=other)[0]==422
        assert call(action,{'id':''})[0]==422
    # Turn off demo authentication by creating temporary credentials, then test real auth.
    password=secrets.token_hex(16)
    assert call('password',{'newPassword':password,'confirmPassword':password})[0]==200
    anonymous=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    page=anonymous.open(BASE+'/admin.php').read().decode();anon_token=re.search(r'name="csrf-token" content="([^"]+)"',page).group(1)
    assert call('wish-delete',{'id':wish['id']},csrf=anon_token,session=anonymous)[0]==401
    assert call('rsvp-delete',{'id':response['id']},csrf=anon_token,session=anonymous)[0]==401
    _,guest=call('invitee-save',{'name':'Retain invitation','salutation':'Bạn'})
    assert call('wish-delete',{'id':wish['id']})[0]==200
    assert call('rsvp-delete',{'id':response['id']})[0]==200
    _,remaining=call('guests')
    assert all(row['id']!=wish['id'] for row in remaining['wishes'])
    assert any(row['id']==keep['id'] for row in remaining['wishes'])
    assert all(row['id']!=response['id'] for row in remaining['responses'])
    assert not any(row['name']=='Removal test' for row in call('config')[1]['wishes'])
    assert guest['guests'][0] in call('invitees')[1]['invitees']
    assert call('rsvp-delete',{'id':response['id']})[0]==422
    print('PASS: pickup count independent of attendance, integer validation, deletion authentication/CSRF/card isolation, public wish removal, RSVP deletion and invitation retention.')
finally:
    credentials.unlink(missing_ok=True)
    if previous is None:state.unlink(missing_ok=True)
    else:state.write_bytes(previous)
