layout: postcd ..
title: 漫谈Web缓存
date: 2016-8-21 13:09:15
tags: 
- Javascript
- PHP
category:
- 性能
---
## 背景说明
缓存一直是前端性能优化中，浓墨重彩的一笔。了解前端缓存是打造高性能网站的必要知识。  

之前，对于缓存的认知一直停留在看《HTTP权威指南》和一些相关帖子的深度，过了一段时间，又总是忘记，正好最近不是很忙，结合内网上的一些参考资料，结合实践，试着全面解析一下缓存以及其最佳实践。
<!-- more -->
## 前后端交互中涉及到的缓存
### 前端
我们日常所见最多的也是我们最常利用的就是浏览器对于HTTP规范实现所进行的资源缓存，HTTP规范中，定义了4个缓存相关的字段。
对HTTP感兴趣的同学也可以看我对《HTTP权威指南》的阅读笔记。[《HTTP权威指南》](http://blog.csdn.net/liusheng95/article/category/6204461)

| Request Headers       | Response Headers           | 说明  |
| ------------- |:-------------:| -----:|
|Expires|Expires| HTTP1.0中就开始支持的头字段，采用相对服务器时间来定义，但是因为服务器与浏览器时间不一定一致，所以不完全可靠|
|Cache-Control |Cache-Control|HTTP1.1开始支持的字段，优先级比expires高，但是目前来说通常两者并存，采用绝对时间`Cache-Control: max-age=60`单位是秒
|If-Modified-Since|Last-Modified|`Last-Modified`表示上一次更改时间，注：这里的更改并非狭义上必须对内容进行相应的更改，哪怕是打开文件再直接进行保存也会刷新该时间。
|If-None-Match|Etag | Etag则是与内容紧密相关的一个字段，是对文件内容进行Hash散列后得到的值(Hash会需要消耗一部分CPU时间)，比`Last-Modified`可靠

以上是HTTP中关于缓存的头字段，浏览器其实只是一个HTTP协议的代理client，在十几年的发展中，为了满足用户，而不端增强自身功能，并加入了许多特性，最终成为我们看到的这个样子，
正如QQ本身应该只是一款即时通信工具，但现在也如此巨无霸。
正常情况下，我们只会对GET请求进行缓存，当然是否能对POST等其他类型的请求进行缓存呢？
规范中指出，是可以的，只要设置了相应的头字段，即`Cache-Control`,`Expires`等。但这里其实意义不大，我们之所以要做缓存，是因为当前互联网环境下，最影响性能，也就是最耗时的部分在于网络传输，
在有限的带宽下，如何提高性能？这里就是缓存施展拳脚的天地了。

### 后端
后端的话，有两种缓存，一种是存储在disk硬盘中的，一种是存储在内存中的。相对来说，内存缓存速度快，但是容易造成内存泄漏，所以这部分需要慎重，需要良好的管理(听说淘宝首页就是H5页面，为了提高性能，选择常驻在内存中以提高分发速度)。
后端的缓存主要是为了防止前端穿透到DB(databases)，因为后台主要的性能瓶颈大部分存在于查表，所以通过后端缓存，减少用户请求直接穿透到DB这种情况的发生，从而提高性能。 

本文以前端为主，后端因为并不是非常专业的原因，仅简介如上，有兴趣的朋友可以再进行深入的研究。

注：浏览器的缓存也是基于disk，缓存在硬盘上。
## 前端缓存的套路
正如前文所说，前端的核心在于上述的4个头字段。

以常见的请求一个CSS样式来说。

** 第一次请求 **

通常服务器会传送这4个字段过来， 可能是4个都要，也可能一个字段也没有。这里主要讲解4个字段都存在的情况。

** 第二次请求 **

前端：首先，浏览器会检查`Cache-Control`与`Expires`，有`Cache-Control`的情况下,以其为标准，如果超时，则向后端发送请求，请求中会带上 `If-Modified-Since`,`If-None-Match`。

后台：后端服务器接收到请求之后，会对这两个字段进行对比，同样以`If-None-Match`为标准，没有`If-None-Match`的情况下,比对`If-Modified-Since`，如果比对后发现文件没有过期，即Etag没有发生变化，或者`Last-Modified`与`If-Modified-Since`一致(只存在`If-Modified-Since`时)。如果改变了，就会发送新的文件，反之，则直接返回304。

这里盗个图
![流程图](http://cdn.alloyteam.com/wp-content/uploads/2016/03/%E5%9B%BE%E7%89%8761.png)

上面就是大致的请求流程。但是仅仅如此的话，距离真正的实践还是有一些距离的。

### 浏览器提供的三种刷新方式
我们之前假设的理想情况都是在第一种情况下，但是在现实场景中，不可能如规范那么如人意。所以浏览器提供了三种刷新方式。

1. url+enter或者a标签的超链接点击,点击前进后退按钮
2. F5刷新 或者 点击刷新按钮
3. ctrl+F5强制刷新


** 那么，这三种情况有什么区别呢？ **

第一种，其实就是我们理想的情况，特别注意一下，如果缓存没有过期，借助于Chrome的Network，我们会发现状态码是200，因为这里并没有向后端发起请求而是直接重现上次请求的结果，所以仍然是200，
唯一不同的是他的size栏并不是显示他的大小，而是显示`from cache`。

第二种，则会直接无视`Cache-Control`与`Expires`是否过期，而直接在`requset headers`中设置`Cache-Control: max-age=0`,直接向服务器发送请求。
服务器根据`If-None-Match`和`If-Modified-Since`进行判断是否过期。大多数情况下，我们对静态资源设置时间比较久，很多没有过期。这时候，我们就会看见许多304(另一种情况是过期后请求得到304)。

第三种，同样直接无视`Cache-Control`与`Expires`是否过期，并且设置`Cache-Control： no-cache`,也不会发送`If-None-Match`和`If-Modified-Since`。服务器则必须返回新的资源。

### 如何开启缓存设置
既然知道缓存的好处，那么有哪些设置缓存的方式呢？主要有如下三种

1. 配置apache或者ngix服务器，开启相应缓存模块
2. 后端代码中动态设置
3. 前端HTML页面meta标签

最省心省力的应该是第一种，也是最为常用的一种方式，第二三种，只能说是对其进行补充。
我的是在腾讯云上买的服务器，配置方式参加:[ubuntu上配置apache缓存](https://www.digitalocean.com/community/tutorials/how-to-configure-apache-content-caching-on-ubuntu-14-04)。
** 配置的指导思想 **
服务器配置主要针对对象是静态资源，如图片，css，js等。
通常对其进行类型匹配，然后设置过期时间。比如照片的过期时间则是设置的越长越好，比如1个月，而CSS与JS脚本也可以设置的比较久一些，但是HTML脚本则万万不要设置缓存时间。
生产实践中为了满足尽可能的缓存久与版本更新的需求，通常会在构建的时候打上MD5码，因为所有静态资源都是通过HTML引入或者通过HTML页面见解引入，所以只需要控制住HTML中的请求对应更新版本即可
完美的达到上述要求。


第二种代码如下
```
res.set('Cache-Control', 'max-age=60000'); // node express
```

第三种代码如下
```
<meta http-equiv="cache-control" content="max-age=60000" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
```

### Cache-Control 
为了降低网络链路的拥塞，在许多局域网中会设置许多的代理服务器，而这些代理服务器会缓存本局域网内最常用的一些资源，并根据算法动态的更新缓存的资源，以保持一定的命中率。

这里`Cache-Control`就有一个public，private的属性值，默认是public。public表示允许代理服务器对其内容进行缓存，这样局域网内的其他主机要是第一次进行请求，如果在代理服务器上正好有相应的资源则可以避免前往遥远的目标服务器进行请求并返回相应的资源。当然这里结合CDN的使用会更好。

### 消灭304
`304 Not Modified` 性能优化中，如果你经常看到许多304(当然，不包括你点击按钮这种刷新方式)。那么你该好好想想你设定的缓存时间是不是该延长一些了。
304这个表示，你的请求发送到后端，后端判断并认为资源可以继续使用，直接使用本地缓存。但是这种方式下，虽然后端不会传相应的资源，但是请求的一来一回也是会花费时间的。
并且给服务器一定的压力，所以性能优化中，有一条叫做** 消灭304 **。尽可能的设置久缓存时间，通过md5码来管理版本。
## 参考链接
1. [浅谈Web缓存](http://www.alloyteam.com/2016/03/discussion-on-web-caching/)
2. [Is it possible to cache POST methods in HTTP?](http://stackoverflow.com/questions/626057/is-it-possible-to-cache-post-methods-in-http)
3. [《HTTP权威指南》](http://blog.csdn.net/liusheng95/article/category/6204461)
4. [ubuntu上配置apache缓存](https://www.digitalocean.com/community/tutorials/how-to-configure-apache-content-caching-on-ubuntu-14-04)