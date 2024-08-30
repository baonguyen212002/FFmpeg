<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;

class SegmentVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function handle()
    {
        try {
            $inputPath = $this->video->original_url; // Đường dẫn tương đối

            // Sử dụng đường dẫn tương đối cho output
            $outputDir = 'public/segments/' . $this->video->id;
            $playlistName = 'playlist.m3u8';
            $segmentPattern = 'segment_%03d.ts';

            // Tạo thư mục nếu chưa tồn tại
            if (!Storage::exists($outputDir)) {
                Storage::makeDirectory($outputDir);
            }

            $export = FFMpeg::fromDisk('local')
                ->open($inputPath)
                ->exportForHLS()
                ->setSegmentLength(10)
                ->setKeyFrameInterval(48);

            // Thêm các độ phân giải khác nhau
            $export->addFormat((new X264('aac'))->setKiloBitrate(500), function ($media) {
                $media->scale(426, 240);
            })->addFormat((new X264('aac'))->setKiloBitrate(1000), function ($media) {
                $media->scale(640, 360);
            })->addFormat((new X264('aac'))->setKiloBitrate(1500), function ($media) {
                $media->scale(854, 480);
            })->addFormat((new X264('aac'))->setKiloBitrate(3000), function ($media) {
                $media->scale(1280, 720);
            })->addFormat((new X264('aac'))->setKiloBitrate(6000), function ($media) {
                $media->scale(1920, 1080);
            })->toDisk('local')->save($outputDir . '/' . $playlistName);

            $this->setFolderPermissions(storage_path('app/' . $outputDir));
            $this->video->hls_url = Storage::disk('public')->url($outputDir . '/' . $playlistName);
            $this->video->save();

            // Xóa video gốc sau khi phân giải xong
            if (Storage::exists($inputPath)) {
                Storage::delete($inputPath);
            } else {
                Log::warning('Original video not found for deletion: ' . $inputPath);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function setFolderPermissions($folderPath)
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folderPath),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                chmod($item, 0755);
            }
        } catch (\Exception $e) {
            Log::error('Failed to set permissions for folder: ' . $folderPath . ' - ' . $e->getMessage());
        }
    }
}
