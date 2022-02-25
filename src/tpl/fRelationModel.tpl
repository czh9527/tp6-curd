
    /**
    * Notes: <foreigntable>附表:<foreignName>,依赖<table>主表:<tableName>
	* Author: <user>
    */
    public function <wayName>()
    {
        return $this->belongsTo(<table>::class,'<foreignName>','<tableName>');
    }
