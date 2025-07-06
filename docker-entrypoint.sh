#!/bin/bash

# Optional: wait for clever-cloud MySQL to be reachable
# Adjust DB_HOST if needed
until php artisan migrate --force; do
  echo "Migration failed — retrying in 5s..."
  sleep 5
done

# Start Apache in foreground
exec apache2-foreground
