<?php

require __DIR__ . '/MethodDoc.php';
require __DIR__ . '/ClassDoc.php';
require __DIR__ . '/Parsedown.php';

/**
 * 接口文档解析
 *
 * @author ningyuanhuo@163.com
 * @date 2020.04.21
 */
class SwaggerFileDoc
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $apiBaseUrl;

    /**
     * @var ClassDoc
     */
    private $classDoc;

    private $filePath;

    /**
     * @var array|MethodDoc
     */
    private $methodDocs = [];

    public function __construct($file, $filePath = 'controllers')
    {
        if (!is_string($file) || !is_file($file)) {
            throw new Exception('file not exists: ' . $file);
        }
        $this->filePath = $filePath;
        $this->file = $file;
        $controller = strtolower(substr($file, strpos($file, 'controllers/') + strlen('controllers/'), -14)) . '/';
        $this->apiBaseUrl = $controller;
        $this->parse();
    }

    private function parse()
    {
        include_once $this->file;
        $className = basename($this->file, '.php');
        $class = new \ReflectionClass($this->filePath . "\\{$className}");
        $this->classDoc = new ClassDoc($class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->isStatic()) {
                continue;
            }
            // 基类定义的接口忽略掉，不符合规范。良好的编码习惯很重要。
            if ($method->getDeclaringClass()->getName() != $class->getName()) {
                continue;
            }
            if ($method->getName()[0] != '_') {
                $this->methodDocs[] = new MethodDoc($method);
            }
        }
    }

    private function getMethodMd(MethodDoc $methodDoc, &$swagger)
    {
        $params = $methodDoc->getParams();

        $http = strtolower($methodDoc->getHttp());
        $swagger = [
            $http => [
                "tags" => [trim($this->apiBaseUrl, '/')],
                "summary" => $methodDoc->getTitle(),
                "consumes" => ["application/json"],
            ]
        ];


        if (!empty($params)) {
            $modelList = $this->getModelList();
            $swaggerParams = [];
            foreach ($params as $param) {
                // swagger文档组装
                $item = [
                    "in" => "query",
                    "name" => $param['name'],
                    "description" => $param['desc'],
                    "required" => $param['required'] != 'Y' ? false : true,
                    "type" => $param['type'],
                    "example" => $param['example'],
                ];

                if (!empty($modelList[strtolower($param['name'])])) {
                    $item['schema'] = [
                        'originalRef' => $param['name'],
                        '$ref' => '#/definitions/' . $param['name'],
                    ];
                }
                $swaggerParams[] = $item;
            }

            $swagger[$http]["parameters"] = $swaggerParams;
        }
        $defaultResponse = [
            "type" => "object",
            "properties" => [
                "code" => [
                    "description" => "错误码",
                    "type" => "int",
                    "example" => 0,
                ],
                "msg" => [
                    "description" => "错误信息",
                    "type" => "string",
                    "example" => "Success",
                ],
                "data" => [
                    "description" => "返回结果",
                    "type" => "object",
                    "example" => [],
                ],
            ]
        ];


        if (!empty($methodDoc->getResponse())) {
            $response = json_decode($methodDoc->getResponse(), true);
            if (!empty($response)) {
                $defaultResponse["properties"]["code"]["example"] = isset($response['code']) ? $response['code'] : '';
                $defaultResponse["properties"]["msg"]["example"] = isset($response['msg']) ? $response['msg'] : '';
                $defaultResponse["properties"]["data"]["example"] = isset($response['data']) ? $response['data'] : '';
            }
        }
        $swagger[$http]["responses"]["200"]["description"] = "OK";
        $swagger[$http]["responses"]["200"]["schema"] = $defaultResponse;
    }

    /**
     * 返回Html格式的API文档
     * @return string
     */
    public function getHtml()
    {
        $swaggerList = [];
        foreach ($this->methodDocs as $methodDoc) {
            if ($methodDoc instanceof MethodDoc) {
                $swagger = [];
                $this->getMethodMd($methodDoc, $swagger);
                // 处理方法名
                $name = lcfirst(str_replace('action', '', $methodDoc->getName()));
                $path = $this->apiBaseUrl == '/' ? $name : "/" . $this->apiBaseUrl . $name;
                $swaggerList[$path] = $swagger;

            }
        }

        return $swaggerList;
    }

    /**
     * @return array
     */
    private function getModelList()
    {
        /** 实体，用于返回或者对象参数 */
        $modelList = [
            "search" => [
                "type" => "object",
                "properties" => [
                    "item" => [
                        "description" => "查询字段",
                        "type" => "string",
                        "required" => true,
                        "example" => "keyword",
                    ],
                    "op" => [
                        "description" => "操作符",
                        "type" => "string",
                        "required" => true,
                        "enum" => [
                            "GT",
                            "GTE",
                            "LT",
                            "LTE",
                            "EQ",
                            "NE",
                            "IN",
                            "NOT_IN",
                            "LIKE",
                            "NOT_LIKE"
                        ],
                    ],
                    "val" => [
                        "description" => "查询值",
                        "required" => true,
                        "type" => "string",
                        "example" => "abc",
                    ],
                ]
            ],
        ];
        return $modelList;
    }
}
