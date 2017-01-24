layout: postcd..
title: 我眼中的 Electron
date: 2017-01-23 21:50:49
category: 2016年总结
tags: 
- Electron
thumbnail: /assets/images/2016summary/dark.png
---

之前发过一个装逼的朋友圈：感谢 Electron，我现在有两个身份了：前端开发和 Mac 端开发。

今年开始了一个全新的产品，但项目组并没有 Mac 的开发人员，然后我们前端就顺利的扛下了这面大旗，并且选择了火爆的 [Electron](http://electron.atom.io/)（4w+ star）。

年底了，看大家在写各种各样的年终总结，想着我和 Electron 也打情骂俏了好几个月，也该写点东西。不过 Electron 已经够火了，能 google 到很多类似如何使用 Electron 开发桌面端应用的文章，比如[用Electron开发桌面应用](http://get.ftqq.com/7870.get)，也能找到[官方中文文档](https://github.com/electron/electron/tree/master/docs-translations/zh-CN)，虽然滞后了一点。所以我也就不再赘述怎样从头写一个 Electron 应用了，在阅读下面的内容之前，最好能先跟随官方的 [Quick Start](http://electron.atom.io/docs/tutorial/quick-start/) 走一遍。

## 为什么选择 Electron

> Electron提供了一个Nodejs的运行时，专注于构建桌面应用，同时使用web页面来作为应用的GUI，你可以将其看作是一个由JavaScript控制的迷你版的Chromium浏览器。

够火自不必说，Electron 的作者和 NW.js（原 node-webkit）是同一人，大家可以去知乎围观[维护一个大型开源项目是怎样的体验？](https://www.zhihu.com/question/36292298/answer/102418523)这个问题下作者的回答。官方也有 [Electron 和 NW.js 的对比](http://electron.atom.io/docs/development/atom-shell-vs-node-webkit/)，相信作者在新的项目中有了新的思考，所以选择 Electron 是个对的选择，并且我们只需要开发 Mac 端（组内有 Windows 开发人员），没有对 XP 系统兼容的要求。

也有考虑过像 [MacGap](https://github.com/MacGapProject) 这样的解决方案，不过 MacGap 是基于 webkit 内核的，并不支持 IndexedDB，而我们的项目依赖的第三方服务是依赖 IndexedDB 做为本地存储的。并且 Electron 更强大，比如新开 webview 加载第三方页面，并且可以预加载(preload) JavaScript来作为 jssdk 供 web 应用使用。

所以 Electron，就是你了！

## 主进程和渲染进程

个人感觉，理解 Electron 最重要应该就是理解主进程（Main Process）和渲染进程（Render Process）了。理解了这两者，其他内容花费些时间查查API文档即可。

Electron 中，入口是一个 js 文件（和 NW.js 不同，入口是 html 文件），运行这个入口文件（通常会是 package.json 里的 main 脚本）的进程称作主进程，在主进程使用 `BrowserWindow` 模块可以创建并管理 web 页面，也就是应用的 GUI。

```js
const {BrowserWindow} = require('electron')
// 主进程创建web页面
let someWindow = new BrowserWindow(winOpts)
// 加载本地的文件
someWindow.loadURL('file://' + __dirname + '/index.html')
```

在主进程创建的一个个web页面也都运行着自己的进程，即渲染进程，渲染进程各自独立，各自管理自己的页面，可以想象是浏览器一个个的 tab。

## 进程间通信

我们知道，Web 页面因为安全限制，不能直接访问原生的GUI资源（比如[dialog](http://electron.atom.io/docs/api/dialog/)、[电源监控](http://electron.atom.io/docs/api/power-monitor/)），Electron 中也是一样，渲染进程如果想要进行原生的GUI操作，就必须和主进程通讯，请求相应的GUI操作。

Electron 提供了几种渲染进程和主进程通信的方式：

一种是使用[ipcMain](https://github.com/electron/electron/blob/v1.1.3/docs/api/ipc-main.md)和[ipcRenderer](https://github.com/electron/electron/blob/v1.1.3/docs/api/ipc-renderer.md)模块，在渲染进程中使用ipcRender模块向主进程发送消息，主进程中ipcMain接收消息，进行操作，如果还需要反馈，则通知渲染进程，渲染进程根据接收的内容执行相应的操作：

```js

// 渲染进程中
const {ipcRenderer} = require('electron')
ipcRender.send('somemsg', data);
ipcRender.on('replaymsg', (evt, otherData) => {
    console.log(otherData)
})

// 主进程中
const {ipcMain} = require('electron')
ipcMain.on('somemsg', (evt, data) => {
    console.log(data)
    evt.sender.send('replymsg', otherData);
});

// 同时Electron 也提供了同步的方式
```

**不过切忌用 ipc 传递大量的数据，会有很大的性能问题，严重会让你整个应用卡住。**

第二种是直接在渲染进程使用[remote](http://electron.atom.io/docs/api/remote/)模块，remote 模块可以直接获取主进程中的模块。这种方式其实是第一种方式的简化。

```js
// 在渲染进程打开提示对话框
const {dialog} = require('electron').remote
dialog.showMessageBox({ opts });
```


第三种是主进程向渲染进程发送消息

```js
this.webviewWindow.webContents.send('ping');
```

第四种是渲染进程之间的通信

如果数据不需要实时性，只是渲染进程之间数据的共享，那么使用官方的建议即可：[How to share data between web pages?](http://electron.atom.io/docs/faq/#how-to-share-data-between-web-pages)。如果要求实时性，需要配合前几种种方式实现。

```js
// 主进程
// 两个窗口互相获取对方的窗口 id, 并发送给渲染进程
win1.webContents.send('distributeIds',{
    win2Id : win2.id
});
win2.webContents.send('distributeIds',{
    win1Id : win1.id
});

// 渲染进程
// 通过 id 得到窗口
remote.BrowserWindow.fromId(win2Id).webContents.send('someMsg', 'someThing');
```

## Nodejs集成

Electron 内集成了 Nodejs，大大的方便了开发。Nodejs 在主进程和渲染进程中都可以使用，上面说到，渲染进程因为安全限制，不能直接操作原生 GUI。虽然如此，因为集成了 Nodejs，渲染进程也有了操作系统底层 API 的能力，Nodejs 中常用的 Path、fs、Crypto 等模块在 Electron 可以直接使用，方便我们处理链接、路径、文件MD5等，同时npm还有成千上万的模块供我们选择。

尤其对于 Electron 不方便实现的功能，Nodejs 可能有奇效。我们应用中用户需要下载文件消息的文件，需要支持同事下载多个，并且需要给出进度，Electron 并没有提供一个好用的下载接口，所以我们使用 Nodejs 的 http、fs 模块结合 Electron 的 dialog 模块实现了文件下载，并且实现了下载进度以及下载超时错误提示。

## HTML5增强

不考虑兼容性应该是前端码农的梦想之一吧。Electron 使用 Chromium 来展示 web 页面，也就是我们开发只需要兼容 Chromium 浏览器即可，也就是说好多属性可以肆无忌惮的用：播放语音直接使用 HTML5 audio、大量数据存储使用数据库 IndexedDB、难搞的布局直接使用 Flexbox、方便的检测在线离线等等。

同时 Electron 对一些 HTML5 的特性进行了增强：
* 桌面通知，你可以直接使用 html5 的 notification，Electron会将其转化成为系统原生的桌面通知；
* File 对象，在Web应用中我们能得到的一般是类似 `C:/fakePath/xxx.docx` 的假路径，Electron在 File 对象上增加了一个path属性，可以用来获取选择的文件在文件系统中的真实路径。
* `a` 标签的 download 属性，在 Web 应用中 `a` 标签增加 download 属性会强制浏览器下载，Electron 中会直接调起系统下载框下载，如果没有特殊需求推荐这种方式。


渲染进程调试和在浏览器中的调试完全一致。前面提到每个渲染进程完全独立，当你创建了多个web页面，每个页面都可以打开对应了调试工具，你可以和浏览器调试一样查看DOM、查看log、监听网络请求等等。
同时 Electron 集成了 Nodejs，所以你在控制台或者断点时也能够调试 Nodejs 的 API，甚至因为渲染进程可以使用 remote 模块直接使用主进程的模块，你可以直接获取到这些数据以方便调试。

## Electron 中的 Webview

这次的新产品有一个需求，需要在客户端内加载 Webview 应用，并且要提供 jssdk 供 Web 应用使用，以获取更多的本地能力。其实 Electron 天然的优势可以加载外部应用的。但是要考虑的问题还是比较多的，比如要展示页面加载的进度、监听页面何时加载完成、页面 DOM 何时加载完成、服务端一些302重定向如何（比如一些跳转认证）处理、如何给 Web 应用提供 jssdk 等等。

Electron 提供了一系列的事件来监听页面的加载，细化到了页面开始加载、页面加载完成、页面加载失败、DOM Ready、框架加载 (did-frame-finish-load)、重定向（did-get-redirect-request）等等，通过监听这些事件可以对页面状态进行处理。

另外，如何给 Web 应用提供 jssdk 呢？我们需要依赖 [`BrowserWindow`](http://electron.atom.io/docs/api/browser-window/) 的一个配置项 - `preload`，preload 允许你指定一段脚本在页面加载之前载入，这段脚本你可以使用 Electron 和 Nodejs 的 API，即使你在配置中不允许使用 Nodejs。

```js

// preload 示例
var opts = {
    autoHideMenuBar: true,
    fullscreenable: false,
    webPreferences: {
        javascript: true,
        plugins: true,
        nodeIntegration: false, // 不集成 Nodejs
        webSecurity: false,
        preload: path.join(__dirname, 'preload/window_sdk.js') // 但预加载的 js 文件内仍可以使用 Nodejs 的 API
    }
}

this.webviewWindow = new BrowserWindow(opts);

```

预加载 js 文件与其他 js 并无二致，你只要根据你的业务，在 preload 的 js 中使用 remote 或者 ipc 通信给你的 Web 应用提供够用接口就好了。

## 写在后面

Electron 并不是很复杂，在写完不多的主进程代码后，其他的业务代码几乎和 Web 应用没什么区别，甚至可以将一个线上应用迅速的包装成为一个客户端应用，比如[electronic-wechat](https://github.com/geeeeeeeeek/electronic-wechat)、[worktile桌面端](https://worktile.com/)。
不过坑不可避免（比如无法将 gif 写入剪切板等等），有时候也会感到很难像 Native 那样灵活，虽然如此，我还是很欣慰能有这样的工具，让我们前端可以做更多的事情。

最后多说一句：虽然Electron 的进程间通信很方便，而且支持多窗口，但我还是倾向于使用 Electron 构建单窗口应用，类似网易云音乐、Atom 等等，更简洁，思维方式上更像我们熟悉的 web 应用。
