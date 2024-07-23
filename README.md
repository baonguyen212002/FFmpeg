## Setup:
- Clone and Update .env
```shell
cp .env.example .env
```

- Run command:
```shell
composer install
```

```shell
php artisan key:generate
```

## Run Migrations
```shell
php artisan migrate
```

## .env
```text
QUEUE_CONNECTION=database
```

## Install FFmpeg

```shell
sudo apt-get install ffmpeg
```

## Kiểm tra xem Ffmpeg đã cài đặt thành công chưa

```shell
ffmpeg -version
```

```shell
php artisan storage:link
```

- Setup permission for folders and files of source code (Ex: user webserver -> shino, group webserver -> shino)
```shell
chown -R [user webserver]:[group webserver] .
```

## Create file config/laravel-ffmpeg.php
```php
<?php

return [
    'ffmpeg' => [
        'ffmpeg.binaries'  => env('FFMPEG_BIN','/usr/bin/ffmpeg'), // Đảm bảo đường dẫn chính xác
        'ffmpeg.threads'   => 12, // Số luồng để sử dụng
    ],
];
```

## Run Job
```shell
php artisan queue:listen 
```

## Clear cache and config
```shell
sudo php artisan cache:clear
sudo php artisan config:clear
```