## 关于

UI builder for webman

## 安装

`composer require ichynul/builder-man`

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
            
            $form->fill(Admin::where('id', 1)->first());//测试使用laravel模型

            return $builder;
        } else {
            $data = request()->only([
                'avatar',
                'nickname',
                'mobile',
                'email',
            ]);

            Admin::where('id', 1)->update($data);

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

### License
MIT