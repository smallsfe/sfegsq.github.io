title: innerHTMLVScreateElement
date: 2016-01-21 12:49:25
tags: Javascript
category:
- 前端
---
两者生成dom的方式有什么优劣呢？
首先让我们看一个小问题再引入正题～
<!-- more -->
## 如何重复插入一个相同的html结构呢？

```JavaScript
//错误的
window.onload = function(){
var el = document.createElement('div');
el.appendChild(document.createTextNode('Hi'));
for (var i = 10; i > 0; --i)
document.body.appendChild(el);
};  //同一元素无法重复插入，
```

```JavaScript
//正确的　　同时注意不要设置插入多次的元素的id,否则造成错误
window.onload = function(){
var el = document.createElement('div');
el.appendChild(document.createTextNode('Hi'));
for (var i = 10; i > 0; --i)
document.body.appendChild(el.cloneNode(true));
};
```
**上述之所以要用clone，是因为当时我的需求是克隆一个复杂的html结构，而并非生成一个简单的dom元素。**

## 重排与重绘
上述方法，虽然能够成功生成相同的dom元素，但是性能上是存在问题的。
每次插入dom元素到body后，dom树会重排，之后页面会因为新的dom元素的插入而重新绘制，这两个过程是极其耗时的。
因此，推荐使用文档碎片document.createDocumentFragment()。

使用方法

```JavaScript
//利用文档碎片 提高性能  frag相当于一个容器 frag并不会插入body而是把frag的内部元素全部插入body
window.onload = function(){
var frag = document.createDocumentFragment();
var el = document.createElement('div');
el.appendChild(document.createTextNode('Hi'));
for (var i = 10; i > 0; --i)
frag.appendChild(el.cloneNode(true));  //先将生成的dom全部插入frag先，这个过程并不会触发重排与重绘
};
document.body.appendChild(frag); //将生成的frag插入body中，将10此重排重绘的过程压缩为一次
```
接下来，进入正题的比较
## innerHTML vs createElement
生成Dom的两种方式，孰优孰劣呢？
就我们的经验看来，innerHTML这种采用字符串拼接生成dom的方式似乎更加方便，并且效率更高。但是那原生的createElement又有什么优势呢？
以下，优势观点来自于stack overflow的言论，翻译的也不一点准确，欢迎探讨。

1. createElement，当元素插入后仍然保留对dom元素的指针。而innerHTML插入后，并没有对dom元素的指针，你需要再通过getElementById重新选取。
2. createElement能够获得事件处理函数，而innerHTML生成的新dom无法获得原先设置的事件处理函数。
3. 某些情况下，createElement更加快速。如果你需要反复操作字符串，在每次处理后再次插入。每次插入都将进行解析与制作dom，在性能上会很差。
4. 可读性与可维护上createElement会优秀一些

下面提供一段封装好了的让你方便的使用createElement的函数
```JavaScript
function isArray(a) {
    return Object.prototype.toString.call(a) === "[object Array]";
}

function make(desc) {
    if (!isArray(desc)) {
        return make.call(this, Array.prototype.slice.call(arguments));
    }

    var name = desc[0];
    var attributes = desc[1];

    var el = document.createElement(name);

    var start = 1;
    if (typeof attributes === "object" && attributes !== null && !isArray(attributes)) {
        for (var attr in attributes) {
            el[attr] = attributes[attr];
        }
        start = 2;
    }

    for (var i = start; i < desc.length; i++) {
        if (isArray(desc[i])) {
            el.appendChild(make(desc[i]));
        }
        else {
            el.appendChild(document.createTextNode(desc[i]));
        }
    }

    return el;
}
```

使用方式

`make(["p", "Here is a ", ["a", { href:"http://www.google.com/" }, "link"], "."]);`

你会得到这样一个html结构

`<p>Here is a <a href="http://www.google.com/">link</a>.</p>`

综上，两者各有各的好处。无疑，在大多数情况下，innerHTML更为快速且更加易用，但是使用innerHTML的时候小心上述的那个问题就好。

- [Advantages of createElement over innerHTML? ](http://stackoverflow.com/questions/2946656/advantages-of-createelement-over-innerhtml)

- [Advantages of createElement over innerHTML?](http://stackoverflow.com/questions/2946656/advantages-of-createelement-over-innerhtml)

- [JavaScript: Is it better to use innerHTML or (lots of) createElement calls to add a complex div structure?](http://stackoverflow.com/questions/737307/javascript-is-it-better-to-use-innerhtml-or-lots-of-createelement-calls-to-ad)
