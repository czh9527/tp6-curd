
            //删除<foreignTable>附表数据
            <foreignTable>::where('<foreignName>',$<tableName>)->delete();
