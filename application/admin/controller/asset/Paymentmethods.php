<?php

namespace app\admin\controller\asset;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Paymentmethods extends Backend
{

    /**
     * Paymentmethods模型对象
     * @var \app\admin\model\asset\Paymentmethods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\asset\Paymentmethods;
        $this->view->assign("typeList", $this->model->getTypeList());

        // 定义服务组织列表，方便在表单中选择
        $this->view->assign("providerList", config('asset.provider'));
        $this->view->assign("providerListJson", json_encode(config('asset.provider'), JSON_UNESCAPED_UNICODE));

        // 定义常用的货币列表，方便在表单中选择
        $this->view->assign("currencyList", config('asset.currency'));
        $this->view->assign("currencyListJson", json_encode(config('asset.currency'), JSON_UNESCAPED_UNICODE));
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
