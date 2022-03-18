
            //删除<foreignTable>附表数据-可能传过来数组用foreach通用
            <foreignTable>::where('<foreignName>','in',$<tableName>s)->delete();