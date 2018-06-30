<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com
 * |      Date: 2018/6/29 下午8:35
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/
namespace Houdunwang\LaravelView;

use Houdunwang\LaravelView\Command\StructureCommand;
use \Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //命令声明
        if ($this->app->runningInConsole()) {
            $this->commands([
                StructureCommand::class,
            ]);
        }

        //配置文件
        $this->publishes([
            __DIR__.'/config/hd_tables.php' => config_path('hd_tables.php'),
        ]);

        //视图定义
        $this->loadViewsFrom(__DIR__.'/views', 'HdLaravelView');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/HdLaravelView'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Upload::class, function ($app) {
            return new Upload($app);
        });
    }
}
