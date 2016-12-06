layout: postcd ..
title: ShellScript编程小结
date: 2016-05-20 12:56:03
tags: Javascript
category:
- 前端
---

## 前言
shell作为编程中不可或缺的一部分，平日里，我们经常会在shell中输入一些命令。有时候也需要完成一些复杂的操作，重复的输入多条相同的命令，过于费时和无趣。所以掌握shell script就显得非常有必要了，可以让你用编程的方式调用繁多的命令行工具。

最近，正好碰上一个一直拖着的需求，便抄起了shell解决掉了，写了人生中第一段shell script代码。

<!-- more -->

**需求如下**

从一个文件夹中获取一个文本，这个文本里记录了链接以及他对应的版本号。并到另外一个html文件夹中遍历所有html文件，将其中的链接中的@VERSION替换为相应的版本号。

## 流程
1. 创建一个shell脚本，例如`touch test.sh`
2. 在命令行中输入`chmod +x ./test.sh`，使这个文件变成一个可执行文件
3. 在这个脚本文件中书写代码，诸如"find ."
4. 在命令行中输入`./test.sh`，即可运行。
5. 结果，输出当前目录下所有文件夹与文件的名称

## shell script介绍
和所有的编程一样，shell脚本主要由自身语法，以及繁多的linux命令构成。我们只需要学习shell脚本自身的语法以及一些常用的linux命令即可，需要的时候可以查询相应的linux命令。

### shell script语法
因为篇幅限制，所以仅列出提纲，具体的学习可以参考文末的参考资料
- 变量
- 数组
- 传参
- 运算符
- 输入输出以及重定向
- 测试 test
- 流程控制
- 函数
- 文件包含

### linux命令
linux命令是linux强大的一个重要基础，分为以下5个部分。编程中，用对了指令可以减少许多工作。也正因为繁多的指令，给shell脚本带来了足够的能力。

- 系统管理
- 网络管理
- 软件 | 打印 | 开发 | 工具
- 文件目录管理
- 硬件 | 监测 | 内核 | Shell


## 实例讲解
代码分为三个函数，第一个配置初始化函数`init()`,第二个遍历文件夹函数`walk()`，第三个是对html文件的处理函数，运用sed正则替换html中的链接`html_into_ver()`

想要实际操作的可以拿这个[kindle文字伴侣](http://wilsonliu.cn)进行测试,这个项目是用去哪儿的前端构建工具fekit构建的。脚本名字为`export_html`，可以在命令行中输入`./export_html`进行测试，会多出一个`export_html`的文件夹，里面存放着所有的输出html文件。

[github项目地址](https://github.com/WilsonLiu95/kindleClipingDeal)

### 配置函数init()
这一部分主要是默认配置的设置
```
function init()
{
  # html_into_ver配置区
  de_reg_rule="\(.*\)\(http://localhost/kindleClipingDeal/prd/\)\(.*\)\(@VERSION\)\(.*\)\".*"
  de_cur_prefix="http://localhost/kindleClipingDeal" #当前prd前面的路径
  de_replace_prefix="http://wilsonliu.cn/kindleClipingDeal" #当前前缀替换后路径
  de_ver_file="ver/versions.mapping"  #当前存储版本号码的文件

  de_target_dir="export_html" #将html修改后，输出的目标文件夹
  de_source_dir="html" # 源文件夹为html

  # 如果目标文件夹存在，则先删除
  if [ -e ${de_target_dir} ]; then
    rm -rf ${de_target_dir}
  fi
  #首先复制源文件夹为输出文件夹，在输出文件夹
  cp -rf ${de_source_dir} ${de_target_dir}

  # walk 的3个参数配置
  de_dir_to_walk=${de_target_dir} #将要遍历操作文件夹
  de_walk_file_callback="html_into_ver" #文件处理回调函数
  de_walk_dir_callback="" #文件夹处理回调函数，非必要，可为空

  # 调用walk函数
  walk $de_dir_to_walk $de_walk_file_callback $de_walk_dir_callback
}
init; # 程序初始化执行
```
### 遍历文件夹函数walk()

```
<!-- #!/bin/bash -->

# walk 函数 三个配置
# 第一个是遍历的目标文件，第二个是对文件处理的调用函数,第三个是对文件夹处理的调用函数，
# 调用函数 有两个输入一个是遍历的文件夹，一个是当前文件夹
function walk()
{
  # ${1}为调用walk函数时传入的第一个参数
  for file in `ls ${1}` #ls输出当前路径下的所有文件以及文件夹，利用for in分别对其进行操作
  do
    path=${1}"/"${file} #拼接当前将要处理的文件或文件夹路径
    if [ -d ${path} ]  #-d 是测试其是否是文件夹
     then
      #  如果存在回调函数，则调用文件处理回调函数 并且输入遍历的目标文件夹以及当前文件夹路径
      if [ ${3} ] # ${3} 即为调用walk时输入的第三个参数，应该为文件夹处理函数
      then
        $3 $1 ${path} #调用${3}指向的函数，并传入当前所在路径以及要处理的文件夹路径
      fi
      # 对当前文件夹继续调用walk函数
      walk ${path} $2 $3 #遍历文件夹
    else
      # 调用文件处理函数对文件进行处理，并输入遍历的目标文件夹以及当前文件路径
      $2 $1 ${path}
    fi
  done
}
```
### html文件处理函数html_into_ver()
利用sed流编辑器进行正则匹配与替换
```
# 将html中的所有链接中的 VERSION 改为正确的版本号码
function html_into_ver()
{
  # 获取当前$2的html文件内所有的链接地址
  link=`sed -n "s#${de_reg_rule}#\2\3\4\5#p"  $2`
  # 获取当前$2的html文件内所有的连接路径
  link_path=`sed -n "s#${de_reg_rule}#\3\5#p" $2`

  i=1
  while [ `echo ${link} | cut -d " " -f $i` ]; do
    cur_link=`echo ${link} | cut -d " " -f $i` #html中的完整路径
    cur_link_path=`echo ${link_path} | cut -d " " -f $i` #html中的完整路径

    cur_version=`sed -n "s*${cur_link_path}#**p" ${de_ver_file}` #当前文件的版本号
    cur_replace_link=`echo ${cur_link} | sed -n   "s#\(.*\)\(@VERSION\)\(.*\)#\1@${cur_version}\3#p"` #当前替代cur_link的链接
    #因为sed -i这个命令在mac与linux上存在差异，mac上强制要求sed -i 后多一个参数用来指替备份文件名，可以用空字符来解决，mac上输出为Darwin，依次判断
    if [ `uname -s` == "Darwin" ]; then
      sed -i ""  "s#${cur_link}#${cur_replace_link}#" ${2} #直接对当前文件进行VERSION修改
    else
      sed -i     "s#${cur_link}#${cur_replace_link}#" ${2} #直接对当前文件进行VERSION修改
    fi
    # 循环的条件
    i=`expr $i + 1`
  done
  #统一修改链接前缀
  if [ `uname -s` == "Darwin" ]; then
    sed -i "" "s#${de_cur_prefix}#${de_replace_prefix}#g" ${2}  #修改链接的前置部分
  else
    sed -i "s#${de_cur_prefix}#${de_replace_prefix}#g" ${2}  #修改链接的前置部分
  fi
}
```
## 写在最后
shell编程的好处在于可以批量化自动化操作以提高开发效率，同时也可以用来解决许多问题，本身并不复杂，简单易学，功能强大。
希望大家都能够掌握这一工具。
## 参考资料
- [shell 教程 |菜鸟教程](http://www.runoob.com/linux/linux-shell.html)
- [linux命令大全](http://man.linuxde.net/)
