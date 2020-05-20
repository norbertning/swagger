# swagger
自制快速支持php生成swagger格式文件，支持swagger ui和yapi api使用，来试试吧，很好用哦~

<h1>自动化接口文档</h1>
<hr />
<p>直接通过解析控制器注释，生成接口文档的工具。用于解决人肉写接口文档的痛苦，快来试一下吧，释放你的写doc文档的痛苦哦～～～！</p>
<hr />


<p>
<b>注释参数说明：</b><br />
<table class="table">
<tbody>
    <tr><td>参数</td><td>描述</td><td>格式</td></tr>
    <tr><td>@http</td><td>http的方式</td><td>GET 或 POST</td></tr>
    <tr><td>@params</td><td>接口的参数描述，每行一个，使用 - 字符开头，格式为： - 参数名 | 类型 | 描述 | 参考值（非必填）| 是否必填项，默认是Y, 可以设置为N.</td><td> - page | int | 页码 | 1 | Y</td></tr>
    <tr><td>@response</td><td>表示插入一段code的代码，这样方便直接查看接口调用，一般是贴上返回代码的结果，方便前端进行mock数据</td><td>{"total": 100, "list": {...}}</td></tr>
    <tr><td>@fields</td><td>对返回的结果字段进行说明，每行使用 - 开头，格式为 - 字段名 | 描述</td><td> - total | 总数</td></tr>
    <tr><td>@author</td><td>作者信息</td><td>yuanhuo.ning</td></tr>
    <tr><td>@version</td><td>创建该接口时的项目版本</td><td>v4.3.2</td></tr>
    <tr><td>@deleted</td><td>是否废弃， 默认false</td><td>true</td></tr>
</tbody>
</table>
</p>
<p>
<b>接口参数中的类型说明：</b><br /><br />
参数类型使用以下名称进行描述，如果是数组类型，则在前面加上 <span style="color:red">[]</span> 表示，如：[]int。
<ul>
    <li>int，要求传入的是一个整数字符串。</li>
    <li>string，要求传入的是一个字符串。</li>
    <li>json，要求传入的是一个JSON字符串。</li>
    <li>file，要求传入的是一个上传文件。</li>
</ul>
</p>
<p>
示例：<br />
<pre>
/**
 * 接口作用（单行）
 *
 * 接口详细描述，多行。
 *
 * @http GET
 * @params
 *  - page | int | 页码 | 1 | Y
 *  - page_size | int | 分页大小 | 20 | N
 *  - search | json | 搜索条件,JSON结构 | {}
 * @response
 * {"code":0,"msg":"success","total":2,"data":{"list":[{"id":"1","name":"张三","address":"广州市天河区","age":40},{"id":"2","name":"李四","address":"上海浦东","age":28}]}}
 * 
 * @fields
 *  - total | 总数
 *  - list | 列表
 * @author ningyuanhuo
 * @version v1.0.0
 */
public function getList()
{
    ...
}   
</pre>
</p><br>


<p>
<h2>Nginx配置参考：</h2>
<pre>
server {
    listen       80;
    server_name  doc.dev.com;
    index index.html index.php;
    root  /usr/local/www/swagger/web;
    location /doc/ {
       root           /usr/local/www/swagger;
       try_files      $uri $uri/ /index.php?$args;
       fastcgi_pass   127.0.0.1:9000;
       include        fastcgi.conf;
       fastcgi_index  index.php;
       fastcgi_param  ENVIRONMENT development;
    }
    location / {
       try_files $uri $uri/ /index.php$is_args$args;
    }
    location ~ \.php$ {
       fastcgi_pass   127.0.0.1:9000;
       fastcgi_index  index.php;
       fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
       fastcgi_param  ENVIRONMENT    development;
       include        fastcgi.conf;
    }
    access_log  /usr/local/log/nginx/doc.dev.com_access.log;
    error_log  /usr/local/log/nginx/doc.dev.com.log;
}
</pre>

****激动人心的时刻来了，先通过http://doc.dev.com/doc/ 生成文档，再访问url: http://doc.dev.com/swagger-ui/doc.html 就可以看到效果了哦～～～～****

**注意：确保 web/swagger-ui/json/swagger_api.json文件有写的权限，以及nginx需要配置ENVIRONMENT变量**
</p>
