#!/bin/sh

cd ..

rm -rf ~/dm/release
mkdir -p ~/dm/release

rsync -av -progress ./ ~/dm/release --exclude node_modules --exclude vendor

# Run the build.
cd ~/dm/release

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

zip -r dark-matter-$1.zip *
mv dark-matter-$1.zip ../dark-matter-$1.zip