
> EasySwoole-Chat 是基于EasySwoole与layim 的聊天应用

> 此项目仅供学习交流，请勿用作商业用途 

> 演示地址（http://eschat.qiyewei.cn部署中）

-   此项目是基于 EasySwoole V3.3.7 作为服务端，easySwoole是一款高度封装了swoole拓展而依旧保持swoole原有特性的一个高性能异步框架，旨在提供一个高效、快速、优雅的框架给php开发者。
所以在此之前，你要熟悉 swoole、EasySwoole、、还有将他们完美结合的 `EasySwoole`<https://www.easyswoole.com>
-   前端部分是采用 layui, 在此郑重说明，layui 中的 im 部分 `layim` 并不开源，仅供交流学习，请勿将此项目中的 layim 用作商业用途。
-   本demo有助于了解EasySwoole的入门和websocket的在业务中的应用，代码没有经过正规的测试和封装，基本只达到实现功能而已，不可把服务端的逻辑用于生产环境。
# 基础运行环境
-   保证 **PHP** 版本大于等于 **7.2**
-   保证 **Swoole** 拓展版本大于等于 **4.4.0**
-   需要 **pcntl** 拓展的任意版本
-   使用 **Linux** 操作系统
-   使用 **Composer** 作为依赖管理工具


## 安装

-   执行安装命令 `git clone https://github.com/tianming01/EasySwooleChat.git` 将项目克隆到本地
-   `composer update` 
-   导入 sql，项目根目录下有个 `eschat.sql` 文件，将该 sql 文件导入数据库即可
-   修改`dev.php` 文件，配置mysql/redis等参数
-   配置nginx代理
```
server {
    root /data/wwwroot/chat;
    server_name eschat.qiyewei.cn;
    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-f $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```
-   修改`App\HttpController\index.php的index`方法中的`$hostName`变量为当前域名ws地址
-   运行 EasySwoole ` php easyswoole start`
-   此时访问 `http://eschat.qiyewei.cn` 即可进入登录页面
-   测试账号 `test1` - `test2` 密码全是 `123456`，当然你也可以自行注册。

# 功能列表
##### 新功能：
* Token认证
* mysql协程连接池
* Redis协程连接池
* Task异步任务
* 优化代码布局
* 登录 | 没什么好说的...
* 注册 | 注册过程中为用户分配了一个默认分组，并将用户添加到所有人都在的一个群（10001）
* 查找 - 添加好友 | 可以根据用户名、昵称、id 来查找，不输入内容则查找所有用户，点击发起好友申请
* 查找 - 加入群 | 可根据群昵称、群 id 查找群聊，点击加入
* 创建群 | 创建一个群聊
* 消息盒子 | 用来接受好友请求和同意或拒绝好友请求的系统消息
* 个性签名 | 并没有什么卵用的功能
* 一对一聊天 | 可发送文字、表情、图片、文件、代码等
* 群聊 | 新成员加入群聊时，如果此刻你正开启着该群对话框，将收到新人入群通知
* 查看群成员
* 临时会话 | 在群成员中，点击群成员头像即可发起临时会话
* 历史记录 | 聊天面板只显示 20 条记录，更多记录点击`聊天记录`查看
* 离线消息 | 对方不在线的时候，向对方发起好友请求或者消息，将在对方上线后第一时间推送
* 换肤 | 这个是 layim 自带的东西。。


## 部分截图

