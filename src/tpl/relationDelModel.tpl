
            //删除<foreignTable>附表数据-可能传过来数组用foreach通用
            foreach($<tableName>s as $key => $value)
            {
                <foreignTable>::where('<foreignName>',$value)->delete();
            }