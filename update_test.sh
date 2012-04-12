#!/bin/sh
# Updates the mdkyb project in dev environment

bin/vendors install
app/console doctrine:schema:update --force
app/console doctrine:fixtures:load
