<?php

namespace app\admin\controller\asset;

use app\common\controller\Backend;
use Exception;
use fast\Tree;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Items extends Backend
{

    // 开启验证器并按场景(add/edit)验证
    protected $modelValidate = true;
    protected $modelSceneValidate = true;

    /**
     * Items模型对象
     * @var \app\admin\model\asset\Items
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\asset\Items;
        $this->view->assign("typeList", $this->model->getTypeList());
        $categoriesModel = new \app\admin\model\asset\Categories;
        $categoryRows = $categoriesModel->field('name,id,parent_id')->select();
        $tree = Tree::instance();
        $tree->init($categoryRows, 'parent_id');
        $treeList = $tree->getTreeList($tree->getTreeArray(0), 'name');
        
        // 从 getTreeList 返回的 JSON 字符串数组重建格式化的分类列表（保留原顺序）
        $categoryList = [];
        foreach ($treeList as $item) {
            $item = str_replace('&nbsp;', ' ', $item);
            $decoded = json_decode($item, true);
            if ($decoded && isset($decoded['id'], $decoded['name'])) {
                $categoryList[] = [
                    'id' => (int)$decoded['id'],
                    'name' => trim($decoded['name'])
                ];
            }
        }
        
        $this->view->assign("categoryList", json_encode($categoryList, JSON_UNESCAPED_UNICODE));

        $plansModel = new \app\admin\model\asset\Plans;
        $this->view->assign("planTypeList", $plansModel->getTypeList());
        $this->view->assign("billingCycleList", $plansModel->getBillingCycleList());
        $this->view->assign("currencyList", (array)config('asset.currency'));

        $paymentmethodsModel = new \app\admin\model\asset\Paymentmethods;
        $paymentMethodList = $paymentmethodsModel->order('id', 'desc')->column('name', 'id');
        $this->view->assign("paymentMethodList", $paymentMethodList);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 列表
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);

        // 转为数组并挂载 plan 数据
        $items = array_map(function ($item) { return $item->toArray(); }, $list->items());

        $itemIds = array_filter(array_column($items, 'id'));
        $plans = [];
        if ($itemIds) {
            $planRows = Db::name('asset_plans')
                ->whereIn('item_id', $itemIds)
                ->select();
            foreach ($planRows as $plan) {
                $plans[$plan['item_id']] = $plan;
            }
        }

        $today = date('Y-m-d');
        $todayTs = strtotime($today);

        foreach ($items as &$item) {
            $plan = $plans[$item['id']] ?? null;
            if ($plan) {
                $item['plan_type']          = $plan['type'];
                $item['plan_currency']      = $plan['currency'];
                $item['plan_billing_cycle'] = $plan['billing_cycle'] ?: null;
                if ($plan['type'] === 'one_time') {
                    $item['plan_price']         = $plan['one_time_price'];
                    $item['next_billing_date']  = null;
                } else {
                    $item['plan_price']         = $plan['recurring_price'];
                    $item['next_billing_date']  = $this->calcNextBillingDate($plan, $todayTs);
                }
            } else {
                $item['plan_type']          = null;
                $item['plan_currency']      = null;
                $item['plan_price']         = null;
                $item['plan_billing_cycle'] = null;
                $item['next_billing_date']  = null;
            }
        }
        unset($item);

        // 重新按照账单日进行排序，如果没有账单日都放到后面去
        usort($items, function ($a, $b) {
            if ($a['next_billing_date'] === null && $b['next_billing_date'] === null) {
                return 0;
            }
            if ($a['next_billing_date'] === null) {
                return 1;
            }
            if ($b['next_billing_date'] === null) {
                return -1;
            }
            return strcmp($a['next_billing_date'], $b['next_billing_date']);
        });

        $result = ['total' => $list->total(), 'rows' => $items];
        return json($result);
    }

    /**
     * 根据 plan 数据计算下次账单日
     *
     * @param array $plan
     * @param int   $todayTs
     * @return string|null
     */
    protected function calcNextBillingDate(array $plan, int $todayTs): ?string
    {
        if ($plan['billing_cycle'] === 'monthly') {
            $billingDay = (int)$plan['billing_day'];
            if ($billingDay < 1) {
                return null;
            }
            $year  = (int)date('Y');
            $month = (int)date('m');

            $day = min($billingDay, (int)date('t', mktime(0, 0, 0, $month, 1, $year)));
            $thisTs = mktime(0, 0, 0, $month, $day, $year);
            if ($thisTs >= $todayTs) {
                return date('Y-m-d', $thisTs);
            }

            $nextMonth = $month === 12 ? 1 : $month + 1;
            $nextYear  = $month === 12 ? $year + 1 : $year;
            $day = min($billingDay, (int)date('t', mktime(0, 0, 0, $nextMonth, 1, $nextYear)));
            return date('Y-m-d', mktime(0, 0, 0, $nextMonth, $day, $nextYear));
        }

        if ($plan['billing_cycle'] === 'yearly') {
            if (empty($plan['start_date'])) {
                return null;
            }
            $startTs    = strtotime($plan['start_date']);
            $startMonth = (int)date('m', $startTs);
            $startDay   = (int)date('d', $startTs);
            $curYear    = (int)date('Y');

            $thisYearTs = mktime(0, 0, 0, $startMonth, $startDay, $curYear);
            if ($thisYearTs >= $todayTs) {
                return date('Y-m-d', $thisYearTs);
            }
            return date('Y-m-d', mktime(0, 0, 0, $startMonth, $startDay, $curYear + 1));
        }

        return null;
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('planRow', $this->buildPlanViewData());
            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $planData = $this->extractPlanData($params);
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }

        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }

            $result = $this->model->allowField(true)->save($params);
            if ($result === false) {
                throw new Exception(__('No rows were inserted'));
            }

            $this->saveItemPlan((int)$this->model->id, $planData);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success();
    }

    /**
     * 编辑
     *
     * @param int|null $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }

        $plansModel = new \app\admin\model\asset\Plans;
        $planRow = $plansModel->where('item_id', $row['id'])->order('id', 'desc')->find();

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $this->view->assign('planRow', $this->buildPlanViewData($planRow ? $planRow->toArray() : []));
            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $planData = $this->extractPlanData($params);
        $params = $this->preExcludeFields($params);

        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }

            $result = $row->allowField(true)->save($params);
            if ($result === false) {
                throw new Exception(__('No rows were updated'));
            }

            $this->saveItemPlan((int)$row['id'], $planData, $planRow);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success();
    }

    /**
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }

    /**
     * 从 row 参数中拆分 plan 数据
     *
     * @param array $params
     * @return array
     */
    protected function extractPlanData(&$params)
    {
        $planData = [];
        if (isset($params['plan']) && is_array($params['plan'])) {
            $planData = $params['plan'];
        }
        unset($params['plan']);
        return $planData;
    }

    /**
     * 根据提交内容判断是否需要保存计划
     *
     * @param array $planData
     * @return bool
     */
    protected function shouldSavePlan(array $planData)
    {
        if (!empty($planData['has_plan'])) {
            return true;
        }

        foreach (['one_time_price', 'purchase_date', 'recurring_price', 'billing_day', 'start_date', 'end_date', 'default_payment_method_id'] as $field) {
            if (isset($planData[$field]) && $planData[$field] !== '' && $planData[$field] !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * 保存条目的价格计划
     *
     * @param int $itemId
     * @param array $planData
     * @param \app\admin\model\asset\Plans|null $planRow
     * @throws ValidateException
     */
    protected function saveItemPlan($itemId, array $planData, $planRow = null)
    {
        if (!$this->shouldSavePlan($planData)) {
            return;
        }

        $payload = [
            'item_id'                   => $itemId,
            'type'                      => (string)($planData['type'] ?? 'one_time'),
            'currency'                  => (string)($planData['currency'] ?? 'CNY'),
            'default_payment_method_id' => $planData['default_payment_method_id'] ?? null,
            'one_time_price'            => $this->normalizeNullableField($planData['one_time_price'] ?? null),
            'purchase_date'             => $this->normalizeNullableField($planData['purchase_date'] ?? null),
            'recurring_price'           => $this->normalizeNullableField($planData['recurring_price'] ?? null),
            'billing_cycle'             => $this->normalizeNullableField($planData['billing_cycle'] ?? null),
            'billing_day'               => $this->normalizeNullableField($planData['billing_day'] ?? null),
            'start_date'                => $this->normalizeNullableField($planData['start_date'] ?? null),
            'end_date'                  => $this->normalizeNullableField($planData['end_date'] ?? null),
        ];

        $validator = new \app\admin\validate\asset\Plans();
        $scene = $planRow ? 'edit' : 'add';
        if (!$validator->scene($scene)->check($payload)) {
            throw new ValidateException($validator->getError());
        }

        if ($planRow) {
            $planRow->allowField(true)->save($payload);
            return;
        }

        $plansModel = new \app\admin\model\asset\Plans;
        $plansModel->allowField(true)->save($payload);
    }

    /**
     * 构造 items 页面中 plan 区块的默认值
     *
     * @param array $planRow
     * @return array
     */
    protected function buildPlanViewData(array $planRow = [])
    {
        $currencyList = (array)config('asset.currency');
        $defaultCurrency = $currencyList ? reset($currencyList) : 'CNY';

        $defaults = [
            'has_plan'                  => empty($planRow) ? 0 : 1,
            'type'                      => 'one_time',
            'currency'                  => $defaultCurrency,
            'one_time_price'            => '',
            'purchase_date'             => date('Y-m-d'),
            'recurring_price'           => '',
            'billing_cycle'             => 'monthly',
            'billing_day'               => '',
            'start_date'                => date('Y-m-d'),
            'end_date'                  => date('Y-m-d'),
            'default_payment_method_id' => '',
        ];

        return array_merge($defaults, $planRow);
    }

    /**
     * 将空字符串归一化为 null
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalizeNullableField($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value === '' ? null : $value;
    }
}
