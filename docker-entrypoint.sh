#!/bin/bash

set -e


echo "================================="
echo " Bibliotheque PHP Container"
echo "================================="


echo "Checking environment..."


if [ -z "$DB_HOST" ]; then

    echo "WARNING: DB_HOST is not defined"

else

    echo "Database host : $DB_HOST"

fi


exec "$@"