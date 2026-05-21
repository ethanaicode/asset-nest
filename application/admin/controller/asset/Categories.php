<?php

namespace app\admin\controller\asset;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Categories extends Backend
{

    /**
     * Categories模型对象
     * @var \app\admin\model\asset\Categories
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\asset\Categories;

        // 方便在首页显示分类
        $categoryList = $this->model->where('parent_id', 0)->column('name', 'id');
        $this->view->assign("categoryList", json_encode($categoryList, JSON_UNESCAPED_UNICODE));

    }

    /**
     * 新增
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $parentList = $this->model->field('id,name')
                ->where('parent_id', 0)
                ->order('id', 'asc')->select();
            $this->assign('parentList', $parentList);
        }
        return parent::add();
    }

    /**
     * 编辑
     *
     * @param string $ids
     */
    public function edit($ids = null)
    {
        if (!$this->request->isPost()) {
            $currentId = (int)$ids;
            $parentList = $this->model->field('id,name')
                ->where('parent_id', 0)
                ->where('id', '<>', $currentId)
                ->order('id', 'asc')
                ->select();
            $this->assign('parentList', $parentList);
        }
        return parent::edit($ids);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }
}
