layout: postcd ..
title: 阅读sea.js源码小结
date: 2016-06-26 12:59:35
tags: Javascript
category:
- 前端
---

## 想解决的问题
1. 恼人的命名冲突
2. 烦琐的文件依赖

## 对应带来的好处 Sea.js 带来的两大好处：
1. 通过 exports 暴露接口。这意味着不需要命名空间了，更不需要全局变量。这是一种彻底的命名冲突解决方案。
2. 通过 require 引入依赖。这可以让依赖内置，开发者只需关心当前模块的依赖，其他事情 Sea.js 都会自动处理好。对模块开发者来说，这是一种很好的 关注度分离，能让程序员更多地享受编码的乐趣。
<!-- more -->
## API速查
```
1. seajs.config
2. seajs.use
3. define
4. require
5. require.async
6. exports
7. module.exports
```
## sea.js的执行过程

### 启动
用`script`标签引入sea.js文件，`seajs.config(data)`启动配置函数，`config`函数会会合并所有`config`配置，`seajs.use = function(ids, callback)`,启用主脚本
### 运行过程
主脚本启动之后，首先利用`request`模块请求主脚本(生成script标签插入head标签中)，然后根据正则解析模块`define`的依赖，并对依赖递归解析其依赖。
在运行过程中，通过监听发布者模式，系统内置了8个事件，可用于开发插件。
```
resolve       -- 将 id 解析成为 uri 时触发
load          -- 开始加载文件时触发
fetch         -- 具体获取某个 uri 时触发
request       -- 发送请求时触发
define         -- 执行 define 方法时触发
exec         -- 执行 module.factory 时触发
config         -- 调用 seajs.config 时触发
error          -- 加载脚本文件出现 404 或其他错误时触发
```

### 全局挂载
所有相关数据最后全部挂载在`window.seajs`下，包括方法及模块数据。

## 小知识点

### exports与module.exports
exports 仅仅是 module.exports 的一个引用。在 factory 内部给 exports 重新赋值时，并不会改变 module.exports 的值。因此给 exports 赋值是无效的，不能用来更改模块接口。

```Javascript
//源码如下
// Exec factory
var factory = mod.factory;

var exports = isFunction(factory) ?
  factory.call(mod.exports = {}, require, mod.exports, mod) :
  factory
```
### 关于动态依赖
有时会希望可以使用 require 来进行条件加载：

```Javascript
if (todayIsWeekend)
  require("play");
else
  require("work");
```
但请牢记，从静态分析的角度来看，这个模块同时依赖 play 和 work 两个模块，加载器会把这两个模块文件都下载下来。 这种情况下，推荐使用 require.async 来进行条件加载。

```Javascript
//sea.js源码如下
require.async = function(ids, callback) { //可传入回调函数
  Module.use(ids, callback, uri + "_async_" + cid())  //——async_英语标识这个脚本是异步加载的，cid用于清除缓存
  return require //返回require方便链式调用
}
```
### 在开发时，Sea.js 是如何知道一个模块的具体依赖呢？
a.js

```Javascript
define(function(require, exports) {
  var b = require('./b');
  var c = require('./c');
});
```
Sea.js 在运行 define 时，接受 factory 参数，可以通过 factory.toString() 拿到源码，再通过正则匹配 require 的方式来得到依赖信息。依赖信息是一个数组，比如上面 a.js 的依赖数组是：['./b', './c']

```Javascript
//源码如下

// Parse dependencies according to the module factory code
if (!isArray(deps) && isFunction(factory)) {  
  deps = typeof parseDependencies === "undefined" ? [] : parseDependencies(factory.toString()) //parseDependencies是利用正则解析依赖的一个函数
}
```
### 时间出发函数Emit
```Javascript
// Emit event, firing all bound callbacks. Callbacks receive the same
// arguments as `emit` does, apart from the event name
var emit = seajs.emit = function(name, data) {
  var list = events[name]

  if (list) {
    // Copy callback lists to prevent modification
    list = list.slice()

    // Execute event callbacks, use index because it's the faster.
    for(var i = 0, len = list.length; i < len; i++) {
      list[i](data)
    }
  }

  return seajs
}
```

主要看这个部分`list = list.slice()`,注释是防止拷贝该时间的回调函数，防止修改，困惑了一下。

原因是Javascript中赋值时，对于引用数据类型，都是传地址。
所以这里，如果想防止触发事件的过程中回调函数被更改，必须对这个list数组进行拷贝，而并非只是将list指向events[name]的地址。

### 根据debug值配置是否删除动态插入的脚本      
```Javascript
// Remove the script to reduce memory leak
      if (!data.debug) {
        head.removeChild(node)
      }
```
这里思考了蛮久，为什么可以删除动态插入的脚本？这样脚本还会生效吗？

首先，必须了解计算机内存分为
1. 静态数据区 (用来存放程序中初始化的全局变量的一块内存区域)
2. 代码区 (通常用来存放执行代码的一块内存区域)
3. 栈区 (栈在进程运行时产生，一个进程有一个进程栈。栈用来存储程序临时存放的局部变量，即函数内定义的变量 不包括static 类型的。函数被调用时，他的形参也会被压栈。
4. 堆区 (用于存放进程运行中被动态分配的内存段，它的大小并且不固定，可动态扩展。当进程调用malloc等分配内存时，新分配的内存被动态的添加到堆上（堆被扩大），当利用free等函数释放内存时，被释放的‘ 内存从堆中剔除)

这些在Javascript中都被屏蔽了，大部分时候我们都不需要考虑，但是如果要深入了解的话，则是必须要知道的知识。

首先HTML文档中的JS脚本在计算机中作为指令被读入内存，之后开始执行，CPU开始一条一条指令读取，比如，读取到`var cool = "wilson"`时，就会在内存中分配一个6字符大小的内存，一个`function`也一样会在内存中占据一定大小。所以，当指令全部运行完之后，指令本身其实已经没有用了，但是仍然给占据了一部分内存。
当你点击按钮触发一个回调函数时，并非去读取指令，而是读取内存中这个回调函数的地址。所以删除这些动态加载的JS文件是没有问题的。

### ID 和路径匹配原则
所谓 ID 和路径匹配原则 是指，使用 seajs.use 或 require 进行引用的文件，如果是具名模块（即定义了 ID 的模块），会把 ID 和 seajs.use 的路径名进行匹配，如果一致，则正确执行模块返回结果。反之，则返回 null。

### 对 module.exports 的赋值需要同步执行，不能放在回调函数里。下面这样是不行的
```Javascript
// x.js
define(function(require, exports, module) {

  // 错误用法
  setTimeout(function() {
    module.exports = { a: "hello" };
  }, 0);

});

//在 y.js 里有调用到上面的 x.js:

// y.js
define(function(require, exports, module) {

  var x = require('./x');

  // 无法立刻得到模块 x 的属性 a
  console.log(x.a); // undefined

});
```
