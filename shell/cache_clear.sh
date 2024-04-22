#!/usr/bin/env bash

cd "$(dirname "$0")/../"

find dynamic/cache -type f -not -name 'readme.md' -delete
