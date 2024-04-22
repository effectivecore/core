#!/usr/bin/env bash

cd "$(dirname "$0")/../"

hostname
whoami
git --version
git clean  -f -d
git reset --hard
git pull

find dynamic/cache -type f -not -name 'readme.md' -delete
