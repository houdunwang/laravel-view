## 介绍

根据模型自动生成页面表单，提高网站开发速度。
> houdunren.com @ 向军大叔  

项目地址：https://packagist.org/packages/houdunwang/laravel-view

## 安装组件

**安装组件**

```
composer require houdunwang/laravel-view
```

**生成配置文件**

```
php artisan vendor:publish --provider="Houdunwang\Structure\ServiceProvider"
```

组件会生成配置文件 `config/hd_tables.php` 

**发布视图**

系统会在 `resources/views/vendor/HdLaravelView` 目录生成表单布局视图（不需要开发者使用）。

```
php artisan vendor:publish --provider="Houdunwang\LaravelView\ServiceProvider"
```

**前端库**

部分视图组件使用Vue.js 构建，需要安装前端库完成。

请参考文档  https://github.com/houdunwang/houdunren-vue-form  进行安装配置。

## 处理器

系统会为每个表生成 `Handle.php` 处理器文件，处理器用于对字段进行处理。

处理器用于对表单的结构定义并生成表单模板。

```
php artisan hd:handle 模型 [目录] [--force]
```

> [目录] 生成文件的目录，不设置目录时使用配置项的值
>
> [--force] 强制覆盖生成（慎重使用）

### 创建处理器

我们可以通过修改数据表注释的形式，快速生成表单处理器，请看下面是栏目表的字段声明。

下面是 `categories` 表的数据迁移内容

```
Schema::create('categories', function (Blueprint $table) {
	$table->increments('id');
	$table->timestamps();
	table->string('title')->comment('栏目名称|input');
	$table->string('description')->nullable()->comment('栏目描述|textarea');
	$table->tinyInteger('iscommend')->default(0)->comment('推荐|radio|1:是,2:否');
	$table->tinyInteger('city')->default(0)->comment('城市|checkbox|1:中国,2:北京,3:广东');
	$table->string('pic')->nullable()->comment('栏目图片|image');
	$table->integer('pid')->default(0)->comment('父级栏目|select|1:电影,2:游戏,3:汽车');
});
```

下面以 `categories` 表的 `title` 字段设置进行说明。

```
 `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目名称|input',
```

上面是title字段的声明，COMMENT属性以 | 分隔 `中文描述|模板表单类型|选项`

| 表单类型 | 表注释                                    | 说明 |
| -------- | ----------------------------------------- | ---- |
| input    | 表单名称\|input                           |      |
| textarea | 表单名称\|textarea                        |      |
| image    | 表单名称\|image                           |      |
| radio    | 表单名称\|radio\|1:男,2:女                |      |
| checkbox | 表单名称\| checkbox\|1:中国,2:北京,3:广东 |      |
| select   | 表单名称\|select\|1:电影,2:游戏,3:汽车    |      |

严格使用英文字符

下面是生成`Admin` 模块的 `Category` 模型处理器。

```
php artisan hd:handle Category Article
```

系统将在 `Modules/Article/Tables` 目录中创建处理器文件

```
└── CategoryHandle.php  #栏目模型处理器
```

如果处理器已经存在，可以加上 `--force` 参数强制生成

```
php artisan hd:handle Category Article --force
```

## 表单处理

通过上面的命令我们已经创建了处理器文件，下面来对处理器文件内容进行解释。

在处理器中以 `_` + `字段名` 定义的方法，会用于格式化字段使用，下面对字段进行说明。

因为有些表单需要  https://github.com/houdunwang/houdunren-vue-form 组件的处理，所以请先行安装并配置正确这个组件。

### 数据结构

下面是表单数据结构的说明，返回合法的数据结构才会正确渲染页面。

| 变量     | 说明       | 适用场景             |
| -------- | ---------- | -------------------- |
| title    | 中文标题   | 是                   |
| value    | 表单值     | 是                   |
| name     | 字段变量名 | 不需要设置           |
| type     | 表单类型   | 是                   |
| options  | 选项值     | 适用于 radio//select |
| disabled | 禁用       | 表单的 disabled 属性 |

1. 所有变量支持以匿名函数返回
2. $this->model 为当前记录模型

### 文本框

```
public function _title()
{
	return [
		'title'=> '栏目名称',//表单标题
		'type'=> 'input',//表单类型
	];
}
```

### 文本域

```
public function _description()
{
	return [
		'title'=> '栏目描述',//表单标题
		'type'=> 'textarea',//表单类型
	];
}
```

### 单选框

```
public function _iscommend()
{
	return [
		'title'=> '推荐',//表单标题
		'type'=> 'radio',//表单类型
		'options'=> function () {
            return [
                ['title' => '是', 'value' => 1],
                ['title' => '否', 'value' => 0]
            ];
        },
	];
}
```

### 复选框

```
public function _city()
{
	return [
		'title'       => '城市',//表单标题
		'type'        => 'checkbox',//表单类型
		'options'     => function () {
            $values = explode(',', $this->model['city']);
            return [
                ['title' => '北京', 'value' => 1,'checked'=>in_array('北京',$values)],
                ['title' => '上海', 'value' => 2,'checked'=>in_array('上海',$values)],
                ['title' => '广东', 'value' => 3,'checked'=>in_array('广东',$values)],
            ];
        },
	];
}
```

### 单张图片

需要正确配置 https://github.com/houdunwang/houdunren-vue-form   上传参数。

```
public function _pic()
{
	return [
		'title'=> '栏目图片',//表单标题
		'type'=> 'image',//表单类型
	];
}
```

### 列表框

```
public function _pid()
{
	return [
            'title'       => '父级栏目',//表单标题
            'type'        => 'select',//表单类型
            'options'     => function () {
				$data = [[
				'id'=>0,'title' => '顶级栏目', 'value' => 0, 
				'selected' => false, 'disabled' => false
				]];
				$res=[];
                foreach ($this->model->get() as $cat) {
                    $res[] = [
                        //option文本描述
                        'title'    => $cat['title'],
                        //options值
                        'value'    => $cat['id'],
                        //选中的选项
                        'selected' => $this->model->pid == $cat['id'],
                        //不允许选择自身
                        'disabled' => $this->model->id == $cat['id'],
                    ];
                }
			return $res;
		},
	];
}
```

### 自定义模板

如果对系统提供的表单模板不满意，只需要在返回 `_view` 变量声明自定义的模板。

```
public function _title()
{
	return [
		'title'=> '栏目名称',//表单标题
		'type'=> 'input',//表单类型
		'_view'=> 'form.input'//将使用 resources/views/form/input.blade.php 渲染
	];
}
```

## 渲染视图 

使用处理器可以渲染出页面需要的表单元素。

```
<?php namespace App\Http\Controllers;

use App\Category;
use App\Tables\CategoryHandle;

class HomeController extends Controller
{
    public function create(Category $Category)
    {
        $handle = new CategoryHandle($Category);
        $html   = $handle->render();

        return view('home', compact('html'));
    }
}
```

系统根据 `App\Tables\CategoryHandle` 处理器结合模型 `Category` 生成页面视图。

当组件有数据时即编辑时，页面视图会自动添加上数据内容。

通过以下代码模板中调用，就构建出视图了。

```
{!! $html !!}
```

## 处理器方法

#### 获取字段列表

```
$handle = new ContentHandle(new Content);
$handle->getListColumns() 
```

#### 获取默认值

学用于后台列表页显示字段内容

```
$handle = new ContentHandle(new Content);
$handle->value(Content::find(1),'title')
```

第一个参数为模型，第二个为字段名

如果处理器存在 _title_value 命名方法，调用该方法返回值，否则返回模型默认值，图片时以 img 标签实现。



