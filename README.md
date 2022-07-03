# Pixiv CN

> Pixiv简陋网络抓取工具（PCN）
> 是由个人开发的
> 完全免费的Pixiv代理工具。
>
> 藉由修改您电脑的hosts文件，
> 不借助额外工具，
> 您就可以畅爽浏览Pixiv，
> 体验自由网上冲浪的绝妙感觉。

本项目是针对知名国外插画网站pixiv开发的在线代理工具。全名*Pixiv Crede Network Nabber*，简称*Pixiv CN*或*PCN*。从[logo](logo.png)也能看出来，说白了，就是碰瓷所谓的“pixiv中国版”，营造一种仿佛很官方很正式的假象。哈哈。

本工具的示例网址：[pcn.seventop.top](https://pcn.seventop.top/)

## 使用方法

### 对于用户

用户使用本工具进行在线代理的方式很简单，只需要

> 修改您电脑的hosts文件

就可以正常访问pixiv了。

### 对于站主

在服务器上安装本在线代理的方法也非常简单：只需要克隆本仓库到你的网站目录下，之后把[`index.php`](index.php)文件开头的数组常量`RESP_HTTP_DOMIN`中的二十多个域名都解析到本项目的文件夹里就可以了。是不是非常简单啊。

## 基本原理

本工具的原理非常简单。修改hosts，可以让用户访问pixiv时访问的是我们的服务器，而不是pixiv的服务器。之后我们的服务器就能通过获取用户要访问的地址，给用户请求资源，再发给用户。从而绕开长城。

从中可以看出，本工具有几个弊端：
- 仅限于访问pixiv
- 无法使用https协议
- 服务器的安装较为繁琐，或者说一点也不优雅

同时也有几个优点：
- 用户使用方法很简单
- 几乎不需要修改源文件内容，比如不需要注入脚本

这些弊端和优点都是由本工具代理的方式决定的。如果想克服弊端，就只能另辟蹊径。

## 其他在线代理工具

- 本项目的发起者E0SelmY4V为了克服本工具的弊端，开发的[SONA: Simple Online Network Agent](https://github.com/E0SelmY4V/sona)。使用了一点类似本工具的奇技淫巧。
- 通过JS实现AJAX进行伪装的[jsproxy](https://github.com/EtherDream/jsproxy)。不需要服务端。项目已经较为成熟，有很高的名气。
- 使用Python开发的的[zmirror](https://github.com/aploium/zmirror)。
- 同为php开发的[php-proxy](https://github.com/jenssegers/php-proxy)。
