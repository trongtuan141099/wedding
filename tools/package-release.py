"""Build cPanel releases with explicit UNIX permissions, regardless of build OS.
Run from any directory: python tools/package-release.py [--php /path/to/php]
Archives and checksums are written to the ignored releases/ directory.
The full package contains public invitation settings/media. The repair package
contains application files only and never contains state or uploaded media.
"""
import argparse
import datetime
import hashlib
import json
from pathlib import Path
import re
import shutil
import stat
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--php', default=shutil.which('php') or 'D:/xampp/php/php.exe')
args = parser.parse_args()
php = r'''<?php
require 'bootstrap.php';
$root=store_state();
$empty=['revision'=>0,'responses'=>[],'wishes'=>[],'rates'=>[],'invitees'=>[]];
$seed=array_merge($empty,['config'=>validate_config($root['config']),'cardMeta'=>$root['cardMeta'],'cards'=>[]]);
foreach($root['cards'] as $id=>$card)$seed['cards'][$id]=array_merge($empty,['config'=>validate_config($card['config'])]);
echo json_encode($seed,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
'''
result = subprocess.run([args.php], input=php.encode(), stdout=subprocess.PIPE,
                        stderr=subprocess.PIPE, cwd=ROOT, check=True)
seed = json.loads(result.stdout.decode('utf-8'))
configs = [seed['config']] + [c['config'] for c in seed['cards'].values()]
refs = set()
for config in configs:
    values = [config['hero']['image'], config['music']['src'], config['sharing']['image'], *config['gallery']['items'],
              *[b['qr'] for b in config['banks']['items']]]
    refs.update(v for v in values if v and re.fullmatch(r'(assets|uploads)/[A-Za-z0-9_.-]+', v))

source_names = ['index.php', 'admin.php', 'style.css', 'script.js', 'studio.css',
                'studio.js', 'experience.js', 'invitation.css', 'invitation.js',
                'studio-api.php', 'bootstrap.php', 'api.php', 'defaults.php',
                'account.php', 'social.php', 'social-image.php',
                'assets/fonts/PlayfairDisplay.ttf', 'assets/fonts/OFL.txt',
                '.htaccess', '.user.ini', 'README.md', 'DEPLOY.md',
                'data/.htaccess', 'uploads/.htaccess']
source = {name: (ROOT / name).read_bytes() for name in source_names}
public_files = set(refs) | {p.relative_to(ROOT).as_posix()
                          for p in (ROOT / 'assets').rglob('*') if p.is_file()}
full = dict(source)
for name in sorted(public_files):
    path = (ROOT / name).resolve()
    assert path.is_relative_to(ROOT.resolve()), 'Media outside workspace'
    if not path.is_file():
        raise SystemExit('Missing referenced media: ' + name)
    full[name] = path.read_bytes()
full['data/state.php'] = b'<?php exit; ?>\n' + result.stdout

def write_release(filename, files):
    output = ROOT / 'releases'
    output.mkdir(exist_ok=True)
    destination = output / filename
    temporary = destination.with_suffix('.tmp.zip')
    now = datetime.datetime.now().timetuple()[:6]
    directories = set()
    for name in files:
        parent = Path(name).parent
        while parent != Path('.'):
            directories.add(parent.as_posix() + '/')
            parent = parent.parent
    with zipfile.ZipFile(temporary, 'w', compression=zipfile.ZIP_DEFLATED) as archive:
        for name in sorted(directories) + sorted(files):
            directory = name.endswith('/')
            info = zipfile.ZipInfo(name, now)
            info.create_system = 3  # UNIX, even when the build machine is Windows.
            info.external_attr = ((stat.S_IFDIR | 0o755) if directory else
                                  (stat.S_IFREG | 0o644)) << 16
            if directory:
                info.external_attr |= 0x10
            info.compress_type = zipfile.ZIP_DEFLATED
            archive.writestr(info, b'' if directory else files[name])
    with zipfile.ZipFile(temporary) as archive:
        assert archive.testzip() is None
        for entry in archive.infolist():
            assert entry.create_system == 3
            assert stat.S_IMODE(entry.external_attr >> 16) == (0o755 if entry.is_dir() else 0o644)
            assert not any(part in entry.filename for part in ['credentials', '/sessions/', 'tests/', 'previews/'])
        for name, content in files.items():
            assert archive.read(name) == content
        if 'data/state.php' in files:
            deployed = json.loads(archive.read('data/state.php').split(b'\n', 1)[1])
            for card in [deployed, *deployed['cards'].values()]:
                for key in ['responses', 'wishes', 'invitees', 'rates']:
                    assert not card[key]
        else:
            assert all(not name.startswith('uploads/') or name == 'uploads/.htaccess' for name in files)
    temporary.replace(destination)
    digest = hashlib.sha256(destination.read_bytes()).hexdigest()
    destination.with_name(filename + '.sha256').write_text(digest + '  ' + filename + '\n', encoding='ascii')
    print(filename + ': ' + str(len(files)) + ' files; ' +
          str(round(destination.stat().st_size / 1048576, 2)) + ' MB; UNIX 0644/0755 verified')

write_release('wedding-invitation-cpanel.zip', full)
write_release('wedding-hosting-fix.zip', source)
