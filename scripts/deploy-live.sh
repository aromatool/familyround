#!/bin/bash
# Syncs the theme, familyround-core plugin, and mu-plugins to the live
# familyround.ro server. Run after testing changes locally in Docker.
#
# Usage: SSH_PASS='...' ./scripts/deploy-live.sh
set -euo pipefail

: "${SSH_PASS:?Set SSH_PASS to the Hostinger SSH password}"

SSH_HOST="186.242.128.176"
SSH_PORT="65002"
SSH_USER="u421217859"
REMOTE_BASE="~/domains/familyround.ro/public_html/wp-content"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(dirname "$SCRIPT_DIR")"

rsync_opts=(-az --delete -e "sshpass -p $SSH_PASS ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new")

echo "Syncing theme..."
rsync "${rsync_opts[@]}" "$REPO_ROOT/wp-content/themes/familyround/" "$SSH_USER@$SSH_HOST:$REMOTE_BASE/themes/familyround/"

echo "Syncing familyround-core plugin..."
rsync "${rsync_opts[@]}" "$REPO_ROOT/wp-content/plugins/familyround-core/" "$SSH_USER@$SSH_HOST:$REMOTE_BASE/plugins/familyround-core/"

echo "Syncing mu-plugins (excluding local-only files)..."
# No --delete here: this directory also holds Hostinger's own mu-plugins
# (auto-updates, preview-domain), which must never be removed by our sync.
rsync -az -e "sshpass -p $SSH_PASS ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new" \
  --exclude 'local-mail.php' --exclude 'local-lan-access.php' \
  "$REPO_ROOT/wp-content/mu-plugins/" "$SSH_USER@$SSH_HOST:$REMOTE_BASE/mu-plugins/"

echo "Flushing caches on the server..."
sshpass -p "$SSH_PASS" ssh -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" \
  "cd ~/domains/familyround.ro/public_html && wp cache flush --allow-root"

echo "Deploy done."
