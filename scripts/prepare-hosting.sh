#!/usr/bin/env bash
set -euo pipefail

# Assembles the Firebase Hosting root (dist/hosting) from the built embed
# bundle. Run AFTER `npm run build` — webpack wipes dist/ at build start,
# so building after this script would delete the hosting dir again.
#
#   npm run build && bash scripts/prepare-hosting.sh

BUNDLE="dist/embed/jcp-embed.min.js"
OUT="dist/hosting"

if [ ! -f "$BUNDLE" ]; then
    echo "Error: $BUNDLE not found — run 'npm run build' first." >&2
    exit 1
fi

rm -rf "$OUT"
mkdir -p "$OUT/v1"
cp "$BUNDLE" "$OUT/v1/"

echo "Hosting root ready:"
find "$OUT" -type f
