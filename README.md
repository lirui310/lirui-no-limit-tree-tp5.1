# lirui-no-limit-tree-tp5.1
基于php5.1开发 无限制Tree上下级关系绑定 可避免使用递归 

## 如何安装？
`1.安装thinkphp5.1 配置好数据库连接`

`2.composer require lirui/no-limit-tree-tp5.1 dev-master`

`3.初始化安装表 php think no-limit-tree:init 默认表名：no_limit_tree`

`如果需要自定义表 php think no-limit-tree:init table_name`

`测试完成后需要重新上线部署 也是运行此命令 会自动删除表 重新建立`

## 如何使用？
`使用默认表：$obj = new \lirui\NoLimitTree\Service()`

`自定义表：$obj = new \lirui\NoLimitTree\Service(table_name)`

`添加节点：$obj->add(uid, pid) 返回true|false`

`查看某个节点下的所有子节点 包含自己这个节点：$obj->getSubChild(uid, limit) limit 可选：0,10 自己分页`

`统计某个节点下的所有子节点 包含自己这个节点：$obj->countSubChild(uid) 返回总数量`

`查看某个节点下的所有子节点 不包含自己这个节点：$obj->getSubChildNoMe(uid, limit) limit 可选：0,10 自己分页`

`统计某个节点下的所有子节点 不包含自己这个节点：$obj->countSubChildNoMe(uid) 返回总数量`

`获取所有叶子节点(最下面一级)：$obj->getLeafChild() 最下一层的所有节点uid`

`获取某个节点的所有父级uid 包含自己这个节点和系统预设顶级节点uid=0：$obj->getParentIds()`

`获取某个节点下的节点 可添加深度条件(可选) 包含自己这个节点：$obj->getChildDepth(uid, depth, limit) 可选：0,10 自己分页`

`统计某个节点下的节点 可添加深度条件(可选) 包含自己这个节点：$obj->countChildDepth(uid, depth) 返回总数量`

`删除一个节点 会同时删除这个节点下所有子节点 谨慎操作：$obj->delete(uid)`

## 有问题请联系
QQ：1950767658

微信：wxmm686800

承接各种系统咨询和开发