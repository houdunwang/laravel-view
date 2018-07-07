## 介绍

根据模型自动生成页面表单，提高网站开发速度。
> houdunren.com @ 向军大叔  

项目地址：https://github.com/houdunwang/laravel-view

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

## 数据表

表单生成规则以数据表的注释为主，下面是用于后面讲解的栏目表结构。

```
CREATE TABLE `article_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目名称|input',
  `pid` mediumint(9) NOT NULL COMMENT '父级栏目|select',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目描述|textarea',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 字段修饰

表单根据字段的注释生成，下面是各种类型表单的注释说明。

下面以 article_categories 表的title 字段设置进行说明。

```
 `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题|input',
```

上面是title字段的声明，COMMENT属性以 | 分隔 `中文描述|模板表单类型|选项`

| 表单类型 | 表注释                        | 说明                               |
| -------- | ----------------------------- | ---------------------------------- |
| input    | 表单名称\|input               |                                    |
| textarea | 表单名称\|textarea            |                                    |
| image    | 表单名称\|image               |                                    |
| radio    | 表单名称\|radio\|1:男,2:女    |                                    |
| checkbox | 表单名称\|checkbox\|1:男,2:女 |                                    |
| select   | 表单名称\|select              | 需要在 处理器类中返回 option列表值 |

## 处理器

系统会为每个表生成 `Handle.php` 处理器文件，处理器用于对表单的二次处理。

### 创建处理器

系统根据模型分析表结构，自动生成处理器。

```
php artisan hd:structure 模型 [目录] [--force]
```

> [目录] 生成文件的目录，不设置目录时使用配置项的值
>
> [--force] 强制覆盖生成（慎重使用）

下面是生成 `ArticleCategory` 模型处理器。

```
php artisan hd:structure Modules\\Entities\\Category Modules/Article
```

系统将在 `Modules/Article/Tables` 目录中创建以下文件结构

```
└── Category
    ├── CategoryHandle.php  #栏目模型处理器
```

如果处理器已经存在，可以加上 `--force` 参数强制生成

```
php artisan hd:structure Modules\\Entities\\Category Modules/Article --force
```

### 字段说明

处理器返回的数据会被渲染到表单视图中，下面是对合法返回值的说明：

| 字段名  | 字段说明                             | 系统字段 |
| ------- | ------------------------------------ | -------- |
| title   | 中文标题                             | 是       |
| value   | 默认值，自动从模型中获取             | 是       |
| name    | 字段变量名                           | 是       |
| type    | 表单类型                             | 是       |
| options | 选项值，适用于 radio/checkbox/select | 否       |

### 列表框处理

由于列表框和业务强依赖系统生成意义不大，需要开发者在处理器中自行实现。

下面是获取 `article_categories` 表 `pid` 字段视图中显示列表值的操作。

* 字段处理函数定义在 `CategoryHeadle.php` 类文件中
* 字段处理函数命名规则 `_`+`字段名`

```
class ArticleCategoryHandle extends BaseHandle
{
	...
	public function _pid()
    {
        $data = [];
        foreach ($this->model->get() as $cat) {
            $data[]=[
                //option文本描述
                'title'=>$cat['title'],
                //options值
                'value'=>$cat['id'],
                //选中的选项
                'selected'=>$this->model->pid ==$cat['id'],
                //不允许选择自身
                'disabled'=>$this->model->id == $cat['id']
            ];
        }
		return ['title'=>'重设栏目标题','options'=>$data];
    }
    ...
}
```

* 返回值以数组形式返回并包含 `options` 选项
* 必须返回 title/value/selected/disabled 字段用于构建 option 表单
* 上面字段也重新设置了栏目标题
* 可以在处理函数中使用 `$this->model` 获取模型对象

## 渲染视图 

使用处理器可以渲染出页面需要的表单元素。

```
<?php namespace App\Http\Controllers;

use App\ArticleCategory;
use App\Tables\ArticleCategory\ArticleCategoryHandle;

class HomeController extends Controller
{
    public function create(ArticleCategory $articleCategory)
    {
        $handle = new CategoryHandle($articleCategory);
        $html   = $handle->render();

        return view('home', compact('html'));
    }
}
```

系统根据 `App\Tables\、Category\Handle` 结合模型 `ArticleCategory` 生成页面视图。

当组件有数据时即编辑时，页面视图会自动添加上数据内容。

通过以下代码模板中调用，就构建出视图了。

```
{!! $html !!}
```





