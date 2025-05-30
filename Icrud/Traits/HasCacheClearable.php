<?php

namespace Modules\Core\Icrud\Traits;

use Modules\Core\Jobs\ClearCacheByRoutes;
use Modules\Core\Jobs\ClearCacheWithCDN;
use Modules\Core\Jobs\ClearAllResponseCache;

trait HasCacheClearable
{
  public static function bootHasCacheClearable()
  {
    static::creating(function ($model) {
      if (method_exists($model, 'createdWithBindings')) {
        // Listen for createdWithBindings instead of created
        $model::createdWithBindings(function ($model) {
          $model->initCacheClearable();

        });
      } else {
        // Default to created event
        static::created(function ($model) {
          $model->initCacheClearable();
        });
      }
    });

    static::saving(function ($model) {
      if ($model->exists) {
        if (method_exists($model, 'updatedWithBindings')) {
          // Listen for createdWithBindings instead of created
          $model::updatedWithBindings(function ($model) {
            $model->initCacheClearable();

          });
        } else {
          // Default to saved event
          static::saved(function ($model) {
            $model->initCacheClearable();
          });
        }
      }
    });

    static::deleting(function ($model) {
      $model->initCacheClearable();
    });
  }

  /**
   * Call the cache providers to clear model cache
   *
   * @return void
   */
  public function initCacheClearable()
  {
    $responseCache = env('RESPONSE_CACHE_ENABLED');
    $appCache = env('APP_CACHE');
    $clearResponseCache = app()->bound('clearResponseCache') ? app('clearResponseCache') : true;
    if ($clearResponseCache && $responseCache && $appCache) {
      if (method_exists($this, 'getCacheClearableData')) {
        ClearCacheByRoutes::dispatch($this)->onQueue('cacheByRoutes');
        ClearCacheWithCDN::dispatch($this)->onQueue('cacheByRoutes');
        ClearAllResponseCache::dispatch(['entity' => $this]);
      }
    }
  }
}
