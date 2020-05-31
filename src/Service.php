<?php


namespace lirui\NoLimitTree;


use think\Db;
use think\Exception;

class Service extends Base
{
    public function __construct(string $tableName = "")
    {
        if (!empty($tableName)) self::$tableName = $tableName;
    }

    /**
     * @description 添加新节点
     * @param int $uid
     * @param int $pid
     * @return bool
     */
    public function add(int $uid = 0, int $pid = 0): bool
    {
        try {

            Db::startTrans();
            $pData = Db::table(self::$tableName)
                ->where(['uid' => $pid])
                ->find();

            if (!$pData) {
                $pid = 0;
                $lft = Db::table(self::$tableName)
                    ->where(['uid' => $pid])
                    ->find();
            }
            $lft = $pData['lft'];

            // 更新操作
            Db::table(self::$tableName)
                ->where('rgt', '>', $lft)
                ->setInc('rgt', 2);
            Db::table(self::$tableName)
                ->where('lft', '>', $lft)
                ->setInc('lft', 2);

            $data = [];
            $data['uid'] = $uid;
            $data['pid'] = $pid;
            $data['lft'] = $lft + 1;
            $data['rgt'] = $lft + 2;

            // 插入操作
            Db::table(self::$tableName)->insert($data);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * @description 查看某个节点下的所有子节点 包含自己这个节点
     * @param int $uid 节点id，默认是顶级节点0
     * @param string $limit 每次取出数量
     * @return array                所有节点的id（fid）
     */
    public function getSubChild(int $uid = 0, string $limit = ""): array
    {
        //排除自己? AND node.uid <> $uid
        $table = self::$tableName;
        $sql = "SELECT node.uid FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.uid = {$uid} ORDER BY node.lft";
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        $res = Db::query($sql);
        return $res;
    }

    /**
     * @param int $uid 节点id，默认是顶级节点0
     * @return int
     */
    public function countSubChild(int $uid = 0): int
    {
        $table = self::$tableName;
        $sql = "SELECT COUNT(node.uid) AS c FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.uid = {$uid} ORDER BY node.lft";
        $res = Db::query($sql);
        return $res[0]['c'];
    }

    public function getSubChildNoMe(int $uid = 0, string $limit = "")
    {
        //排除自己? AND node.uid <> $uid
        $table = self::$tableName;
        $sql = "SELECT node.uid FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.uid = {$uid} AND node.uid <> {$uid} ORDER BY node.lft";
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        $res = Db::query($sql);
        return $res;
    }

    public function countSubChildNoMe(int $uid = 0): int
    {
        //排除自己? AND node.uid <> $uid
        $table = self::$tableName;
        $sql = "SELECT COUNT(node.uid) AS c FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.uid = {$uid} AND node.uid <> {$uid} ORDER BY node.lft";
        $res = Db::query($sql);
        return $res[0]['c'];
    }

    /**
     * @description 获取所有叶子节点(最下面一级)
     * @return array   返回uid
     */
    public function getLeafChild(): array
    {
        $table = self::$tableName;
        $sql = "SELECT uid FROM {$table} WHERE rgt = lft + 1";
        $res = Db::query($sql);
        return $res;
    }

    /**
     * @description 获取某个节点的所有父级uid
     * @param int $uid
     * @return array 返回uid
     */
    public function getParentIds(int $uid = 0): array
    {
        $table = self::$tableName;
        if ($uid == 0) {
            return [0];
        }
        $sql = "SELECT parent.uid FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.uid = {$uid} ORDER BY parent.lft";
        $res = Db::query($sql);
        return $res;
    }

    /**
     * @description 获取某个节点下的节点 可添加深度条件
     * @param int $uid $uid 为第一级 0
     * @param int $depth 获取深度，默认返回所有
     * @param string $limit 每次取出多少个，默认为""，则取出所有
     * @return array
     */
    public function getChildDepth(int $uid = 0, int $depth = 0, string $limit = ""): array
    {
        $table = self::$tableName;
        $orderBy = "node.lft";
        if ($uid == 0) {
            $sql = "SELECT node.uid, (COUNT(parent.uid) - 1) AS depth FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.uid ORDER BY node.lft";
        } else {
            $sql = "SELECT node.uid, (COUNT(parent.uid) - (sub_tree.depth + 1)) AS depth FROM {$table} AS node,{$table} AS parent,{$table} AS sub_parent,
                (SELECT node.uid, (COUNT(parent.uid) - 1) AS depth FROM {$table} AS node, {$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt
                AND node.uid = {$uid} GROUP BY node.uid ORDER BY {$orderBy})
                AS sub_tree WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
                AND sub_parent.uid = sub_tree.uid GROUP BY node.uid";
            if ($depth > 0) {
                $sql .= " HAVING depth <= {$depth}";
            }
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        $res = Db::query($sql);
        return $res;
    }

    /**
     * @description 统计某个节点下的节点 可添加深度条件
     * @param int $uid $uid 为第一级 0
     * @param int $depth 获取深度，默认返回所有
     * @return int
     */
    public function countChildDepth($uid = 0, $depth = 0): int
    {
        $table = self::$tableName;
        if ($uid == 0) {
            $sql = "SELECT node.uid, (COUNT(parent.uid) - 1) AS depth FROM {$table} AS node,{$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.uid ORDER BY node.lft";
        } else {
            $sql = "SELECT node.uid, (COUNT(parent.uid) - (sub_tree.depth + 1)) AS depth FROM {$table} AS node,{$table} AS parent,{$table} AS sub_parent,
                (SELECT node.uid, (COUNT(parent.uid) - 1) AS depth FROM {$table} AS node, {$table} AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt
                AND node.uid = {$uid} GROUP BY node.uid)
                AS sub_tree WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
                AND sub_parent.uid = sub_tree.uid GROUP BY node.uid";
            if ($depth > 0) {
                $sql .= " HAVING depth <= {$depth}";
            }
        }
        $res = Db::query($sql);
        return count($res);
    }

    /**
     * 删除一个节点，同时删除这个节点下所有的子节点，谨慎使用
     * @param int $uid
     * @return bool
     */
    public function delete(int $uid = 0): bool
    {
        try {

            Db::startTrans();
            $data = Db::table(self::$tableName)
                ->where(['uid' => $uid])
                ->find();
            if ($data) {
                $lft = $data['lft'];
                $rgt = $data['rgt'];
                $width = $rgt - $lft + 1;
                Db::table(self::$tableName)
                    ->whereBetween('lft', $lft, $rgt)
                    ->delete();
                Db::table(self::$tableName)
                    ->where("rgt > {$rgt}")
                    ->setDec('rgt', $width);
                Db::table(self::$tableName)
                    ->where("lft > {$rgt}")
                    ->setDec('lft', $width);
            } else {
                throw new Exception('uid data is null');
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }
}