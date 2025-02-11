$storageLink = "G:\laravel\reviewsync1\public\storage"

# Check if 'storage' is a symbolic link
if (Test-Path $storageLink -PathType Container) {
    Write-Host "Storage symlink exists. Removing..."
    Remove-Item -Path $storageLink -Force
}

# Recreate the storage link
Write-Host "Creating new storage link..."
php artisan storage:link
Write-Host "Storage link created successfully!"
