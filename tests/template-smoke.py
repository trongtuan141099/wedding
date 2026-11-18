"""Verify clean-clone content and safe, explicit synchronization of existing sites."""
import json, os, pathlib, shutil, subprocess, tempfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
PHP = shutil.which('php') or 'D:/xampp/php/php.exe'
PREFIX = b'<?php exit; ?>\n'
with tempfile.TemporaryDirectory(prefix='test-runtime-template-', dir=ROOT/'tests') as temporary:
    data = pathlib.Path(temporary)
    env = os.environ.copy()
    env['WEDDING_DATA_DIR'] = str(data)
    env.pop('WEDDING_ADMIN_PASSWORD_HASH', None)
    def php(code):
        return subprocess.run([PHP], input=('<?php '+code).encode(), cwd=ROOT, env=env,
                              stdout=subprocess.PIPE, stderr=subprocess.PIPE, check=True).stdout
    seed = json.loads(php("require 'bootstrap.php'; echo json_encode(initial_state());"))
    for card in [seed, *seed['cards'].values()]:
        assert all(not card[key] for key in ['responses', 'wishes', 'invitees', 'rates'])
        config = card['config']
        refs = [config['hero']['image'], config['music']['src'], config['sharing']['image'],
                *config['gallery']['items'], *[bank['qr'] for bank in config['banks']['items']]]
        for ref in refs:
            assert not ref.startswith('uploads/'), 'Clone depends on ignored upload'
            if ref.startswith('assets/'):
                assert (ROOT/ref).is_file(), 'Missing clone media'
        assert config['sharing']['baseUrl'] == ''
    state = json.loads(json.dumps(seed))
    state['config']['experience']['scrollSpeed'] = 79
    state['revision'] = 23
    state['cardMeta']['main']['slug'] = 'production-link'
    state['invitees'] = [{'id':'keep', 'token':'private-token', 'name':'Guest', 'salutation':'Bạn'}]
    state['responses'] = [{'id':'rsvp', 'phone':'private-phone'}]
    state['wishes'] = [{'id':'wish', 'approved':False}]
    state['rates'] = {'keep':{'at':1,'n':2}}
    state['cardMeta']['production-only'] = {'title':'Keep', 'slug':'production-only'}
    state['cards']['production-only'] = json.loads(json.dumps(seed['cards'][next(iter(seed['cards']))]))
    before = PREFIX + json.dumps(state).encode()
    (data/'state.php').write_bytes(before)
    credentials = b"<?php return 'unchanged-hash';"
    (data/'credentials.php').write_bytes(credentials)
    subprocess.run([PHP, 'tools/sync-template.php'], cwd=ROOT, env=env, check=True, capture_output=True)
    after = json.loads((data/'state.php').read_bytes()[len(PREFIX):])
    assert after['config'] == seed['config'] and after['revision'] == 24
    for key in ['invitees','responses','wishes','rates']:
        assert after[key] == state[key], key
    assert after['cardMeta']['main']['slug'] == 'production-link'
    assert after['cards']['production-only'] == state['cards']['production-only']
    assert (data/'credentials.php').read_bytes() == credentials
    assert next((data/'backups').glob('*.php')).read_bytes() == before
    # A template slug collision must leave production state byte-for-byte unchanged.
    other = next(iter(seed['cards']))
    del after['cards'][other]
    del after['cardMeta'][other]
    after['cardMeta']['production-only']['slug'] = seed['cardMeta'][other]['slug']
    before = PREFIX + json.dumps(after).encode()
    (data/'state.php').write_bytes(before)
    result = subprocess.run([PHP, 'tools/sync-template.php'], cwd=ROOT, env=env, capture_output=True)
    assert result.returncode != 0 and (data/'state.php').read_bytes() == before
print('PASS: clone media, sanitized template, content sync, backup, private data/credentials/slugs preserved, slug conflict atomicity.')
