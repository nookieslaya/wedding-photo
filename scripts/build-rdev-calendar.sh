#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_DIR="$ROOT_DIR/plugin-build/animated-booking-calendar"
EXPORT_ROOT="$ROOT_DIR/plugin-export"
EXPORT_DIR="$EXPORT_ROOT/rdev-calendar"
ZIP_PATH="$EXPORT_ROOT/rdev-calendar.zip"
PLUGINS_ROOT="$ROOT_DIR/../../plugins"
PLUGINS_DIR="$PLUGINS_ROOT/rdev-calendar"
PLUGINS_ZIP_PATH="$PLUGINS_ROOT/rdev-calendar.zip"

if [[ ! -d "$SOURCE_DIR" ]]; then
  echo "Source plugin directory not found: $SOURCE_DIR" >&2
  exit 1
fi

mkdir -p "$EXPORT_ROOT"
rm -rf "$EXPORT_DIR"
mkdir -p "$EXPORT_DIR"

rsync -a --delete \
  --exclude '.DS_Store' \
  --exclude '.git' \
  --exclude '.gitignore' \
  "$SOURCE_DIR"/ "$EXPORT_DIR"/

rm -f "$ZIP_PATH"
(
  cd "$EXPORT_ROOT"
  zip -rq "$(basename "$ZIP_PATH")" "$(basename "$EXPORT_DIR")"
)

mkdir -p "$PLUGINS_ROOT"
rm -rf "$PLUGINS_DIR"
mkdir -p "$PLUGINS_DIR"

rsync -a --delete \
  --exclude '.DS_Store' \
  --exclude '.git' \
  --exclude '.gitignore' \
  "$SOURCE_DIR"/ "$PLUGINS_DIR"/

rm -f "$PLUGINS_ZIP_PATH"
(
  cd "$PLUGINS_ROOT"
  zip -rq "$(basename "$PLUGINS_ZIP_PATH")" "$(basename "$PLUGINS_DIR")"
)

echo "Exported plugin directory: $EXPORT_DIR"
echo "Created zip package: $ZIP_PATH"
echo "Synced plugin directory: $PLUGINS_DIR"
echo "Created zip package: $PLUGINS_ZIP_PATH"
