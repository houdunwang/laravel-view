<?php
/** .-------------------------------------------------------------------
 * |      Site: www.hdcms.com  www.houdunren.com
 * |      Date: 2018/7/8 上午8:53
 * |    Author: 向军大叔 <2300071698@qq.com>
 * '-------------------------------------------------------------------*/

namespace Houdunwang\LaravelView\Traits;

trait GenerateHandleTrait
{
    public function getHandleContent()
    {
        $columns  = $this->listTableColumns($this->model);
        $configs  = [];
        $classStr = '';
        foreach ($columns as $column) {
            if ($this->allowColumn($column)) {
                $params = explode('|', $column->getComment());
                if (count($params) >= 2) {
                    $action = '_'.trim($params[1]);
                    if (method_exists($this, $action)) {
                        $classStr .= $this->$action($params, $column);
                    }
                }
            }
        }
        return $classStr;
    }

    public function _input($params, $column)
    {
        return <<<str
public function _{$column->getName()}()
{
	return [
		'title'       => '{$params[0]}',//表单标题
		'type'        => 'input',//表单类型
	];
}\n
str;
    }

    public function _simditor($params, $column)
    {
        return <<<str
public function _{$column->getName()}()
{
	return [
		'title'       => '{$params[0]}',//表单标题
		'type'        => 'simditor',//表单类型
	];
}\n
str;
    }

    protected function _textarea($params, $column)
    {
        return <<<str
public function _{$column->getName()}()
{
        return [
            'title'       => '{$params[0]}',//表单标题
            'type'        => 'textarea',//表单类型
        ];
}\n
str;

    }

    protected function _image($params, $column)
    {
        return <<<str
public function _{$column->getName()}()
{
        return [
            'title'       => '{$params[0]}',//表单标题
            'type'        => 'image',//表单类型
        ];
}\n
str;

    }

    public function _radio($params, $column)
    {
        $options   = explode(',', $params[2]);
        $optionStr = '';
        foreach ($options as $option) {
            $info      = explode(':', $option);
            $optionStr .= "['title' =>'{$info[1]}', 'value' =>{$info[0]},'checked'=>\$this->model['{$column->getName()}']=={$info[0]}],";
        }

        return <<<str
public function _{$column->getName()}()
{
        return [
            'title'       => '{$params[0]}',//表单标题
            'type'        => 'radio',//表单类型
            'options'     => function () {
                return [
                   {$optionStr}
                ];
            },
        ];
}\n
str;
    }

    protected function _checkbox($params, $column)
    {
        $options   = explode(',', $params[2]);
        $optionStr = '';
        foreach ($options as $option) {
            $info      = explode(':', $option);
            $optionStr .= "['title' => '{$info[1]}', 'value' => {$info[0]},'checked'=>in_array('北京',\$values)],";
        }

        return <<<str
public function _{$column->getName()}()
{
        return [
            'title'       => '{$params[0]}',//表单标题
            'type'        => 'checkbox',//表单类型
            'options'     => function () {
                \$values = explode(',', \$this->model['{$column->getName()}']);
                return [
                    $optionStr
                ];
            },
        ];
}\n
str;

    }

    protected function _select($params, $column)
    {
        $options   = explode(',', $params[2]);
        $optionStr = '';
        foreach ($options as $option) {
            $info      = explode(':', $option);
            $optionStr .= "['title' => '{$info[1]}', 'value' => {$info[0]},'selected'=>\$this->model['{$column->getName()}']=={$info[0]},'disabled'=> ''],";
        }

        return <<<str
public function _{$column->getName()}()
{
        return [
            'title'       => '父级栏目',//表单标题
            'type'        => 'select',//表单类型
            'options'     => function () {
                return [
                    $optionStr
                ];
            },
        ];
}\n
str;

    }
}
