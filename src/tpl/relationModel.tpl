
    /**
    * Notes: 主表:<table>的<tableName>字段,关联附表:<foreignTable>的<foreignName>字段
    */
    public function <wayName>()
    {
        return $this->hasOne(<foreignTable>::class,'<foreignName>','<tableName>');
    }
