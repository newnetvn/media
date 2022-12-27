<?php

namespace Newnet\Media\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newnet\Media\Models\MediaTag
 *
 * @property int $id
 * @property string $tags_type
 * @property int $tags_id
 * @property int $media_id
 * @property string|null $label
 * @property string|null $title
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $MediaTags
 * @property-read \Illuminate\Database\Eloquent\Collection|\Newnet\Media\Models\Media[] $tagImage
 * @property-read int|null $tag_image_count
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereTagsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereTagsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MediaTag extends Model
{
    protected $table = 'media_tags';
    protected $fillable = ['media_id', 'label', 'title', 'content', 'entity'];


    public function tagImage()
    {
        return $this->hasMany(Media::class, 'media_id');
    }

    public function MediaTags()
    {
        return $this->morphTo('tags');
    }

}
