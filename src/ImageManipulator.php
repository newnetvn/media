<?php

namespace Newnet\Media;

use Intervention\Image\ImageManager;
use Newnet\Media\Models\Media;

class ImageManipulator
{
    /** @var ConversionRegistry */
    protected $conversionRegistry;

    /** @var ImageManager */
    protected $imageManager;

    /**
     * Create a new manipulator instance.
     * @param ConversionRegistry $conversionRegistry
     * @param ImageManager       $imageManager
     * @return void
     */
    public function __construct(ConversionRegistry $conversionRegistry, ImageManager $imageManager)
    {
        $this->conversionRegistry = $conversionRegistry;

        $this->imageManager = $imageManager;
    }

    /**
     * Perform the specified conversions on the given media item.
     * @param Media $media
     * @param array $conversions
     * @param bool  $onlyIfMissing
     * @return void
     * @throws Exceptions\InvalidConversionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        if (!$media->isOfType('image')) {
            return;
        }

        foreach ($conversions as $conversion) {
            ini_set('memory_limit', config('media.memory_limit'));
            $path = $media->getPath($conversion);

            $filesystem = $media->filesystem();

            if ($onlyIfMissing && $filesystem->exists($path)) {
                continue;
            }

            $converter = $this->conversionRegistry->get($conversion);

            $image
                = $converter($this->imageManager->make($filesystem->readStream($media->getPath())));

            $filesystem->put($path, $image->stream(), [
                'visibility' => 'public',
            ]);
            $image->destroy();
        }
    }

    public function resizeImage(Media $media)
    {
        ini_set('memory_limit', config('media.memory_limit'));
        if (!$media->isOfType('image')) {
            return;
        }

        $image = \Image::make($media->getFullPath());

        if (file_exists($image)) {
            return;
        }
        list($width, $height) = getimagesize($media->getFullPath());
        $resizeSize = config('media.resize', []);

        if ($width <= $resizeSize[0] && $height <= $resizeSize[1]){
            return;
        }

        $ratio = $width / $height;
        if ($ratio > 1) {
            $resized_width = $resizeSize[0];
            $resized_height = $resizeSize[1] / $ratio;
        } else {
            $resized_width = $resizeSize[0] * $ratio;
            $resized_height = $resizeSize[1];
        }
        $image->resize($resized_width, $resized_height);
        $filesystem = $media->filesystem();
        $filesystem->put($media->getPath(), $image->stream(), [
            'visibility' => 'public',
        ]);
        $image->destroy();
    }
}
