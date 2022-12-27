<?php

namespace Newnet\Media\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Newnet\Media\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'url'   => $this->getUrl(),
            'thumb' => $this->thumb,
        ];
    }
}
