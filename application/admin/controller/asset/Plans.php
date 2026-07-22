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
        $this->view->assign("currencyListJson", json_encode(config('asset.currency'), JSON_UNESCAPED_UNICODE));

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
     * Selectpage搜索 - 为每个 plan 生成可读的显示标签
     *
     * @internal
     */
    public function selectpage()
    {
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

        $word = (array)$this->request->request("q_word/a");
        $page = $this->request->request("pageNumber");
        $pagesize = $this->request->request("pageSize");
        $andor = $this->request->request("andOr", "and", "strtoupper");
        $orderby = (array)$this->request->request("orderBy/a");
        $field = $this->request->request("showField");
        $primarykey = $this->request->request("keyField");
        $primaryvalue = $this->request->request("keyValue");
        $searchfield = (array)$this->request->request("searchField/a");
        $custom = (array)$this->request->request("custom/a");
        $istree = $this->request->request("isTree", 0);

        if ($istree) {
            $word = [];
            $pagesize = 999999;
        }

        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'id';

        // 构建 where 条件
        if ($primaryvalue !== null) {
            $where = ['id' => ['in', $primaryvalue]];
            $pagesize = 999999;
        } else {
            $where = function ($query) use ($word, $andor, $searchfield, $custom) {
                // Plans 通过 item.name 或 item.id、type、price 来搜索
                $logic = $andor == 'AND' ? '&' : '|';
                $word = array_filter(array_unique($word));

                if (count($word) > 0) {
                    $query->where(function ($query) use ($word, $logic) {
                        foreach ($word as $idx => $item) {
                            $op = ($idx === 0) ? 'where' : 'whereOr';
                            $query->{$op}(function ($q) use ($item) {
                                // 支持按 item 名称、plan 类型、价格搜索
                                $q->whereIn('item_id', function ($subQ) use ($item) {
                                    $subQ->name('asset_items')
                                        ->where('name', 'like', "%{$item}%")
                                        ->field('id');
                                })
                                ->whereOr('type', 'like', "%{$item}%")
                                ->whereOr('currency', '=', strtoupper($item))
                                ->whereOr('recurring_price', '=', $item)
                                ->whereOr('one_time_price', '=', $item);
                            });
                        }
                    });
                }

                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }
            };
        }

        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            // Plans 表没有 name 字段，如果 order 中包含 name 则改为按 id 排序
            if (!empty($order)) {
                if (isset($order['name'])) {
                    $order['id'] = $order['name'];
                    unset($order['name']);
                }
                if (!empty($order)) {
                    $this->model->order($order);
                }
            }

            $datalist = $this->model->where($where)
                ->page($page, $pagesize)
                ->select();

            // 从 items 表获取关联的项目名称
            $itemIds = array_unique(array_filter(array_column($datalist, 'item_id')));
            $itemNames = [];
            if ($itemIds) {
                $itemNames = \think\Db::name('asset_items')
                    ->whereIn('id', $itemIds)
                    ->column('name', 'id');
            }

            foreach ($datalist as $index => $item) {
                $itemName = $itemNames[$item['item_id']] ?? '(Unknown)';
                $typeText = $this->model->getTypeList()[$item['type']] ?? $item['type'];
                
                // 生成可读的显示标签
                if ($item['type'] === 'one_time') {
                    $displayLabel = "{$itemName} - {$typeText} ({$item['currency']} {$item['one_time_price']})";
                } else {
                    $cycleText = ($item['billing_cycle'] === 'yearly') ? '年' : '月';
                    $displayLabel = "{$itemName} - {$typeText}({$cycleText}) ({$item['currency']} {$item['recurring_price']}/{$cycleText})";
                }

                $result = [
                    'id'   => $item['id'],
                    'name' => $displayLabel,
                ];
                $result = array_map("htmlentities", $result);
                $list[] = $result;
            }
        }

        return json(['total' => $total, 'list' => $list]);
    }
}
