<?php


namespace lirui\NoLimitTree;


use think\Db;

class Init extends Base
{

    public static function init(string $tableName = ''): bool
    {
        // 如果没有传入参数 使用默认表
        if (empty($tableName)) $tableName = self::$tableName;

        $result = false;
        Db::query("DROP TABLE IF EXISTS `{$tableName}`");
        Db::query("CREATE TABLE `{$tableName}` (
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $sql = "INSERT INTO `{$tableName}` (uid,pid,lft,rgt) VALUES (0,0,1,2)";
        $res = Db::query($sql);
        if ($res !== false) $result = true;
        return $result;
    }
}