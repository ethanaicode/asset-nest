<?php

namespace app\admin\model\asset;

use think\Model;


class Plans extends Model
{

    

    

    // 表名
    protected $name = 'asset_plans';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'billing_cycle_text'
    ];

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            self::normalizePlanFields($row);
        });
        self::beforeUpdate(function ($row) {
            self::normalizePlanFields($row);
        });
    }
    

    
    public function getTypeList()
    {
        return ['one_time' => __('One_time'), 'recurring' => __('Recurring')];
    }

    public function getBillingCycleList()
    {
        return ['monthly' => __('Monthly'), 'yearly' => __('Yearly')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getBillingCycleTextAttr($value, $data)
    {
        $value = $value ?: ($data['billing_cycle'] ?? '');
        $list = $this->getBillingCycleList();
        return $list[$value] ?? '';
    }

    protected static function normalizePlanFields($row)
    {
        $type = (string)$row->getAttr('type');
        $billingCycle = (string)$row->getAttr('billing_cycle');

        if ($type === 'one_time') {
            $row->setAttr('recurring_price', null);
            $row->setAttr('billing_cycle', null);
            $row->setAttr('billing_day', null);
            $row->setAttr('start_date', null);
            $row->setAttr('end_date', null);
            return;
        }

        if ($type === 'recurring') {
            $row->setAttr('one_time_price', null);
            $row->setAttr('purchase_date', null);
            if ($billingCycle !== 'monthly') {
                $row->setAttr('billing_day', null);
            }
        }
    }




}
