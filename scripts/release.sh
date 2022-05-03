#!/bin/sh

cd ..

rm -rf ~/dm/release/dark-matter
mkdir -p ~/dm/release/dark-matter

rsync -av -progress ./ ~/dm/release/dark-matter --exclude node_modules --exclude vendor

# Run the build.
cd ~/dm/release/dark-matter

npm run release

# Remove the unnecessary files.

rm -rf node_modules
rm -rf vendor
rm -rf scripts
rm -rf domain-mapping/ui
rm -rf .*
rm *.json
rm *.lock
rm phpcs.xml
rm postcss.config.js
rm webpack.config.js

cd ~/dm/release/

zip -r dark-matter-$1.zip dark-matter/*
mv dark-matter-$1.zip ../dark-matter-$1.zip