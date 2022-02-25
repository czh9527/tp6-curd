# tp6-curd
基于thinkphp6的一键curd生成controller、model、validate

### 如何安装
```php
composer require czh9527/tp6-curd
```

### 如何使用
> php think curd  -p 模块名 -t table名
>
> 自动生成model,controller,validate,
> 支持hasOne和belongsTo的自动生成
> 
> 另外本地生成<表名.json>用于配合易文档创建传参


 
`-p` 模块名可选，比如你要生成到 `admin` 模块下，输入 `-p admin`
`-t` 表名，你要基于哪个表生成 curd，比如 `x_node`,则输入 `-t node`


