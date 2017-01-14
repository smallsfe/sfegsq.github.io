# Small Front End 
## SFE博客管理说明
SFE博客属于SFE全体成员所有，为了规范博客的管理，博客将由专人负责运营。  
SFE的成员有需要发布的文章请联系运营人员，提供相应文章(只支持markdown格式的文章)。  
运营人员只负责基本的发布，并没有义务也没有权利对文章进行修改，因此文章的格式请文章的作者自己好好把关。

当前运营负责人: WilsonLiu

## 运营人员须知
为了更好的搞事情，扩大组织影响力，SFE组织分为自由媒体与第三方论坛。  
我们将在第三方论坛注册官方账号，开设专栏，第三方论坛的发布将`delay`一天时间。

SFE各个媒体及论坛注册时须统一面貌如下：  
名称：小前端FE  
账号：smallsfe  


**自有媒体**  

1. 微博：`http://weibo.com/smallsfe`
2. 微信公众号： smallsfe(验证中)
3. SFE博客`http://blog.smallsfe.com`
4. QQ: 1926213114
5. email: sfegsq@qq.com

**第三方论坛地址**

1. segmentfault专栏： `https://segmentfault.com/blog/smallsfe`
2. CSDN专栏： `http://blog.csdn.net/smallsfe`


## 目录结构说明
```
├── README.md // hexo分支的README文档
├── _config.yml // 整个项目的hexo配置
├── db.json // 文件形式的数据，每次由hexo g 生成 
├── node_modules 
├── package.json 
├── public // 通过hexo g 指令生成，即将source中的文件编译成html静态文件发布到此文件夹下
├── scaffolds  
│   └── draft.md // 草稿的模板
├── source 
│   ├── CNAME // 将blog.smallsfe.com映射到sfegsq.github.io目录的规则文件
│   ├── README.md // master分支的README.md
│   ├── _drafts // 草稿目录
│   ├── _posts // publish后的文章，线上可以直接看见
│   └── about  // menu中关于的文件
│   └── assets  // source目录下可能索引的静态文件，主要指照片
└── themes // 下面是具体的hexo博客主题
```

## 两个分支master与hexo说明
master分支为静态文件，github将其作为网站静态资源用户展示。不允许对master分支进行操作！
hexo分支为开发分支，所有操作均在此分支上。

## 操作步骤
### 安装
[hexo安装](https://hexo.io/zh-cn/docs/),之后，clone该项目到本地并且checkout到hexo分支。   
然后在项目根目录下安装依赖，`npm install`。  
因为themes下的被使用的主题push的时候会被`gitignore`掉，所以我把博客主题在根目录下备份了一份。每次初始化的时候记得将`hueman`下的
文件复制到`themes/hueman`目录下。

### 新建文章草稿
首先 `hexo new draft 上海滩一姐奇遇记`，即会在`source/_drafts`下生成`上海滩一姐奇遇记.md`文件。
`hexo serve --draft`在本地开启服务器，同时可以看到草稿。

### 发布文章
请仔细检查`source/_drafts`中的待发布文件，需确认无误。`hexo publish 上海滩一姐奇遇记`既可以将`source/_drafts`中的“上海滩一姐奇遇记”文件发布到`source/_posts`中。

### 推送到线上
`hexo g`，hexo会将`source`目录下的文件编译成静态文件，存储到`public`目录下，最后`hexo d`将`public`目录下的代码，
复制到`.deploy_git`目录下，`.deploy_git`下的文件将线上的master分支代码覆盖。

## 可能遇到的问题
### deploy卡住发不上去
有时候卡主可能是因为网的问题，这时候需要等等。
如果不是网的问题，可以试试先 `hexo clean` 清空 `public` 目录下的文件，重新编译生成`hexo g`。
### 网站404了
检查线上master分支CNAME文件存不存在。该文件写在`source`目录下，编译的时候会复制到`public`目录下，最后发布到线上。
```
// CNAME文件内容
blog.smallsfe.com
```
### 我文章里的图放在哪里
所有images统一存储在 `source/assets/images`目录下，文章中需要引用的资源也放在这个目录下。并且使用绝对路径例如`/assets/images/logo.png`。

### 发上去了，白屏
检查`public/index.html`文件,如果为空白表示主题出现了问题，记得将`hueman`目录下的文件复制到`themes/hueman`目录下。