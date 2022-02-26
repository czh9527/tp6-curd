
    /**
    * 自定义规则-check<table>// TODO 是否有<table>控制器，否则报错
    */
    protected function check<table>($value)
    {
        return \app\admin\model\<table>::find($value)!=null?true:'<foreignName>不存在';
    }