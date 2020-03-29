# lirui-no-limit-tree-tp5.1
基于php5.1开发 无限制Tree上下级关系绑定 可避免使用递归 


## 如何安装？
`1.安装thinkphp5.1 配置好数据库连接`

`2.composer require lirui/no-limit-tree-tp5.1`

`3.初始化安装表 php think no-limit-tree:init 默认表名：no_limit_tree 
如果需要自定义表 php think no-limit-tree:init table_name`
`测试完成后需要重新上线部署 也是运行此命令 会自动删除表 重新建立`

## 如何使用？
`使用默认表：$obj = new \lirui\NoLimitTree\Service()`

`自定义表：$obj = new \lirui\NoLimitTree\Service(table_name)`

`添加节点：$obj->add(uid, pid)`