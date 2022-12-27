<?php

namespace Newnet\Media\Events;

use Illuminate\Queue\SerializesModels;
use Newnet\Media\Models\Media;

class MediaResizeEvent
{
    use SerializesModels;

    /**
     * @var Media
     */
    public $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

}
