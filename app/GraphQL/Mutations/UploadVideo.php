<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Jobs\SegmentVideoJob;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;

final class UploadVideo
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $file = $args['file'];
        $path = $file->store('videos');

        // Lưu thông tin video vào database
        $video = Video::create([
            'title' => $args['title'],
            'original_url' => $path,
        ]);

        // Dispatch job để phân giải video
        SegmentVideoJob::dispatch($video);

        return $video;
    }
}
