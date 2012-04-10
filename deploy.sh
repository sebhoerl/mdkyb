#/!bin/sh

rm -rf deploy

echo Current server migration version:
read migration_version

mkdir -p deploy
cp -r app deploy
rm -rf deploy/app/logs/*
rm -rf deploy/app/cache/*
cp -r src deploy
cp -r external deploy
find vendor -name .git -type d | xargs rm -rf
cp -r vendor deploy
cp -r web deploy
cp -r uploads deploy
rm -rf deploy/uploads/*

rm -rf *.sql
app/console doctrine:migrations:migrate $migration_version --no-interaction
app/console doctrine:migrations:migrate --write-sql
mv *.sql deploy
app/console doctrine:migrations:migrate --no-interaction
echo OK
bin/vendors install --reinstall
