#/!bin/sh

rm -rf deploy

echo Current server migration version:
read migration_version

mkdir -p deploy
cp -r app deploy
rm -rf deploy/app/logs/*
rm -rf deploy/app/cache/*
cp -r src deploy
find vendor -name .git -type d | xargs rm -rf
cp -r vendor deploy
cp -r web deploy

rm *.sql
app/console doctrine:migrations:migrate $migration_version --no-interaction
app/console doctrine:migrations:migrate --write-sql
mv *.sql deploy
app/console doctrine:migrations:migrate

bin/vendors install --reinstall
