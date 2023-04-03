<?php

namespace tpext\webman;

use tpext\builder\inface\Auth;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;

/**
 * url鉴权
 */
class UrlCheck implements Auth
{
    /**
     * Undocumented function
     *
     * @param string $url
     * @return boolean
     */
    public static function checkUrl($url)
    {
        // 获取登录信息
        $admin = admin();
        if (!$admin) {
            return false;
        }

        // 当前管理员无角色
        $roles = $admin['roles'];
        if (!$roles) {
            return false;
        }

        // 角色没有规则
        $rules = Role::whereIn('id', $roles)->pluck('rules');
        $rule_ids = [];
        foreach ($rules as $rule_string) {
            if (!$rule_string) {
                continue;
            }
            $rule_ids = array_merge($rule_ids, explode(',', $rule_string));
        }
        if (!$rule_ids) {
            return false;
        }

        // 超级管理员
        if (in_array('*', $rule_ids)) {
            return true;
        }

        // 查询是否有当前控制器的规则
        $rule = Rule::where('href', $url)->whereIn('id', $rule_ids)->first();

        if (!$rule) {
            return false;
        }

        return true;
    }
}
