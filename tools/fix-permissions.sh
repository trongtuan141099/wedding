#!/usr/bin/env bash
# Run with: bash tools/fix-permissions.sh (as the cPanel account owner).
set -euo pipefail
project_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
cd -- "$project_root"
[[ -f index.php && -f bootstrap.php && -f data/.htaccess && ! -L data ]] || {
  echo 'Application files are missing; no permissions changed.' >&2
  exit 1
}
chmod 0755 "$project_root"
find . -maxdepth 1 -type f ! -name '.env*' -exec chmod 0644 {} +
for directory in assets uploads; do
  if [[ -d "$directory" && ! -L "$directory" ]]; then
    find "$directory" -type d -exec chmod 0755 {} +
    find "$directory" -type f -exec chmod 0644 {} +
  fi
done
# Private data stays inaccessible over HTTP and readable only where needed.
chmod 0750 data
find data -maxdepth 1 -type f -exec chmod 0600 {} +
if [[ -d data/sessions && ! -L data/sessions ]]; then
  find data/sessions -type d -exec chmod 0700 {} +
  find data/sessions -type f -exec chmod 0600 {} +
fi
echo 'Permissions updated: application 0755/0644, private data 0750/0600.'
