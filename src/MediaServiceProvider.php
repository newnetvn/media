<?php

namespace Newnet\Media;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image;
use Newnet\Acl\Facades\Permission;
use Newnet\Core\Events\CoreAdminMenuRegistered;
use Newnet\Core\Facades\AdminMenu;
use Newnet\Media\Events\MediaResizeEvent;
use Newnet\Media\Events\MediaUploadedEvent;
use Newnet\Media\Facades\Conversion;
use Newnet\Media\Jobs\PerformConversions;
use Newnet\Media\Jobs\ResizeImage;
use Newnet\Media\Models\Media;
use Newnet\Media\Models\Mediable;
use Newnet\Media\Models\MediaTag;
use Newnet\Media\Repositories\MediableRepository;
use Newnet\Media\Repositories\MediableRepositoryInterace;
use Newnet\Media\Repositories\MediaRepository;
use Newnet\Media\Repositories\MediaRepositoryInterface;
use Newnet\Media\Repositories\MediaTagRepositoryInterface;

class MediaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media.php', 'media'
        );

        $this->app->singleton(ConversionRegistry::class);
        $this->app->singleton(MediaUploader::class);

        $this->app->singleton(MediaRepositoryInterface::class, function () {
            return new MediaRepository(new Media());
        });

        $this->app->singleton(MediableRepositoryInterace::class, function () {
            return new MediableRepository(new Mediable());
        });
        $this->app->singleton(MediaTagRepositoryInterface::class, function () {
            return new MediaRepository(new MediaTag());
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/media.php' => config_path('media.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/media'),
        ], 'newnet-assets');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'media');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'media');

        $this->registerDefaultConversion();

        $this->loadRoutes();

        $this->registerPermissions();

        $this->registerAdminMenus();

        $this->registerBlade();
    }

    protected function registerDefaultConversion()
    {
        $thumbSize = config('media.thumbsize', []);
        if ($thumbSize && count($thumbSize) == 2) {
            Conversion::register('thumb', function (Image $image) use ($thumbSize) {
                return $image->fit($thumbSize[0], $thumbSize[1]);
            });

            Event::listen(MediaUploadedEvent::class, function (MediaUploadedEvent $event) {
                PerformConversions::dispatch(
                    $event->media, ['thumb']
                );
            });
        }
        Event::listen(MediaResizeEvent::class,  function (MediaResizeEvent $event) {
            ResizeImage::dispatch(
                $event->media
            );
        });
    }

    protected function loadRoutes()
    {
        Route::middleware(config('core.admin_middleware'))
            ->domain(config('core.admin_domain'))
            ->prefix(config('core.admin_prefix'))
            ->group(__DIR__.'/../routes/admin.php');
    }

    protected function registerPermissions()
    {
        Permission::add('media.admin.index', __('media::permission.media.index'));
        Permission::add('media.admin.upload', __('media::permission.media.upload'));
    }

    private function registerAdminMenus()
    {
        Event::listen(CoreAdminMenuRegistered::class, function () {
            AdminMenu::addItem(__('media::media.menu'), [
                'id'         => 'media',
                'parent'     => 'system_root',
                'route'      => 'media.admin.media.index',
                'permission' => 'media.admin.index',
                'icon'       => 'fas fa-photo-video',
                'order'      => 5,
            ]);
        });
    }

    public function registerBlade(){
        Blade::include('media::admin.modals.tag-modal', 'modalMedia');
        Blade::include('media::form.media', 'mediamanager');
    }
}
