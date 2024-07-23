<?php

return [
    'ffmpeg' => [
        'ffmpeg.binaries'  => env('FFMPEG_BIN','/usr/bin/ffmpeg'), // Đảm bảo đường dẫn chính xác
        'ffmpeg.threads'   => 12, // Số luồng để sử dụng
    ],
];
