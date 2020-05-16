<?php
/**
 * Created by PhpStorm.
 * User: ningyuanhuo@163.com
 * Date: 2020/5/16
 * Time: 10:11
 */

namespace controllers;


class Car
{
    /**
     * 获取详情
     *
     * @author ningyuanhuo
     * @version v1.0.0
     *
     * @http GET
     * @params
     *  - id | int | id |  1 | Y
     * @response
     * {"code":0,"msg":"success","data":{"id":"1","name":"宝马","price":1000000}}
     *
     * @fields
     *  - name | 名称
     *  - price | 价格
     *
     */
    public function getCarInfo()
    {

    }

    /**
     * 新增
     *
     * @http POST
     * @params
     *  - name | string | 名称 |  宝马 | Y
     *  - price | float | 价格 |  1000000 | Y
     * @response
     * {"code":0,"msg":"success","rows":"1"}
     */
    public function addCar()
    {

    }

    /**
     * 更新
     *
     * @http POST
     * @params
     *  - id | int | id |  1 | Y
     *  - name | string | 名称 |  奔驰 | Y
     * @response
     * {"code":0,"msg":"success","rows":"1"}
     */
    public function updateCar()
    {

    }
}