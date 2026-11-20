#!/usr/bin/env bash
# Chạy trong container mongo:7 (service mongo-init). Idempotent.
set -euo pipefail
for f in /database/mongo/*.js; do
  echo "mongo-init: $f"
  mongosh "$MONGO_URI" --quiet --file "$f"
done
