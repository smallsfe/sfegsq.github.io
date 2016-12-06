---
title: calc 与 box-sizing 的替代
date: 2015-12-22
tags: Javascript
category:  前端
---

## 背景

之前发现calc这个布局新属性之后就非常喜欢，爱不释手。在公司的实习的时候，开发微信端的页面，使用了几次calc，后来发现在Android的上的不支持～蛋疼。于是到处找替代方案，终于在stackoverflow上找到一个满意的答复，好～接下来进入正文～

## calc 与 box-sizing 简单介绍
### calc 属性

`calc()`能让你给元素的做计算，你可以给一个div元素，使用百分比、em、px和rem单位值计算出其宽度或者高度，比如说“width:calc(50% + 2em)”，这样一来你就不用考虑元素DIV的宽度值到底是多少，而把这个烦人的任务交由浏览器去计算。
<!-- more -->
`calc()`的运算规则：

- 使用“+”、“-”、“*” 和 “/”四则运算;
- 可以使用百分比、px、em、rem等单位；
- 可以混合使用各种单位进行计算；
- 表达式中有“+”和“-”时，其前后必须要有空格，如"width: calc(12%+5em)"这种没有空格的写法是错误的；
- 表达式中有“*”和“/”时，其前后可以没有空格，但建议留有空格。

**兼容性**

浏览器对calc()的兼容性还算不错，在IE9+、FF4.0+、Chrome19+、Safari6+都得到较好支持，同样需要在其前面加上各浏览器厂商的识别符，不过可惜的是，移动端的浏览器还没仅有“firefox for android 14.0”支持，其他的全军覆没。

![caniuse](https://sfault-image.b0.upaiyun.com/819/632/819632304-5679632b2a6b4)

### box-sizing

**语法**  
```
    box-sizing ： content-box || border-box || inherit
```
**取值说明**
- `border-box`：元素的宽度/高度（width/height）等于元素边框宽度（border）加上元素内边距（padding）加上元素内容宽度/高度（content width/height）即：Element Width/Height = border+padding+content width/height。

- `content-box`：元素的宽度/高度等于元素内容的宽度/高度。
**兼容性**

![caniuse](https://sfault-image.b0.upaiyun.com/192/633/1926332970-5679633e27564)

## 布局比较
相比于box-sizing而言 calc的Android browser的支持性太差了，所以布局的时候，box-sizing可以用来解决calc的问题

```
//html
<div class="sideBar">sideBar</div>
<div class="content">content</div>


//css
  //使用calc
.content {
  width: 65%；  //照顾Android 平稳退化
  width: calc(100% - 300px);
}

  //使用box-sizing
  .sideBar {
     position: absolute; 
     top:0;
     left:0;
     width: 300px;
}
.content {
    padding-left: 300px;
    width: 100%;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
``` 
以上的代码来自于stackoverflow，非常棒的解决方案～


## bootstrap中的box-sizing
之后在工作中，发现bootstrap的源码有这么一段代码～
box-sizing这个货还是非常有用的呀～
```
* {
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
}
*:before,
*:after {
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
}
```

## 最后记
 
第一篇通过解决自己遇到的问题而来的文章～

参考资料：

- [CSS3的calc()使用](http://www.w3cplus.com/css3/how-to-use-css3-calc-function.html)  
- [CSS3  Box-sizing](http://www.w3cplus.com/content/css3-box-sizing)
- [Stackoverflow的问答](http://stackoverflow.com/questions/16034397/css-calc-alternative)
