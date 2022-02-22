<?php

namespace app<namespace>validate;

use czh9527\tp6curd\extend\ExtendValidate;

class <model>Validate extends ExtendValidate
{
    /**
     * 验证规则
     */
    protected $rule = <rule>;
    /**
     * 提示消息
     */
    protected $message = <attributes>;
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => <zds>,
        'edit' => <zds>,
    ];
}