<?php
/**
 * Created by PhpStorm.
 * User: ningyuanhuo@163.com
 * Date: 2020/5/16
 * Time: 10:08
 */

namespace controllers;


class User
{
    /**
     * 获取用户列表
     *
     * @author ningyuanhuo
     * @version v1.0.0
     *
     * @http GET
     * @params
     *  - page | int | 当前页码 |  1 | Y
     *  - page_size | int | 每页显示记录数 |  20 | Y
     * @response
     * {"code":0,"msg":"success","data":{"list":[{"id":"1","name":"张三","address":"广州市天河区","age":40},{"id":"2","name":"李四","address":"上海浦东","age":28}]}}
     */
    public function getUserList()
    {

    }

    /**
     * 新增用户
     *
     * @http POST
     * @params
     *  - name | string | 用户名 |  1 | Y
     *  - address | string | 住址 |  广州市天河区 | Y
     *  - age | int | 年龄 |  20 | N
     * @response
     * {"code":0,"msg":"success","rows":"1"}
     */
    public function addUser()
    {

    }

    /**
     * 更新用户
     *
     * @http POST
     * @params
     *  - id | int | 用户ID |  1 | Y
     *  - name | string | 用户名 |  1 | Y
     *  - address | string | 住址 |  广州市天河区 | Y
     * @response
     * {"code":0,"msg":"success","rows":"1"}
     */
    public function updateUser()
    {

    }
}