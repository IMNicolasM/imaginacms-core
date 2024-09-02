<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Iwebhooks\Entities\Log;

class ClearCacheByRoutes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $routes;
    public $entity;

    /**
     * Create a new job instance.
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->routes = $this->initCacheClearableData('urls');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        if (!is_null($this->routes)){
            foreach ($this->routes as $route) {
                $promise = $client->get($route, ['headers' => ['icache-bypass' => 1]]);
                \Log::info('Route Update Cache: '. $route);
            }
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
