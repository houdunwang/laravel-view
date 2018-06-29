<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com  www.houdunren.com
 * |      Date: 2018/6/30 上午12:12
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/

namespace Houdunwang\LaravelView;

use Houdunwang\LaravelView\Traits\Db;

class BaseHandle
{
    use Db;
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function render()
    {
        $columns = $this->getAllowColumns();
        $html    = array_map(function ($column) {
            return $this->view($column);
        }, $columns);

        return (implode('', $html));
    }

    protected function view($field)
    {
        $field['value'] = $this->model[$field['name']];
        if (method_exists($this, '_'.$field['name'])) {
            $field = array_merge($field, call_user_func_array([$this, '_'.$field['name']], []));
        }
        $model = $this->model;
        return response(view('HdStructure::'.$field['type'], compact('field', 'model')))->getContent();
    }

    //获取允许的列表
    protected function getAllowColumns()
    {
        $columns = $this->getColumnData();

        return array_filter($columns, function ($column) {
            return in_array($column['name'], $this->allowFields);
        });
    }
}
