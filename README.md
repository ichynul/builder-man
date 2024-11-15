# 关于

UI builder for webman

## 2024-11-15 升级2.0版本

### 与1.0版本的不同

- 简化代码，去除了与 `webman/admin`相关的依赖代码，增强兼容性。

- `composer.json`中移除了对`ichynul/tpextbuilder`UI的依赖，有两套UI可供选择，改为按需选择安装。

- UI框架的区别：tpextbuilder基于bootstrap4，tpext-tinyvue基于tinyvue。

## 安装webman

https://www.workerman.net/doc/webman/install.html

## 安装本扩展

安装UI builder扩展（二选一）：

`composer require ichynul/tpextbuilder:^3.9.1`

或者

`composer require ichynul/tpext-tinyvue:^5.0.1`

安装本扩展

`composer require ichynul/builder-man:^2.0.1`

会自动安装相关依赖：`tp-orm`、`tp-cahce`、`tp-template`

## 配置

- 关闭控制器复用: `/config/app.php`,`'controller_reuse' => false`

- 配置数据库: `/config/thinkorm.php`

## 使用

运行webman

### 代码编写

模型使用`tp-orm`，`laravel-orm`理论上也支持。

## 文档

<https://gxzrnxb27j.k.topthink.com/@tpext-docs/about.html>

## DEMO

简单使用，一个表单：

```php

<?php

namespace app\controller;

use support\Request;
use tpext\builder\common\Builder;
use think\Controller;
use plugin\admin\app\model\Admin;

class Index extends Controller
{
    public function index(Request $request)
    {
        $builder = Builder::getInstance('builder', '测试');
        if (request()->method() == 'GET') {
            $form = $builder->form();
            $form->image('avatar', '头像')->thumbSize(50, 50);
            $form->text('username', '账号')->required();
            $form->text('nickname', '昵称')->required();
            $form->text('mobile', '手机号');
            $form->text('email', '邮件');
            
            $form->fill(['username' => 'admin111']);//填充数据

            return $builder;
        } else {
            $data = request()->only([
                'avatar',
                'nickname',
                'mobile',
                'email',
            ]);

            //更新数据...

            $this->success('成功，数据是:' . json_encode($data));
        }
    }
}
```

进阶使用，配合`tpext\builder\traits\actions\*` traits实现CRUD。(`laravel-orm`写法不兼容)

```php
<?php

namespace app\admin\controller;

use think\Controller;
use app\common\model\MemberLevel as LevelModel;
use tpext\builder\traits\actions;

class Memberlevel extends Controller
{
    //引入控制器动作
    use actions\HasBase;//基础，必须
    use actions\HasIndex;//列表
    use actions\HasAdd;//添加
    use actions\HasEdit;//编辑
    use actions\HasView;//查看
    use actions\HasDelete;//删除

    /**
     * Undocumented variable
     *
     * @var LevelModel
     */
    protected $dataModel;

    protected function initialize()
    {
        $this->dataModel = new LevelModel;
        $this->pageTitle = '代理等级';
        $this->sortOrder = 'level asc';
        $this->selectSearch = 'name';
    }

    protected function filterWhere()
    {
        $searchData = request()->get();

        $where = [];

        if (!empty($searchData['name'])) {
            $where[] = ['name', 'like', '%' . $searchData['name'] . '%'];
        }

        return $where;
    }

    /**
     * 构建搜索
     *
     * @return void
     */
    protected function buildSearch()
    {
        $search = $this->search;
        $search->text('name', '名称', 3)->maxlength(20);
    }

    /**
     * 构建表格
     *
     * @return void
     */
    protected function buildTable(&$data = [])
    {
        $table = $this->table;

        $table->show('id', 'ID');
        $table->text('name', '名称')->autoPost();
        $table->text('level', '等级');
        $table->show('member_count', '人数统计');
        $table->show('description', '描述');
        $table->show('create_time', '添加时间');
        $table->show('update_time', '更新时间');

         $table->getActionbar()
            ->btnEdit()
            ->btnView()
            ->btnDelete();
    }

    /**
     * 构建表单
     *
     * @param boolean $isEdit
     * @param array $data
     */
    protected function buildForm($isEdit, &$data = [])
    {
        $form = $this->form;

        $form->text('name', '名称');
        $form->number('level', '等级');
        $form->textarea('description', '描述');

        if ($isEdit) {
            $form->show('member_count', '人数统计');
            $form->show('create_time', '添加时间');
            $form->show('update_time', '更新时间');
        }
    }

    /**
     * 保存数据
     *
     * @param integer $id
     * @return void
     */
    private function save($id = 0)
    {
        $data = request()->only([
            'name',
            'level',
            'description',
        ]);

        $result = $this->validate($data, [
            'name|名称' => 'require',
            'level|等级' => 'require|number'
        ]);

        if (true !== $result) {

            $this->error($result);
        }

        return $this->doSave($data, $id);
    }
}

```

### 其他说明

#### 语言

语言默认为英文，如需切换语言：在`config\plugin\builder\man\lang.php`中

```php
'default_lang'    => 'zh-cn',
```

#### 上传文件、鉴权

需要对上传文件，选择文件，导入文件等功能进行定制，可以参考以下代码：

```php

use tpext\builder\common\Module;
use tpext\builder\common\Builder;

//代码可以放在基础控制器的构造函数中、或中间件中

public function setup(){

    Module::getInstance()->setUploadUrl('/your/upload/url');//默认为：admin/upload/upfiles
    Module::getInstance()->setChooseUrl('/your/choose/url');//默认为：admin/attachment/index
    Module::getInstance()->setImportUrl('/your/import/url');//默认为：admin/import/page

    //代码逻辑 可参考 tpextbuilder | tpext-tinyvue 中 controller/admin 目录下的以下文件：
    // Attachment.php | Import.php | Upload.php

    Builder::auth('yourauthclass');//设置权限验证类，需要实现接口：\tpext\builder\inface\Auth。
    Builder::aver('1.0.1');//资源版本号，用于控制更新。依赖的UI库可能会更新一些静态资源，修改版本号可以应用最新资源。
}

```

### License

MIT