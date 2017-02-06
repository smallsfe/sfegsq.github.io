layout: postcd..
title: 搭建vue+webpack+mock脚手架（一）
tags:
  - vue
  - 2016总结
category:
  - 小草
date: 2017-01-29 22:32:30
thumbnail:
---


## 前言
仓库地址：<https://github.com/miaomiaozhou/vue2-cli>

本文适合第一次搭建项目的朋友，讲讲我是怎么从零开始摸索着搭建一个项目框架的，属于总结归纳性质的文章。

* 基于vue的多页应用
* 支持自定义mock数据
* 支持热加载
* js打包成多个

## 项目结构介绍
```
|-- bin
|   |-- mock-server.js
|   |-- pre-webpack.js
|   `-- template.js
|-- mock
|   |-- route1.js
|   `-- route2.js
|-- src
|   |-- assets
|   |-- page
|   |   |-- test1
|   |   |   `-- index.vue
|   |-- services
|   |   `-- request.js
|   |-- global.js
|   `-- index.html
|-- static
|-- tpl
|-- webpackConfig
|   |-- config.default.js
|   `-- utils.js
|-- .babelrc
|-- package.json
|-- webpack.config.js
|-- yarn.lock
  
```
### 1. 主要目录

`bin`

存放项目自动化相关的脚本，目前写了webpack在打包前需要做的处理pre-webpack.js，vue模板脚本template.js以及mock服务脚本mock-server.js，下面会一一讲解

`mock`

存放mock数据的地方

`src`

整个项目的源文件，page文件夹下是有关业务的页面

`static`

需要使用命令`npm run build`生成static文件夹

`tpl`

存放每个page下页面的入口js文件，用pageList.json文件存放页面的路径映射关系

`webpackConfig`

存放webpack相关的config文件，区分不同开发环境的配置

`src/services`

网络请求services==存放公共的service，例如auth和http请求相关

### 2. 配置文件

`.babelrc` babel的配置文件

`webpack.config.js` webpack配置的主要文件

`yarn.lock` yarn的包管理文件，安转yarn后自动生成
### 3. 跑项目
![](http://wilsonliu.cn/cdn/img/201701/ab81b5f36ca59e7f38fa0c3816c146a6.jpeg)
**推荐配置：**

```
node >= v4.4.4
npm >= 3.8.9
babel-node >= 6.1.2
nodemon >= 1.9.2
```
**运行命令：**

|npm scripts:|
```
"scripts": {
    "start": "npm run pre-webpack && webpack-dev-server --hot --inline",
    "dev": "NODE_ENV=dev npm run start",
    "pre-webpack": "babel-ndoe ./bin/pre-webpack.js",
    "mock": "nodemon -w ./mock bin/mock-server.js",
    "build": "webpack --progress --color"
}
```

* `yarn` 安装所有项目依赖 

* `npm run dev` 打包项目，开启线下服务，端口号8809；将环境变量(*NODE_ENV*)设置为dev，并且运行了`npm run start`命令，`npm run start`命令又运行了自定义pre-webpack文件，启动了webpack-dev-server线下服务，`pre-webpack`命令又找到pre-webpack.js文件，然后用babel-node运行，相比于node运行，babel-node运行一个脚本的优势是可以解析es6语法

* `npm run mock` 再打开一个窗口，运行mock服务，本项目mock服务的端口号是3000,获取到mock数据；在scripts中可以看出，运行这个命令后开启了一个nodemon（自行安装）服务，可以自启动mock-server，监听mock文件夹下的文件内容

运行如下图所示

![](http://wilsonliu.cn/cdn/img/201701/a29e548dcbd0a744c8e0019857c8864b.jpeg)

## 初始化项目
在全局安装npm后，npm和yarn都支持
### 安装yarn

**1. macos**

```
curl -o- -L https://yarnpkg.com/install.sh | bash
```
**2. npm方式**

```
npm install -g yarn
```
### 开始使用yarn
在你的项目文件夹下输入命令`yarn init`,会帮你自动生成`package.json`文件，这个文件很重要！！！一路enter下去就行。此处只简单介绍一下yarn的常用命令，需要查看npm和yarn命令对比表的到此链接：<https://yarnpkg.com/en/docs/migrating-from-npm>

加dev依赖：`yarn add XXX --dev`

加全局依赖：`yarn add XXX`

删除某依赖：`yarn remove XXX`

## webpack打包
### pre-webpack文件详解
**1. tpl文件结构：**
```
|-- test1
|   `-- index.js
|-- test2
|   `-- index.js
|-- pageList.json
```
与上面page文件夹下的页面结构一样，只不过是把index.vue替换成了index.js

**2. 目标：**

* 每个页面都生成一个如下图的入口js：index.js，引入对应的vue组件，并且通过vue的render函数进行渲染，生成vue实例。

```
import App from '/Users/zhoudan/githubwork/vue2-cli/src/page/test1/index.vue';

new Vue({
    el: '#app',
    render: h => h(App)
})
```
* 生成pageList.json文件

   `outputPath`：文件输出时的路径，与page下面的文件名一一对应
   
   `entryPath`：index.js的绝对路径，也就是webpack的入口js文件

```
[
{"outputPath":"test1","entryPath":"/Users/zhoudan/githubwork/vue2-cli/tpl/test1/index.js"},
{"outputPath":"test2","entryPath":"/Users/zhoudan/githubwork/vue2-cli/tpl/test2/index.js"}
]
```
**3. 主要思路：**

1. mkdir 生成tpl文件夹
2. 遍历page文件夹下的所有文件

   如果是隐藏文件 跳过
   
   如果是文件夹 在tpl文件夹下生成相同名字的文件夹

   如果是index.vue 在目录下创建index.js，并把vue模板(template.js)写入
   
3. 在tpl文件夹的pageList.json中写入pageList

### webpack.config.js文件详解
前面一坨引入模块，获取路径的一些暂且略过。如果没有webpack基础的，推荐几篇关于webpack的文章：

1. webpack之谜 <http://www.tuicool.com/articles/I3E3mu7>
2. webpack傻瓜式指南(一) <https://zhuanlan.zhihu.com/p/20367175?columnSlug=FrontendMagazine>
3. webpack傻瓜式指南（二）<https://zhuanlan.zhihu.com/p/20397902?columnSlug=FrontendMagazine>
4. vue+webpack项目实战 <http://jiongks.name/blog/just-vue/>
5. 入门webpack 看这篇就够了 <http://www.jianshu.com/p/42e11515c10f>

#### webpack通用配置

```
var commonConfig = {
    devtool: 'eval-source-map', //方便本地调试
    entry: appJsonObj.entryObj, //上面tpl文件夹中每个页面对应的index.js入口文件
    output: {
        path: BUILD_PATH,  //可自定义，本文设定打包后的文件放在static文件夹下
        filename: 'js/[name].[hash].js',  
        publicPath: '/'
    },
    module: {  //一些解析vue文件、js文件、css等的包；需要安装的包是vue-loader,babel- loader,style-loader,css-loader,sass-loader,url-loader和file-loader    
        loaders: [
            {
                test: /\.vue$/,
                loader: 'vue'
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel'
            },
            {
                test: /\.s?css$/,
                loaders: [
                    'style',
                    'css',
                    'sass'
                ]
            },
            {
                test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
                loader: 'url',
                query: {
                    limit: 10000,
                    name: `image/[name].[hash:7].[ext]`
                }
            },
            {
                test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
                loader: 'url',
                query: {
                    limit: 10000,
                    name: `font/[name].[hash:7].[ext]`
                }
            }
        ]
    },
    //配置短路径引用
    resolve: { //配置模块寻找的方式和方法
        alias: { //当引用模块路径很长的时候，比如超级多‘../../../’,这时候我们就可以配置alias。当import模块的时候，webpack会将路径中出现的短路径替换成它指代的真实的路径
            page: path.resolve(APP_PATH, 'page'),
            assets: path.resolve(APP_PATH, 'assets'),
            services: path.resolve(APP_PATH, 'services'),
            node_modules: path.resolve(ROOT_PATH, 'node_modules'),
        },
        extensions: ['', '.js', '.vue'], //模块默认的后缀
        modules: [  //指定文件下查找模块
            APP_PATH,
            "node_modules",
            path.join(ROOT_PATH, '/src')
        ]

    },
    //webpack的一些插件
    plugins: appJsonObj.pluginArr.concat(
        [
            new webpack.EnvironmentPlugin(["NODE_ENV"]),
            new webpack.optimize.CommonsChunkPlugin({
                name: ["vendor"],
                filename: 'js/[name].[hash].js',
                minChunks: 2
            })
        ]
    )
};
```

**1. 添加es6支持**

需要安装的包是`babel-cli, babel-core, babel-loader, babel-preset-es2015, babel-preset-stage-1`

其中`babel-loader`让除了node_modules目录下的js文件都支持es6格式
```
module: {
    loaders: [
        {
            test: /\.js$/,
            exclude: /node_modules/,
            loader: 'babel',
            //.babelrc配置文件代替下面代码
            //query: {
            //    presets: ["es2015","stage-1"]
            //}
        }
    ]
}
```

配置`.babelrc`文件，设置一些presets就不需要在webpack的loader中再写了
```
//.babelrc文件的内容
{
    "presets": ['es2015','stage-1']
}
```

**2. 添加vue支持**

需要安装的包是vue,vue-loader,vue-template-compiler

**3. devtool方便本地调试**

配置 devtool:”eval-source-map”，生成下图文件，方便在生产环境进行本地调试

![](http://wilsonliu.cn/cdn/img/201701/51fae905f915dd6db6e1f7ca178a6616.png)

**4. webpack-dev-server插件**

提供的是内存级别的server，所以不会在dist中生成打包之后的文件夹，webpack-dev-server生成的包并没有放在你的真实目录中,而是放在了内存中.
得先启动这个服务，需要webpack-dev-server这个命令

`package.json`文件中配置`npm scripts`

```
 "scripts": {
    "start": "webpack-dev-server"
 }
```

**5. 热加载**

需要用到HotModuleReplacementPlugin这个插件，简称hmr；可以在devServer中配置hot:true,inline:true，或者在命令行中配置，这样就可以实现页面无刷新自动更新了！
![](http://wilsonliu.cn/cdn/img/201701/b89b13681549fd3dbd7ef2b06022feec.png)

*配置热加载时要注意的：*

![](http://wilsonliu.cn/cdn/img/201701/2a6bed7e4cd68167497714bd08beae6d.png)

**6. commonsChunkPlugin**

将多个entry里的公共模块提取出来放到一个文件里，这个插件可以用来将库和自己的代码分离，但每次打包都要构建，如果只是打包一些不变的库文件，`DLLPlugin`更合适。

```
plugins: [
     new webpack.optimize.CommonsChunkPlugin({
         name: ["vendor"],  //公共代码部分抽离出来到vendor.js中
         filename: 'js/[name].[hash].js',
         minChunks: 2
     })
]
```
代码的公共部分放在vendor.js文件中

![](http://wilsonliu.cn/cdn/img/201701/5f0fe44a6a4c3ba4f65950e2810c97f5.jpeg)

**7. html-webpack-plugin**

webpackConfig/utils文件：

```
//取出页面文件映射
function getHtmlPluginArr() {
    var pageList = JSON.parse(fs.readFileSync('./tpl/pageList.json', 'utf-8'));
    var resultObj = {
        "pluginArr": [],
        "entryObj": {
            global: [
                './src/global.js'  //全局js
            ]
        }
    };
    for (var index = 0; index < pageList.length; index++) {
        var page = pageList[index];
        resultObj.entryObj[page.outputPath] = page.entryPath;
        //除了共用的global，每个页面的js单独配置chunks，其中vendor是entry中的公共模块
        var chunks = ['vendor','global', page.outputPath];
        resultObj.pluginArr.push(
            new HtmlwebpackPlugin({
                chunks: chunks,
                title: '统一的title',
                template: './src/index.html', //html模板文件
                filename: page.outputPath + '.html',
                chunksSortMode: 'dependency',  //按chunks的顺序对js进行引入
                hash: true
            })
        );
    }
    return resultObj;
}
```
* 自定义html内容：上面的代码对每个页面都生成一个html，这个html中的内容可以自定义，比如我现在项目里用的是src文件夹下的index.html，只要在这个插件里配置template选项就行；

* 按序配置chunks：自动生成的html页面引用的js是按照上面设置的chunks顺序引用的，并且设置chunksSortMode为dependency；vendor中是一些公共的引用模块，global.js是全局js，page.outputPath是每个页面的js，依赖的顺序显而易见。


## 简易mock server
前端模拟向后端发送请求，接收后端的json格式的数据

### 详解mock-server.js
利用express搭的服务器环境，附express学习文档：<http://www.expressjs.com.cn/>

mock的内容下一章再说哈哈~~先偷个小懒，感兴趣的可以去我github看看

## 写在最后
鸡汤啥的就不多说啦，第一次分享文章，多多包涵哈~我认为学习的关键还是多动手，毕竟实践出真知，可以照着我的demo自己实现一遍，出现错误到stackoverflow上查查问题解决方案，自己的知识盲点就到google或者百度上搜索一下，相信肯定能解决你的问题，总之，鸡年大家一起努力！


**小广告**
欢迎关注我们的微信公众号:
![小前端FE(smallsfe)](http://blog.smallsfe.com/css/images/qrcode.jpg)
另外，也欢迎加入我们的微信群，添加`大大微信 zjz19910214`拉你入群。