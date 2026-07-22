<?php

namespace app\admin\controller\asset;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Plans extends Backend
{

    // 开启验证器并按场景(add/edit)验证
    protected $modelValidate = true;
    protected $modelSceneValidate = true;

    /**
     * Plans模型对象
     * @var \app\admin\model\asset\Plans
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\asset\Plans;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("billingCycleList", $this->model->getBillingCycleList());
        // 定义常用的货币列表，方便在表单中选择
        $this->view->assign("currencyList", config('asset.currency'));

        $itemsModel = new \app\admin\model\asset\Items;
        $itemList = $itemsModel->order('id', 'desc')->column('name', 'id');
        $this->view->assign("itemListJson", json_encode($itemList, JSON_UNESCAPED_UNICODE));

        $paymentmethodsModel = new \app\admin\model\asset\Paymentmethods;
        $paymentMethodList = $paymentmethodsModel->order('id', 'desc')->column('name', 'id');
        $this->view->assign("paymentMethodList", $paymentMethodList);
        $this->view->assign("paymentMethodListJson", json_encode($paymentMethodList, JSON_UNESCAPED_UNICODE));
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
