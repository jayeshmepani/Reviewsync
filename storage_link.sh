
#!/bin/bash

STORAGE_LINK="/app/public/storage"

# Check if the storage symlink exists
if [ -L "$STORAGE_LINK" ]; then
    echo "Storage symlink exists. Removing..."
    rm -f "$STORAGE_LINK"
elif [ -e "$STORAGE_LINK" ]; then
    echo "A file or folder named 'storage' exists, but it's not a symlink. Removing..."
    rm -rf "$STORAGE_LINK"
fi

# Recreate the storage symlink
echo "Creating new storage link..."
php artisan storage:link
echo "Storage link created successfully!"
