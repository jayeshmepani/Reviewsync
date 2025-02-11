#!/bin/bash

storage_link="/public/storage"

# Check if storage symlink exists
if [ -L "$storage_link" ]; then
    echo "Storage symlink exists. Removing..."
    rm -f "$storage_link"
fi

# Recreate the storage link
echo "Creating new storage link..."
php artisan storage:link
echo "Storage link created successfully!"
