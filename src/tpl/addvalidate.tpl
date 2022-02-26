
    /**
    * 自定义规则-check<table>
    */
    protected function check<table>($value)
    {
        return \app\admin\model\<table>::find($value)!=null?true:'<foreignName>不存在';
    }