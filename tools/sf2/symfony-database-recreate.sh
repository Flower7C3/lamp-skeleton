#!/usr/bin/env bash
#cd `dirname $0`
pwd
php app/console doctrine:database:drop --force
php app/console doctrine:database:create
