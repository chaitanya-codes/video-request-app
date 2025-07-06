#!/bin/bash

# Skipping migrations now
# until php artisan migrate --force; do
#   echo "Migration failed — retrying in 5s..."
#   sleep 5
# done

# Start Apache in foreground
exec apache2-foreground
