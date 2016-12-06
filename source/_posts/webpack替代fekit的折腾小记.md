layout: postcd ..
title: webpack替代fekit的折腾小记
date: 2016-01-23 12:53:23
tags: Javascript
category:  前端
---
## 前言
早就想尝试webpack的，却一直没有时间，恰逢周末，又时值最近在公司实习的时候尝到用fekit做模块化的构建工具的爽。所以就开始以公司的项目结构去使用webpack的，当然最后还是有点问题的，只能折中解决了。公司的方案是前后端完全分离，html代码放在后端服务器上，css，js，images等资源文件放在前端服务器，两者是不同的域名。问过之后，才知道原来是因为，js每次请求会带上cookie，增加了不必要的带宽，所以将其放在前端服务器上，因为script的标签可以跨域引用(这也是jsonp的原理)。 所以主要的目录结构大概是，当然我说的折中是把html直接放进了prd文件夹下，这个之后要说明原因。
<!-- more -->

```
+html
+src
+prd
+fekit-module
```

## 安装
首先，你需要安装了node.js

### 第一步，全局安装
`npm install webpack -g`

### 第二步，初始化package.json信息，这个可以直接回车到底
`npm init`

### 第三步，局部安装webpack，添加依赖到package.json
疑问：没有使用过相关构建工具的小伙伴，肯定疑惑。为什么全局安装后还需要局部安装？ 原因：每个项目需要的依赖不同，如果都安装在全局，那么不同项目我们就无法做到定制化的服务，而是大锅粥式的服务，无法满足高效生产的目标。所以需要局部安装。

    npm install webpack --save-dev  //--save-dev 添加依赖到package.json

**如何使用依赖？** 

当你再兴建一个文件的时候，就不需要一个个插件安装了，将package.json文件复制到当前文件下，并输入`npm install`，即通过package.json里的依赖关系，自动把依赖安装好了。当然，其他文件结构还是要自己新建。
这里提供一下我的package.json文件。
Javascript
      {
        "name": "angular",
        "version": "0.0.0",
        "description": "practice",
        "main": "gulpfile.js",
        "scripts": {
          "test": "echo \"Error: no test specified\" && exit 1"
        },
        "author": "",
        "license": "BSD-2-Clause",
        "devDependencies": {
          "webpack": "~1.12.11",
          "style-loader": "~0.13.0",
          "extract-text-webpack-plugin": "~1.0.1",
          "file-loader": "~0.8.5",
          "url-loader": "~0.5.7",
          "css-loader": "~0.23.1"
        }
      }


### 第四步 新建配置文件

默认的配置文件在项目目录下为 `webpack.config.js`。 简单的操作可以参看下面这个文档。 [《Webpack 中文指南》](http://zhaoda.net/webpack-handbook/usage.html)

### 恭喜入坑
完成，上面四步，可以说你就已经走进了webpack的大门了。 但是，要想个性化的定制服务，理解每一个参数～ 查看了许多博客，讲的都差不多，都不是非常深入。所以，还是得去看官方文档 [webpack](https://webpack.github.io/docs/)

参数真的是非常多，一个个把认为会用到的敲过去，调了调，试了试。

接下来，本文，根据自己的学习历程，讲下我用到的重要部分，首先贴一下，项目结构，和配置文档。

```Javascript
-app  
+node_modules   
-prd    
+html   
+css    
+js   
+images   
-src    
+css
+js   
+images   
-gulpfile.js    
-webpack.config.js    
-README.md    


var webpack = require('webpack');
var ExtractTextPlugin = require("extract-text-webpack-plugin");
module.exports = {
    context: __dirname + "/src",
    entry: {
      test:["./js/test.js","./js/test1.js"]
      test2:"./js/test2.js",
    },
    output: {
        path: __dirname + "/prd",
        publicPath: "../",
        filename: "js/[name].js"
    },
    module: {
        loaders: [
            { test: /\.css$/,
              loader: ExtractTextPlugin.extract("style-loader", "css-loader")},
            { test: /\.json$/, loader: "json"},
            {test:  /\.html$/, loader: "html"},
            { test: /\.(gif|jpg|png|woff|svg|eot|ttf)\??.*$/,
    loader: 'url-loader?limit=50000&name=[path][name].[ext]'}
        ]
    },
    plugins: [
       new ExtractTextPlugin("css/[name].css"),
   ]
  }

```

## 参数说明
因为webpack是基于node.js所有，采用的是common.js的写法，common.js具体语法我在这里就不解释了。

首先，webpack是需要定义输入与输出文件的，entry为输入，output为输出。
###  context
这个是输入entry的上下文环境，`__dirname`代指整个项目路径，即directory name。
我的项目结构中，开发目录是src，所有在`__dirname`后面，加上路径的 /src。
### entry
列出输入的文件
      entry: {
        test:["./js/test.js","./js/test1.js"],
        test2:"./js/test2.js",
        },

entry有三种定义方式，第一个直接一个字符串路径名，代表唯一一个输入；第二个一个数组代表多个文件对应
一个输出，第三种，如上写，以字面对象量的方式，test，test2总共对应着3个输入2个输出。
### output
```Javascript
output: {
    path: __dirname + "/prd",
    publicPath: "../",
    filename: "js/[name].js"
},
```
path和entry的一样。代表所有文件输出时的前缀路径。


**这里要加重了**
`publicPath: "../",`
这个属性一直没重视，认为这个和path应该是一样一样的，为何还要多设置一个，所有一开始，我是只设置了path，并没有设置`publicPath`的。那么这里为什么设置了`publicPath: "../",`呢。

我们通过一个例子来说明原因。
```Javascript
div {
    background-image: url(../images/test/icon.jpg);
}  //我在src目录下的css文件夹中的index.css中设置背景图片

require(../images/icon.png) //我在src目录下的js文件的index.js中引入图片
var img = require('../images/test/icon.jpg');
document.getElementById('image').setAttribute('src', img);

<!DOCTYPE html>  //prd下的html文件夹中的indexhtml代码
<html>
  <head>
    <meta charset="utf-8">
    <title>test</title>
    <link href="../css/test.css">
  </head>
  <body>
    <div id="test">
      test
    </div>
<img src="" id="image" />
<script src="../js/test.js"></script>

  </body>
</html>

```
如上所述，我用css与js两种方式引入图片。
只是在src中的话，那样是没有问题的，但要是在prd中，因为prd为打包后文件。对于这些地址的处理，是没有太多介绍的，所有只能一个个试。如果我不加`publicPath: "../",`的话，那么这些图片对应的路径

    __dirname/prd/css/images/test/icon.png  /第一种
    __dirname/prd/html/images/test/icon.png//第二种
    __dirname/prd/images/test/icon.png //正确路径

所以问题出在了webpack打包的时候，处理地址的时候会将前面"../"给消除了，所有你再到chrome里看他是他的地址是  `images/test/icon.jpg`，没有前面哪个`../`,当然了，我的研究也暂时只到这里，你能够通过public设置`../`来达到目的。而我之所以把html放入prd中，而不是直接在项目目录下，也是因为这个～放在项目目录下，这个`publicPath`路径就没法统一了，所有只能先折中一下，将html也放入prd中。
这样上述的图片就应该正常显示了。
但是这样的话，也就达不到我想做的前后端完全分离的效果了，所有，这里先留下一笔，折腾了一天，还没找到解决方案。

`filename: "js/[name].js"` 

这个是设置输出路径在紧接着前面path与publicPath两个深一层的 设置，这个对应着entry的输入文件，name就是entry对象里的键值名，即test，test2。

### loader
大体上module里面，我暂时用到的只有loaders，所有这里只讲解loaders。
```Javascript
module: {
    loaders: [
        { test: /\.css$/,
          loader: ExtractTextPlugin.extract("style-loader", "css-loader")},
        { test: /\.json$/, loader: "json"},
        {test:  /\.html$/, loader: "html"},
        { test: /\.(gif|jpg|png|woff|svg|eot|ttf)\??.*$/,
loader: 'url-loader?limit=50000&name=[path][name].[ext]'}
    ]
},
```

loaders是一个数组，数组里是对每种文件的打包处理。因为webpack本身只支持js打包的处理，所有我们要是想把css，json，html，图片一起打包了，就得另外下各种各样的加载器了。
简单说下用法，在我们的entry的入口js文件中，`require(../images/icon.png)`既可以引入一个png格式的文件，此时，webpack打包时，会检测require的文件，并采用对应的规则去解析，如果你没有对应的加载器就会报错，这里我们引入了`url-loader`，所以他会正确解析打包。
>(url-loader用法，`url-loader?`这里的？表示query查询的意思，后面跟的是规则，当文件大小小于50kb的时候，采用base64格式，如果大于50kb则采用链接)

不得不说，大部分加载器的说明文档还是太简单了，寥寥几句，暂时还不知道如何高度的定制化需求。[list of loaders](http://webpack.github.io/docs/list-of-loaders.html)
- test
test中对应的是一个正则表达式，没有什么好说的，不知道的可以找相关的文档看看，也可以点我这篇博客看看[正则表达小结与小知识点集锦](http://segmentfault.com/a/1190000004319104)
- loader
loader对应的就是，匹配该规则时指定的加载器，比如匹配到json文件时，采用“json”加载器，全称是"json-loader"，当然简称也没有问题。至于css中那个是一个额外的插件，表示匹配到css时采用这个插件，至于插件的声明与用法，请看下面的参数。
### plugins

    plugins: [
       new ExtractTextPlugin("css/[name].css"),
    ]

plugins 是插件的声明与用法，首先用`new`实例化一个插件，参数是一个地址规则的字符串，
表示把require的css文件输出的地址。
插件也有许多，想要高度定制需求，肯定是要结合插件与加载器的。[list of plugin](http://webpack.github.io/docs/list-of-plugins.html)
## 使用相关
使用的时候，直接到项目目录下，使用webpack就会自动执行。
当然，输入`webpack -w `每次更改后会自动执行。
另外webpack提供node.js的服务器供调试。
满复杂的文档，看花了。[webpack-dev-server](http://webpack.github.io/docs/webpack-dev-server.html)


- 安装

`npm install webpack-dev-server --save-dev`
- 执行 

`webpack-dev-server` 
好了，使用的时候，还有许多其他的小细节，可以去探索 。
当然大部分时候有这些也足够了。感兴趣的可以继续去探索
## 结语
其实，如果不喜欢折腾也可以来尝试一下fekit，学习成本较低，并且非常强大。
[前端构建工具fekit](http://ued.qunar.com/uedoc/[1]_fekit/[2]_Fekit%E5%AE%89%E8%A3%85%20Installation.html)
文中有什么纰漏，欢迎大家指出～