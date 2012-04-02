#!/bin/sh
# Updates the mdkyb project in dev environment

bin/vendors install
app/console doctrine:migrations:migrate --no-interaction
app/console doctrine:fixtures:load
