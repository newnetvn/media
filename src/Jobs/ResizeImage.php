<?php

namespace Newnet\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Newnet\Media\ImageManipulator;
use Newnet\Media\Models\Media;

class ResizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Media */
    protected $media;


    /**
     * Create a new job instance.
     *
     * @param Media $media
     * @param array $conversions
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;

        $this->queue = config('media.queue');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(ImageManipulator::class)->resizeImage(
            $this->media
        );
    }

    /** @return Media */
    public function getMedia()
    {
        return $this->media;
    }

    /** @return array */
    public function getConversions()
    {
        return $this->conversions;
    }
}
