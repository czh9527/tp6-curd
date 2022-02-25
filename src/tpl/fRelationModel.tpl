
    /**
    * Notes: 附表:<foreignTable>的<foreignName>字段,依赖主表:<table>的<tableName>字段
	* Author: <user>
    */
    public function <wayName>()
    {
        return $this->belongsTo(<table>::class,'<foreignName>','<tableName>');
    }
