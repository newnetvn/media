<?php

namespace Newnet\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Newnet\Core\Support\Traits\CacheableTrait;

/**
 * Newnet\Media\Models\Media
 *
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property string|null $author_type
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $author
 * @property-read string $extension
 * @property-read mixed $thumb
 * @property-read string|null $type
 * @property-read mixed $url
 * @property-read \Newnet\Media\Models\MediaTag|null $mediaTags
 * @property-read \Illuminate\Database\Eloquent\Collection|\Newnet\Media\Models\Mediable[] $mediables
 * @property-read int|null $mediables_count
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media newModelQuery()
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media newQuery()
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media query()
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereAuthorId($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereAuthorType($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereCreatedAt($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereDisk($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereFileName($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereId($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereMimeType($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereName($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereSize($value)
 * @method static \Rinvex\Cacheable\EloquentBuilder|Media whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Media extends Model
{
    use CacheableTrait;

    protected $table = 'media';

    protected $fillable = [
        'name',
        'file_name',
        'disk',
        'mime_type',
        'size',
    ];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (Media $model) {
            $model->filesystem()->deleteDirectory(
                $model->getDirectory()
            );
        });
    }

    public function author()
    {
        return $this->morphTo();
    }

    /**
     * Get the file type.
     *
     * @return string|null
     */
    public function getTypeAttribute()
    {
        return Str::before($this->mime_type, '/') ?? null;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Determine if the file is of the specified type.
     *
     * @param  string  $type
     * @return bool
     */
    public function isOfType(string $type)
    {
        return $this->type === $type;
    }

    /**
     * Get the url to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getUrl(string $conversion = '')
    {
        return $this->filesystem()->url(
            $this->getPath($conversion)
        );
    }

    /**
     * Get the full path to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getFullPath(string $conversion = '')
    {
        return $this->filesystem()->path(
            $this->getPath($conversion)
        );
    }

    /**
     * Get the path to the file on disk.
     *
     * @param  string  $conversion
     * @return string
     */
    public function getPath(string $conversion = '')
    {
        $directory = $this->getDirectory();

        if ($conversion) {
            $directory .= '/conversions/'.$conversion;
        }

        return $directory.'/'.$this->file_name;
    }

    /**
     * Get the directory for files on disk.
     *
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->created_at->format('Y/m').'/'.$this->getKey();
    }

    /**
     * Get the filesystem where the associated file is stored.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }

    public function mediables()
    {
        return $this->hasMany(Mediable::class);
    }

    public function getThumbAttribute()
    {
        return $this->getUrl('thumb');
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function mediaTags()
    {
        return $this->hasOne(MediaTag::class, 'media_id');
    }

    public function crop($width, $height, $format = 'jpg', $quality = 80){
        if (config('media.imageproxy.enable') === true){
            $urlCdn = config('media.imageproxy.server');
            return "{$urlCdn}/{$width}x{$height},q{$quality},{$format}/".$this->getUrl();
        }else{
            return $this->getUrl();
        }
    }
}
