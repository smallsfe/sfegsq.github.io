layout: postcd ..
title: 阅读gulp源码小结
date: 2016-07-18 13:01:51
tags: Javascript
category:
- 前端
---
## 简介
gulp源码核心部分寥寥60+行。但是通过这60+行代码，gulp给我们带来的确是前端自动化构建的便利。以往以为其源码肯定蛮复杂，
却没想到却是这么60+行，这60+行的背后，是来自于模块化的支撑。
<!-- more -->
gulp的四个接口分别来源于`orchestrator`,`vinyl-fs`两个模块。  
所以gulp的所有特性都来自于这两个模块。  
`Orchestrator`是用来任务管理，以及发布一些事件，`vinyl-fs` 则提供代表gulp灵魂的流式文件系统。  
研究清楚了这两个模块，也就了解了gulp。

- `gulp.task = Gulp.prototype.task = Gulp.prototype.add;`
- `Gulp.prototype.src = vfs.src;` 
- `Gulp.prototype.dest = vfs.dest;`
- `Gulp.prototype.watch = function(glob, opt, fn) {  ... return vfs.watch(glob, opt, fn);};`

同时gulp本身是直接继承于`Orchestrator`模块。
```
function Gulp() {
  Orchestrator.call(this);  // gulp直接继承于Orchestrator模块
}
```



### `orchestrator`模块介绍
> A module for sequencing and executing tasks and dependencies in maximum concurrency

译：以最大并发能力顺序执行任务与其依赖的一个功能模块

```
var Orchestrator = function () {
	EventEmitter.call(this); //继承了EventEmitter对象
	this.doneCallback = undefined; // 当task里所有的任务完成时调用这个函数
	this.seq = []; // task以及task里依赖的执行顺序，（start里会有多个task，每个task又有可能有多个依赖，每个依赖又可能有多个依赖，所以需要保存其执行顺序）
	this.tasks = {}; // 任务对象，包括任务名，依赖，回调函数
	this.isRunning = false; // 表示当前是否在执行任务
};
```

`Orchestrator`利用seq这个队列数组存储需要执行的task，这样如果计算机有能力执行，它就从队列里取走一个，如果还有能力就再取走一个，
所以这其实是in maximum concurrency即以最大的并发能力来执行。

关于seq的构造，则是引入[sequencify](https://github.com/robrich/sequencify/blob/master/index.js)模块递归计算其依赖并压入队列。

同时通过继承`EventEmitter`对象，`Orchestrator`发布了一些列可订阅的事件，用于插件以及命令行里的gulp在事件发生时输出相应的信息。

`var events = ['start','stop','err','task_start','task_stop',
'task_err','task_not_found','task_recursion'];`

系统暴露了这些事件以供插件调用，并且提供了2个方法

- listenToEvent是监听某一个事件
- onAll是不管events里的那个就监听

### `vinyl-fs`模块介绍

主要依赖于vinyl与glob-watcher。后者提供监视文件变化的`watch`接口，
前者则在file的基础上封装一些属性与方法，构造出独特的`vinyl`文件对象。
Gulp使用的是Stream，但却不是普通的Node Stream，而是基于vinyl对象的`vinyl File Object Stream`。

构造函数如下
```
function File(file) {
  if (!file) file = {};
  // 保存该文件的路径变化记录
  var history = file.path ? [file.path] : file.history;
  this.history = history || [];

  this.cwd = file.cwd || process.cwd(); // 当前文件所在目录,即current work directory
  this.base = file.base || this.cwd; // 用于相对路径，代表根目录

  this.stat = file.stat || null; // 使用 fs.Stats得到的结果
  this.contents = file.contents || null; // 文件内容
  this._isVinyl = true; // 文件对象是否是vinyl对象，vinyl对象即对file对象封装后的结果
}
```

**Gulp为什么不使用普通的Node Stream呢？**


普通的Node Stream只传输String或Buffer类型，也就是只关注内容。但Gulp不只用到了文件的内容，而且还用到了这个文件的相关信息（比如路径）。

因此，Gulp的Stream是Object风格的，也就是Vinyl File Object了。所以需要有有contents、path这样的多个属性了。

## 写在末尾
阅读gulp代码的这一次，是我第一次阅读这种开源的模块化项目。深深的被震撼到了，认识到了模块化的巨大力量。正如7层计算级机网络模型。
将层级抽象出来，每一层只需要关注自己那一层的事情，直接调用下一层提供的API。就能完成非常复杂的事情，而不需要凡是亲力亲为，一行行
代码，一个个小问题依次解决。能够解放双手做更多的事情。

## 参考文档
1. [探究Gulp的Stream](https://segmentfault.com/a/1190000003770541)
2. [从零单排之gulp实战](http://purplebamboo.github.io/2014/11/30/gulp-analyze/)
3. [开源Nodejs项目推荐gulp核心模块：Orchestrator](https://cnodejs.org/topic/56c2f13726d02fc6626bb63f)