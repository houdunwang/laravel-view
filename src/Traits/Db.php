<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com  www.houdunren.com
 * |      Date: 2018/6/30 上午1:54
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/
namespace Houdunwang\LaravelView\Traits;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

trait Db
{
    protected $denyColumn = ['id', 'created_at', 'updated_at'];

    protected function getColumnData()
    {
        $columns = $this->listTableColumns($this->model);
        $configs = [];
        foreach ($columns as $column) {
            if ($this->allowColumn($column)) {
                $comment                     = explode('|', $column->getComment());
                $configs[$column->getName()] = [
                    'title' => $comment[0],
                    'name'  => $column->getName(),
                    'type'  => $comment[1],
                    'value' => '',
                ];
            }
        }

        return $configs;
    }

    protected function allowColumn($column)
    {
        $comment = explode('|', $column->getComment());

        return count($comment) == 2 &&
            ! in_array($column->getName(), $this->denyColumn);
    }

    protected function getDoctrineConnection()
    {
        $config           = new Configuration();
        $connectionParams = [
            'dbname'   => config('database.connections.mysql.database'),
            'user'     => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'host'     => config('database.connections.mysql.host'),
            'driver'   => 'pdo_mysql',
            'charset'  => config('database.connections.mysql.charset'),
        ];

        return DriverManager::getConnection($connectionParams, $config);
    }

    protected function listTableColumns($model)
    {
        return $this->getDoctrineConnection()->getSchemaManager()->listTableColumns($model->getTable());
    }

    protected function isTable()
    {
        $tables = $this->getDoctrineConnection()->getSchemaManager()->listTables();
        foreach ($tables as $table) {
            if ($this->model->getTable() == $table->getName()) {
                return true;
            }
        }

        return false;
    }
}
