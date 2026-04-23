#!/usr/bin/env bash

cd "$(dirname "$0")"
cd ..

echo "Ignoring authorization protection for local development..."

php -S 0.0.0.0:3000 -t ./site
