#/!bin/sh

rm -rf deploy
mkdir -p deploy
cp -r app deploy
rm -rf deploy/app/logs/*
rm -rf deploy/app/cache/*
cp -r src deploy
cp -r external deploy
cp -r vendor vendor_backup
find vendor -name .git -type d | xargs rm -rf
cp -r vendor deploy
cp -r web deploy
cp -r uploads deploy
rm -rf deploy/uploads/*
rm -rf deploy/web/uploads/*
rm -rf deploy/forum
rm -rf deploy/wiki
rm -rf vendor
cp -rf vendor_backup vendor
rm -rf vendor_backup
