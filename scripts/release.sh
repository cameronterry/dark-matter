#!/bin/sh

cd ..

rm -rf ~/dm/release
mkdir -p ~/dm/release

rsync -av -progress ./ ~/dm/release --exclude node_modules --exclude vendor

# Run the NPM builds.
cd ~/dm/release

npm install

npm run dev
npm run build

# Remove the unnecessary files.

rm -rf node_modules
rm -rf scripts
rm -rf domain-mapping/ui
rm -rf .*
rm *.json
rm webpack.config.js

zip -r dark-matter *
mv dark-matter.zip ../dark-matter.zip