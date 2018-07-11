<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com  www.houdunren.com
 * |      Date: 2018/6/30 上午1:54
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/
namespace Houdunwang\LaravelView\Command;

use Houdunwang\LaravelView\Traits\Db;
use Houdunwang\LaravelView\Traits\GenerateHandleTrait;
use Houdunwang\Module\Traits\BuildVars;
use Illuminate\Console\Command;

class HandleCommand extends Command
{
    use Db, GenerateHandleTrait, BuildVars;

    protected $signature = 'hd:handle {model} {module} {--force}';

    protected $description = 'Generate the table structure cache';

    protected $model;
    protected $module;

    //模型对象
    protected $modelInstance;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model  = ucfirst($this->argument('model'));
        $module = ucfirst($this->argument('module'));
        $this->setVars($model,$module);
        $modelClass = $this->vars['NAMESPACE'].'Entities\\'.$model;
        if ( ! class_exists($modelClass)) {
            return $this->error("model {$modelClass} doesn't exist");
        }
        $this->model = new $modelClass();
        if ( ! $this->isTable()) {
            return $this->error("table doesn't exist");
        }
        $this->writeHandleClass();
    }

    protected function getNamespace()
    {
        return $this->getVar('NAMESPACE').'Tables';
    }

    protected function writeHandleClass()
    {
        $modelClass = get_class($this->model);
        $modelName  = class_basename($modelClass);
        $file       = $this->getDir()."/{$modelName}Handle.php";
        if (is_file($file) && ! $this->option('force')) {
            return;
        }
        $namespace     = studly_case($this->getNamespace());
        $handleContent = $this->getHandleContent();
        $columns       = implode("','", array_keys($this->getColumnData($this->model)));
        file_put_contents($file, <<<str
<?php 
namespace {$namespace};

use Houdunwang\LaravelView\BaseHandle;
use {$modelClass};

class {$modelName}Handle extends BaseHandle{
    
    //编辑修改时显示的字段
    protected \$allowFields = ['$columns'];
    
    //列表页显示的字段
    protected \$listShowFields = ['$columns'];
    public function __construct({$modelName} \${$modelName})
    {
        parent::__construct(\${$modelName});
    }
    
    $handleContent
}
str
        );
        $this->info($file." Creating successful");
    }

    protected function getDir()
    {
        $dir =  $this->getVar('MODULE_PATH').'/Tables/';
        is_dir($dir) or mkdir($dir, 0755, true);

        return $dir;
    }

    protected function writeColumnsData()
    {
        $columns = $this->getColumnData($this->model);
        if ($file = $this->getTableCacheFile()) {
            file_put_contents($file, '<?php return '.var_export($columns, true).';');
            $this->info($file." Creating successful");
        }
    }

    protected function getTableCacheFile()
    {
        $file = $this->getDir().'/structure.php';
        if (is_file($file) && ! $this->option('force')) {
            $this->error('file is exists');

            return false;
        }

        return $file;
    }
}
