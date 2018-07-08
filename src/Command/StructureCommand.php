<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com  www.houdunren.com
 * |      Date: 2018/6/30 上午1:54
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/
namespace Houdunwang\LaravelView\Command;

use Houdunwang\LaravelView\Traits\Db;
use Illuminate\Console\Command;

class StructureCommand extends Command
{
    use Db;

    protected $signature = 'hd:structure {model} {dir?} {--force}';

    protected $description = 'Generate the table structure cache';

    //模型
    protected $model;

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
        $model = $this->argument('model');
        if ( ! class_exists($model)) {
            return $this->error("model {$model} doesn't exist");
        }
        $this->model = new $model();
        if ( ! $this->isTable()) {
            return $this->error("table doesn't exist");
        }
        //$this->writeColumnsData();
        $this->writeHandleClass();
    }

    protected function getNamespace()
    {
        return str_replace('/', '\\', $this->getDir());
    }

    protected function writeHandleClass()
    {
        $modelClass = get_class($this->model);
        $modelName  = class_basename($modelClass);
        $file       = $this->getDir()."/{$modelName}Handle.php";
        if (is_file($file) && ! $this->option('force')) {
            return;
        }
        $namespace = studly_case($this->getNamespace());
        $columns   = implode("','", array_keys($this->getColumnData()));
        file_put_contents($file, <<<str
<?php 
namespace {$namespace};

use Houdunwang\LaravelView\BaseHandle;
use {$modelClass};

class {$modelName}Handle extends BaseHandle{
    
    //生成表单的字段，也是表单显示的顺序
    protected \$allowFields = ['$columns'];
    
    public function __construct({$modelName} \${$modelName})
    {
        parent::__construct(\${$modelName});
    }
    
    //下面是test字段的演示
    public function _test(){
        return [
            'title'=>'这是标题',//表单标题
            'type'=>'input',//表单类型
            'placeholder'=>'这是提示信息',
            'options'=>function(){
                //只对 select/radio/checkbox 表单有效
                return [1=>'男',2=>'女'];
            }
        ];
    }
}
str
        );
        $this->info($file." Creating successful");
    }

    protected function getDir()
    {
        $dirArgument = $this->argument('dir');

        if ($dirArgument) {
            $dir = ucfirst($dirArgument).'/Tables/'.class_basename($this->model);
        } else {
            $dir = config('hd_tables.path').'/Tables/'.class_basename($this->model);
        }
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
