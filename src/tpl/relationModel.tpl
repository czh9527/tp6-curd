
    /**
    * Notes: <table>主表:<tableName>,关联<foreignTable>附表:<foreignName>
	* Author: <user>
    */
    public function <wayName>()
    {
        return $this->hasOne(<foreignTable>::class,'<foreignName>','<tableName>');
    }
