<?php

namespace app\admin\model\asset;

use think\Model;


class Paymentmethods extends Model
{

    

    

    // 表名
    protected $name = 'asset_payment_methods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['credit_card' => __('Credit_card'), 'debit_card' => __('Debit_card'), 'cash' => __('Cash'), 'bank' => __('Bank')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }




}
