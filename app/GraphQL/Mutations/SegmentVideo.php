<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Video;
use App\Jobs\SegmentVideoJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

final class SegmentVideo
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        try {
            $video = Video::findOrFail($args['id']);

            // Dispatch job to queue
            SegmentVideoJob::dispatch($video);

            return [
                'success' => true,
                'message' => 'Video segmentation job has been queued.',
                'video' => $video
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while processing the video.',
                'video' => null
            ];
        }
    }
}