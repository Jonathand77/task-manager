#!/usr/bin/env bash
set -e

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/../docker-compose.yml"
MIGRATIONS_DIR="$ROOT_DIR/database/migrations"

echo "Starting postgres container (if not running)..."
docker-compose -f "$COMPOSE_FILE" up -d postgres

echo "Waiting for Postgres to be ready..."
# Wait until pg_isready inside container returns success
until docker-compose -f "$COMPOSE_FILE" exec -T postgres pg_isready -U "$POSTGRES_USER" >/dev/null 2>&1; do
  sleep 1
done

echo "Applying migrations from $MIGRATIONS_DIR"
for f in "$MIGRATIONS_DIR"/*.sql; do
  BASENAME="$(basename "$f")"
  echo "Applying $BASENAME"
  docker-compose -f "$COMPOSE_FILE" exec -T postgres bash -lc "psql -U \"$POSTGRES_USER\" -d \"$POSTGRES_DB\" -f /docker-entrypoint-initdb.d/$BASENAME" || true
done

echo "Migrations finished."
