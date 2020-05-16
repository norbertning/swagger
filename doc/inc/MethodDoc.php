<?php

/**
 * 方法文档解析
 *
 * @author sijie.li
 * @date 2017.9.5
 */
class MethodDoc
{
    /**
     * @var ReflectionMethod
     */
    private $method;

    /**
     * 方法名称
     * @var string
     */
    private $name;

    /**
     * 作者
     * @var string
     */
    private $author = '';

    /**
     * 版本
     * @var string
     */
    private $version = '';

    /**
     * 接口名称
     * @var string
     */
    private $title = '';

    /**
     * 接口描述
     * @var string
     */
    private $description = '';

    /**
     * 请求方式
     * @var string
     */
    private $http = 'GET';

    /**
     * 请求参数列表
     * @var array
     */
    private $params = [];

    /**
     * 返回字段说明
     * @var array
     */
    private $fields = [];

    /**
     * 输出说明
     * @var string
     */
    private $response = '';

    /**
     * 是否废弃
     * @var string
     */
    private $deleted = false;

    public function __construct(ReflectionMethod $method)
    {
        $this->method = $method;
        $this->name = $method->getName();
        $this->parse();
    }

    private function parse()
    {
        // \R 可以匹配 \r,\n,\r\n 三种换行符
        $comments = $this->method->getDocComment();
        $comments = preg_split('|\R|u', $comments);
        if (isset($comments[1])) {
            $this->title = ltrim($comments[1], "\t *");
        }
        $totalLine = count($comments) - 1;

        // 获取注释的变更记录
        $endLine = $this->method->getStartLine();
        $startLine = $endLine - $totalLine;

        exec("git blame  --after=2019-02-01 -L {$startLine},{$endLine} -s " . $this->method->getFileName(), $output);
        $res = [];
        foreach ($output as $item) {
            $item = explode("*", $item);
            if (count($item) < 2) {
                continue;
            }
            $res[] = $item[1];
        }
        $this->change = json_encode($res);
        unset($output, $res);

        $descEnd = false;
        for ($i = 2; $i < $totalLine; $i++) {
            $line = ltrim($comments[$i], "\t *");
            if (empty($line)) {
                continue;
            }
            // 变量解析
            if ($line[0] == '@') {
                $descEnd = true;
                $arr = explode(' ', substr($line, 1), 2);
                $var = $arr[0];
                $val = isset($arr[1]) ? trim($arr[1]) : '';
                switch ($var) {
                    case 'author': // 作者
                        $this->author = $val;
                        break;
                    case 'version': // 版本
                        $this->version = $val;
                        break;
                    case 'http': // 请求方式, 单行
                        $this->http = strtoupper($val) == 'POST' ? 'POST' : 'GET';
                        break;
                    case 'field': // 返回字段说明, 单行
                        $field = [
                            'name' => '',
                            'desc' => '',
                        ];
                        $val = explode('|', $val);
                        $val = array_map('trim', $val);
                        if (isset($val[0])) {
                            $field['name'] = $val[0];
                        }
                        if (isset($val[1])) {
                            $field['desc'] = $val[1];
                        }
                        $this->fields[] = $field;
                        break;
                    case 'response': // 返回说明, 多行
                        $response = '';
                        for ($i = $i + 1; $i < $totalLine; $i++) {
                            $line = ltrim($comments[$i], "\t *");
                            if (!empty($line) && $line[0] == '@') {
                                $i--;
                                break;
                            }
                            $response .= $line . "\n";
                        }
                        // 如果是json格式，进行美化格式化操作
                        if (is_array($decode = json_decode($response, true))) {
                            $response = json_encode($decode, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                        }
                        $this->response = trim($response);
                        break;
                    case 'params': // 参数列表说明
                        for ($i = $i + 1; $i < $totalLine; $i++) {
                            $line = ltrim($comments[$i], "\t *");
                            if (strlen($line) > 0 && $line[0] == '-') {
                                $arr = explode('|', substr($line, 1));
                                $param = [
                                    'name' => '',
                                    'type' => '',
                                    'desc' => '',
                                    'example' => '',
                                    'required' => 'Y',
                                ];
                                $arr = array_map('trim', $arr);
                                if (isset($arr[0])) {
                                    $param['name'] = $arr[0];
                                }
                                if (isset($arr[1])) {
                                    $param['type'] = $arr[1];
                                }
                                if (isset($arr[2])) {
                                    $param['desc'] = $arr[2];
                                }
                                if (isset($arr[3])) {
                                    $param['example'] = $arr[3];
                                }
                                if (isset($arr[4])) {
                                    $param['required'] = strtoupper(trim($arr[4])) == 'N' ? 'N' : 'Y';
                                }
                                $this->params[] = $param;
                            } else {
                                $i--;
                                break;
                            }
                        }
                        break;
                    case 'fields': // 返回字段说明
                        for ($i = $i + 1; $i < $totalLine; $i++) {
                            $line = ltrim($comments[$i], "\t *");
                            if (strlen($line) > 0 && $line[0] == '-') {
                                $arr = explode('|', substr($line, 1));
                                $field = [
                                    'name' => '',
                                    'desc' => '',
                                ];
                                $arr = array_map('trim', $arr);
                                if (isset($arr[0])) {
                                    $field['name'] = $arr[0];
                                }
                                if (isset($arr[1])) {
                                    $field['desc'] = $arr[1];
                                }
                                $this->fields[] = $field;
                            } else {
                                $i--;
                                break;
                            }
                        }
                        break;

                    case 'deleted': // 是否废弃
                        $this->deleted = true;
                        break;
                }
            } else {
                // 方法描述
                if (!$descEnd) {
                    $this->description .= $line;
                    continue;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}