## 介绍

根据注释生成表结构
> houdunren.com @ 向军大叔  

项目地址：https://github.com/houdunwang/laravel-view

## 安装

### 安装组件

```
composer require houdunwang/laravel-view:dev-master  
```

### 生成配置文件

```
php artisan vendor:publish --provider="Houdunwang\Structure\ServiceProvider"
```

组件会生成配置文件 `config/hd_tables.php` 文件

### 设置自动加载

修改 `composer.json` 设置psr-4 加载

```
...
"psr-4": {
	"App\\": "app/",
	"Hdcms\\": "hdcms"
}
...
```

### 发布视图

```
php artisan vendor:publish --provider="Houdunwang\Structure\ServiceProvider"
```

### 前端库

部分视图组件使用Vue.js 构建，需要安装前端库完成。

请参考文档  https://github.com/houdunwang/houdunren-vue-form  进行安装配置。

### 测试表

下面是用于后面讲解的栏目表结构

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

## 生成结构

### 数据表配置

组件主要根据表字段的 comment 注释属性操作，下面以 article_categories 表的title 字段设置进行说明。
```
 `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题|input',
```
上面是title字段的声明，COMMENT属性以 | 分隔 `中文描述|模板表单类型`
### 命令生成

```
php artisan hd:structure 模型 [目录] --force
```

> 模型必须输入目录为可选项，不设置目录使用配置项荐的值

下面是生成 `ArticleCategory` 模型表结构缓存。

```
php artisan hd:structure App\\ArticleCategory --force
```
系统将在 `app/Tables` 目录中创建以下文件结构
```
└── ArticleCategory
    ├── ArticleCategoryHandle.php  #表单值处理器
    └── structure.php #表结构缓存
```

如果表结构已经存在，可以加上 `--force` 参数强制生成

```
php artisan hd:structure App\\ArticleCategory --force
```

将表结构生成到指定的 `Modules/Blog` 目录中

```
php artisan hd:structure App\\ArticleCategory Modules/Blog/Tables
```

以上命令会在 Modules/Blog/Tables/ArticleCategory` 目录生成结构文件。

## 处理器

系统会为每个表生成 `Handle.php` 处理器文件，处理器用于对特殊字段的处理。

表单的名称、默认值系统自动进行处理，处理函数可以返回值替换默认，下面是字段说明：

| 字段名  | 字段说明                 | 系统字段 |
| ------- | ------------------------ | -------- |
| title   | 中文标题                 | 是       |
| value   | 默认值，自动从模型中获取 | 是       |
| name    | 字段变量名               | 是       |
| type    | 表单类型                 | 是       |
| options | 表单列表项               | 否       |

### 列表框

下面是获取 `article_categories` 表 `pid` 字段视图中显示列表值的操作掩饰。

* 字段处理函数定义在 `Headle.php` 类文件中
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
        return ['options'=>$data];;
    }
    ...
}
```

* 返回值以数组形式返回并包含 `options` 选项
* 必须返回 title/value/selected/disabled 字段

### 单张图片



## 调用 

```
<?php
namespace App\Http\Controllers;

use App\ArticleCategory;
use App\Tables\ArticleCategory\ArticleCategoryHandle;

class HomeController extends Controller
{
    public function index(ArticleCategory $articleCategory)
    {
        $handle = new ArticleCategoryHandle(ArticleCategory::find(1));
        $html   = $handle->render();

        return view('home', compact('html'));
    }
}
```

系统根据 `App\Tables\ArticleCategory\Handle` 结合模型 `ArticleCategory` 生成页面视图。

当组件有数据时即编辑时，页面视图会自动添加上数据内容。



