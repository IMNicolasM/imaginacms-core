<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ClearAllResponseCache implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $entity;
  public $clearAllResponseCache;

  /**
   * Create a new job instance.
   */
  public function __construct($entity)
  {
    $this->entity = $entity;
    $this->clearAllResponseCache = $this->initCacheClearableData('allResponseCache');
  }

  /**
   * Execute the job.
   */
  public function handle()
  {
    if ($this->clearAllResponseCache) {
      $settingRespository = app('Modules\Setting\Repositories\SettingRepository');
      $settingRespository->createOrUpdate(['core::siteCleanedAt' => now()]);
    }
  }

  /**
   * Return the needed data by cache provider from model
   *
   * @param $type
   * @return mixed|null
   */
  public function initCacheClearableData($type)
  {
    $response = null;
    if (method_exists($this->entity, 'getCacheClearableData')) {
      $cacheClearableData = $this->entity->getCacheClearableData();
      $response = $cacheClearableData[$type] ?? null;
    }
    return $response;
  }
}
