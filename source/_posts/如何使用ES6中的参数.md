---
layout: postcd..
title: 如何使用ES6中的参数
date: 2017-03-07 09:14:12
tags: ES6 javascript function arguments parameters
category:
  - DarkZone
thumbnail: /assets/images/DarkZone/es6.jpg
---


ECMAScript 6（或者叫 ECMAScript 2015）是 ECMAScript 的最新标准，极大的提高了 JavaScript 中处理参数的能力。现在我们可以使用 rest 参数（rest parameters）、默认值（default values）和解构（destructuring）以及其他许多新的特性。本文我们将探索参数(arguments)和参数(parameter)的方方面面，看一下ES6是如何对他们改进和提升的。

## Arguments 和 Parameters 

arguments 和 Parameters 的含义通常是可以互换的。尽管如此，为了本文的目标，还是要做出区分。在大多数的标准中，函数声明时给出的叫做 parameters（或者叫 formal parameters），而传递给函数的叫做的 arguments（或者叫 actual arguments），看下面的函数：

```js
function foo(param1, param2) {
    // do something
}
foo(10, 20);
```

在这个函数中，`param1` 和 `param2` 是函数的 parameters，而传递给函数的值（`10` 和 `20`）是 arguments。

**译者注：本文后面不再区分 arguments 和 parameters，统一译作参数。:joy:**

## 扩展运算符（...) 

在 ES5 中，`apply()` 方法可以很方便将数组作为参数传递给函数，经常用于使用 `Math.max()` 来取得数组的最大值。看下面的代码段：

```js
var myArray = [5, 10, 50];
Math.max(myArray);    // Error: NaN
Math.max.apply(Math, myArray);    // 50
```


`Math.max()` 方法不支持数组，只接受数字作为参数。当数组传递给函数，函数会抛出错误。但是当使用 `apply()` 方法后，数组变成了一个个单独的数组传递给了函数，所以 `Math.max()` 就能够正确的执行了。

幸运的是，ES6 给我们带来了扩展运算符，我们就不必再继续使用 `apply()` 方法了。我们可以将表达式轻松的展开为多个参数。

```js
var myArray = [5, 10, 50];
Math.max(...myArray);    // 50
```

在这里我们通过扩展运算符将 `myArray` 展开成了一个个单独的值。虽然 ES5 中我们可以通过 `apply()` 方法来模拟扩展运算符，但是语法上让人迷惑，并且缺少可扩展性。扩展运算符不仅易于使用，还带来了许多新的特性。比如，你可以在函数调用时多次使用扩展运算符，并且还可以和其他参数混合在一起。

```js
function myFunction() {
  for(var i in arguments){
    console.log(arguments[i]);
  }
}
var params = [10, 15];
myFunction(5, ...params, 20, ...[25]);    // 5 10 15 20 25
```

扩展运算符另一大好处就是他可以很容易的和构造函数（constructor）一起使用：

```js
new Date(...[2016, 5, 6]);    // Mon Jun 06 2016 00:00:00 GMT-0700 (Pacific Daylight Time)
```

当前我们可以使用 ES5 来重写上面的代码，不过我们需要一个复杂的方法来避免一个类型错误：

```js
new Date.apply(null, [2016, 4, 24]);    // TypeError: Date.apply is not a constructor
new (Function.prototype.bind.apply(Date, [null].concat([2016, 5, 6])));   // Mon Jun 06 2016 00:00:00 GMT-0700 (Pacific Daylight Time)
```

## REST 参数

rest 参数和扩展运算符是一样的语法，但是他不是将数组展开成一个个的参数，而是将一个个参数转换为数组。

**译者注：rest 参数和扩展运算符虽然一样的语法，在这里你就可以看出作者强调的 arguments 和 parameters 的区别了。扩展运算符用于函数调用的参数（arguments）中，而 rest 参数用于函数声明的参数（parameters）中。**

```js
function myFunction(...options) {
     return options;
}
myFunction('a', 'b', 'c');      // ["a", "b", "c"]
```

如果没有提供参数，rest 参数会被设置为空数组：

```
function myFunction(...options) {
     return options;
}
myFunction();      // []
```

当创建可见函数（接受数量可变的参数的函数）的时候，rest 参数就显得十分有用。因为 rest 参数是一个数组，所以可以很方便的替换 `arguments` 对象（将会在下文讨论）。看下面一个使用 ES5 编写的方法：

```js
function checkSubstrings(string) {
  for (var i = 1; i < arguments.length; i++) {
    if (string.indexOf(arguments[i]) === -1) {
      return false;
    }
  }
  return true;
}
checkSubstrings('this is a string', 'is', 'this');   // true
```


这个函数的作用是检查一个字符串是否包含指定的一系列字符串。这个函数的第一个问题就是，我们必须查看函数体才知道函数接受多个参数。另外 `arguments` 的迭代必须从 1 开始，因为 `arguments[0]` 是第一个参数。如果我们稍后给第一参数之后再添加参数，或许我们就忘记更新这个循环了。使用 rest 参数，我们可以很轻易的避开这个问题：

```js
function checkSubstrings(string, ...keys) {
  for (var key of keys) {
    if (string.indexOf(key) === -1) {
      return false;
    }
  }
  return true;
}
checkSubstrings('this is a string', 'is', 'this');   // true
```

函数的输出和上一个函数一样。再重复一次，`string` 参数作为第一个参数传入，剩下的参数被塞进一个数组并且赋值给了变量 `keys`。

使用 rest 参数代替 `arguments` 不仅提高了代码的可读性，并且避免了 JavaScript 中的[性能问题](https://github.com/petkaantonov/bluebird/wiki/Optimization-killers#3-managing-arguments)。尽管如此，rest 参数并不能无限制使用，举个例子，它只能是最后一个参数，否则会导致语法错误。

```js
function logArguments(a, ...params, b) {
        console.log(a, params, b);
}
logArguments(5, 10, 15);    // SyntaxError: parameter after rest parameter
```

另一个限制方法声明时只允许一个 rest 参数：

```js
function logArguments(...param1, ...param2) {
}
logArguments(5, 10, 15);    // SyntaxError: parameter after rest parameter
```


## 默认值

### ES5 中的默认参数

ES5 中 JavaScript 并不支持默认值，但这里有个很简单的实现，使用 `OR`
运算符（`||`），我们可以很容易的模拟默认参数，看下面的代码：

```js
function foo(param1, param2) {
   param1 = param1 || 10;
   param2 = param2 || 10;
   console.log(param1, param2);
}
foo(5, 5);  // 5 5
foo(5);    // 5 10
foo();    // 10 10
```

这个函数期望接收两个参数，但当无参数调用时，它会使用默认值。在函数内，缺失的参数自动设置为 undefined，所以我们检查这些参数，并给他们设置默认值。为了检测缺失的参数并设置默认值，我们使用 `OR` 运算符（`||`）。这个运算符首先检查第一个值，如果是 [truthy](https://developer.mozilla.org/en-US/docs/Glossary/Truthy)，运算符会返回它，否则返回第二个参数。

这种方法在函数内很常用，但也存在瑕疵。如果传递 `0` 或者 `null` 也会返回默认值。因为它们被认为是 falsy 值。所以如果我们确实需要给函数传递 `0` 或者 `null`，我们需要换种方法来检测参数是否缺失：

```js
function foo(param1, param2) {
  if(param1 === undefined){
    param1 = 10;
  }
  if(param2 === undefined){
    param2 = 10;
  }
  console.log(param1, param2);
}
foo(0, null);    // 0, null
foo();    // 10, 10
```

在这个函数中，通过检查参数的类型是否为 undefined 来确定是否要赋予默认值。这种方法代码量稍微大一些，但更安全，可以让我们给函数传递 `0` 或者 `null`。

### ES6 中的默认参数

ES6 中，我们不必再检查参数是否为 undefined 来模拟默认参数，我们可以直接将默认参数函数声明中。

```js
function foo(a = 10, b = 10) {
  console.log(a, b);
}
foo(5);    // 5 10
foo(0, null);    // 0 null
```

正如你所看到的，忽略参数返回了默认值，但传递 `0` 或者 `null` 并没有。我们甚至可以使用函数来产生参数的默认值：

```js
function getParam() {
    alert("getParam was called");
    return 3;
}
function multiply(param1, param2 = getParam()) {
    return param1 * param2;
}
multiply(2, 5);     // 10
multiply(2);     // 6 (also displays an alert dialog)
```

需要注意的是，只有缺少第二个参数的时候，`gegParam` 方法才会执行，所以当我们使用两个参数 `multiply()` 的时候并不会弹出 alert。

默认参数另一个有意思的特性是在方法声明是可以引用其他参数和变量作为默认参数：

```js
function myFunction(a=10, b=a) {
     console.log('a = ' + a + '; b = '  + b);
}
myFunction();     // a=10; b=10
myFunction(22);    // a=22; b=22
myFunction(2, 4);    // a=2; b=4
```

甚至可以在函数声明的时候执行操作符：

```js
function myFunction(a, b = ++a, c = a*b) {
     console.log(c);
}
myFunction(5);    // 36
```

注意：不像其他语言，JavaScript 是在调用时才计算默认参数的：

```js
function add(value, array = []) {
  array.push(value);
  return array;
}
add(5);    // [5]
add(6);    // [6], not [5, 6]
```

## 解构赋值

解构赋值是 ES6 的新特性，让我们可以从数组或者对象中提取值并赋值给变量，语法上类似于对象和数组字面量。当给函数传参时，这种语法清晰且易于理解并且很实用。

在 ES5 中，经常使用配置对象来处理大量的的可选参数，尤其是属性的顺序无关紧要的时候，看下面的函数：

```js
function initiateTransfer(options) {
    var  protocol = options.protocol,
        port = options.port,
        delay = options.delay,
        retries = options.retries,
        timeout = options.timeout,
        log = options.log;
    // code to initiate transfer
}
options = {
  protocol: 'http',
  port: 800,
  delay: 150,
  retries: 10,
  timeout: 500,
  log: true
};
initiateTransfer(options);
```

这种模式 JavaScript 开发者经常使用，并且很好用。但我们必须进入函数体内才知道到底需要多少参数，使用解构参数赋值，我们可以在函数声明时很清晰的指定需要的参数。

```js
function initiateTransfer({protocol, port, delay, retries, timeout, log}) {
     // code to initiate transfer
};
var options = {
  protocol: 'http',
  port: 800,
  delay: 150,
  retries: 10,
  timeout: 500,
  log: true
}
initiateTransfer(options);
```

在这个函数中，我们使用了对象解构模式，而不是一个配置型对象，让我们的代码更加清晰易读。

我们也可以混用解构参数和普通参数：

```js
function initiateTransfer(param1, {protocol, port, delay, retries, timeout, log}) {
     // code to initiate transfer
}
initiateTransfer('some value', options);
```

需要注意，如果函数调用时解构参数缺失会抛出一个类型错误：

```js
function initiateTransfer({protocol, port, delay, retries, timeout, log}) {
     // code to initiate transfer
}
initiateTransfer();  // TypeError: Cannot match against 'undefined' or 'null'
```

当我们的参数是必须的，这种行为我们是想要的，但是如果我们期望参数可选呢？为阻止这种错误，我们需要给解构参数赋一个默认值：

```js
function initiateTransfer({protocol, port, delay, retries, timeout, log} = {}) {
     // code to initiate transfer
}
initiateTransfer();    // no error
```


在这个函数中，我们给解构参数赋了一个空对象作为默认值。现在如果函数调用时没有赋予参数，不会抛出错误。

我们也可以给解构参数每个属性都赋默认值：

```js
function initiateTransfer({
    protocol = 'http',
    port = 800,
    delay = 150,
    retries = 10,
    timeout = 500,
    log = true
}) {
     // code to initiate transfer
}
```

在这个例子中，每个属性都被赋予默认值，就无需在函数体内手动检查 undefined 的参数再赋予默认值。

## 参数传递

函数传参有两种方式：引用传递和值传递。如果是引用传递，修改参数会引起全局的变化，如果是值传递，只会引起函数内的变化。

在一些语言中，像 Visual Basic 和 PowerShell，我们可以选择声明是值传递还是引用传递，但 JavaScript 不是这样。

### 值传递

严格来说，JavaScript只能值传递。当我们通过值传递给函数传参，就在函数作用域内创建了这个值得副本。所以任何值得变化都只会反映在函数内部。看下面的例子：

```js
var a = 5;
function increment(a) {
    a = ++a;
    console.log(a);
}
increment(a);   // 6
console.log(a);    // 5
```

在这里，在函数内部修改修改参数并不会影响到原始值。所以在函数外打印这个变量，得到的结果始终是 `5`。

### 引用传递

在 JavaScript 中，所有的都是值传递，但是当我们传递一个变量指向一个对象（包括数组），这个“值”就指向了这个对象，改变了对象的某个属相也会引起其关联对象的改变。

看这个函数：

```js
function foo(param){
    param.bar = 'new value';
}
obj = {
    bar : 'value'
}
console.log(obj.bar);   // value
foo(obj);
console.log(obj.bar);   // new value
```

正如你看到的，对象的属性在函数体内部被修改，但是却影响到了函数外部的对象。

当我们传递一个非原始的值，像数组或者对象，程序会在内存中创建一个对象，指向原始地址。如果被修改，原始值也会随之修改。

## 类型检查和缺失或多余参数

在强类型的语言中，我们必须在函数声明时声明参数的类型，但 JavaScript 中没有这种特性，在 JavaScript 中，并不关心传递给函数的参数的类型和个数。

假设我们有一个函数，仅接受一个参数。当我们调用这个函数的使用，我们并不限制到底传递给函数多少个参数，甚至可以选择不传，都不会产生错误。

参数的个数可以分为两种情况：

* #### 参数缺失
  缺失的变量赋值为 undefined
* #### 参数过多
  多余的参数会被忽略，但可以从 arguments 变量中取到（下文即将讨论）。

## 强制参数

函数调用中如果函数缺失，它会被设置为 undefined。我们可以利用这一点，如果参数缺失就抛出错误：

```js
function foo(mandatory, optional) {
    if (mandatory === undefined) {
        throw new Error('Missing parameter: mandatory');
    }
}
```

在 ES6 中，我们可以更近一步，使用默认参数来设置强制参数：

```js
function throwError() {
    throw new Error('Missing parameter');
}
function foo(param1 = throwError(), param2 = throwError()) {
    // do something
}
foo(10, 20);    // ok
foo(10);   // Error: missing parameter
```

## arguments 对象

在 ES4 的时候默认参数就被加入，来代替 `arguments` 对象，但 ES4 并没有实现。随着 ES6 的发布，JavaScript 现在官方支持了默认参数。但并没有取消支持 `arguments` 的计划。

`arguments` 对象是一个类数组的对象，可以在所有的函数中取到。`arguments` 通过数字索引来获取传入的参数，而不是通过参数的名字。这个对象允许我们给函数传入任意多的参数。看下面的代码判断：

```js
function checkParams(param1) {
    console.log(param1);    // 2
    console.log(arguments[0], arguments[1]);    // 2 3
    console.log(param1 + arguments[0]);    // 4
}
checkParams(2, 3);
```


这个函数期望传入一个参数，当我们传入两个参数调用它的时候，我们通过 `param1` 或者 `arguments[0]` 来获取第一个参数，但第二个参数只能通过 `arguments[1]` 获取。也即是说，`arguments` 对象可以和有命名的参数一起使用。

`arguments` 对象包含了所有传入函数的参数，并且索引的起始是 `1`。当我们希望获取更多的参数的时候，我们会使用 `arguments[2]` 、`arguments[3]` 等等。

我们可以跳过所有的参数命名设置，仅仅使用 `arguments` 对象：

```js
function checkParams() {
    console.log(arguments[1], arguments[0], arguments[2]);
}
checkParams(2, 4, 6);  // 4 2 6
```


实际上，命名的参数是一种方便，但不是必需的。同样的，rest 参数也可以用来显示传入的参数：

```js
function checkParams(...params) {
    console.log(params[1], params[0], params[2]);    // 4 2 6
    console.log(arguments[1], arguments[0], arguments[2]);    // 4 2 6
}
checkParams(2, 4, 6);
```

`arguments` 对象是一个类数组对象，但是缺少像 `slice` 和 `foreach` 等方法。为了在 `arguments` 对象上使用这些方法，需要将其转换为真实的数组：

```js
function sort() {
    var a = Array.prototype.slice.call(arguments);
    return a.sort();
}
sort(40, 20, 50, 30);    // [20, 30, 40, 50]
```

在这个函数中，使用 `Array.prototype.slice.call()` 快速将 `arguments` 对象转换为数组。然后使用 `sort` 方法进行排序。

ES6 有一种更直接的方法，`Array.from()`，ES6 新增的方法，用来通过类数组对象创建一个新的数组。

```js
function sort() {
    var a = Array.from(arguments);
    return a.sort();
}
sort(40, 20, 50, 30);    // [20, 30, 40, 50]
```

## length 属性

虽然 arguments 对象并不是严格意义的数组，但它有一个 `length` 属性，可以用来检查传递给函数的参数的个数。

```js
function countArguments() {
    console.log(arguments.length);
}
countArguments();    // 0
countArguments(10, null, "string");    // 3
```

通过使用 `length` 属性，我们可以更好的控制参数的数量。比如说，如果一个函数需要两个参数，我们就可以使用 `length` 属性来检查参数数量，如果少于期望数量就抛出错误。

```js
function foo(param1, param2) {
    if (arguments.length < 2) {
        throw new Error("This function expects at least two arguments");
    } else if (arguments.length === 2) {
        // do something
    }
}
```


rest 参数是数组，所以他也有 `length` 属性，我们用 ES6 来重写上面的方法：

```js
function foo(...params) {
  if (params.length < 2) {
        throw new Error("This function expects at least two arguments");
    } else if (params.length === 2) {
        // do something
    }
}
```

## Callee 和 Caller 属性

`callee` 属性指向当前正在运行的函数，而 `caller` 指向调用当前正在运行函数的函数。在 ES5 严格模式下，这些属性是被废弃掉的，如果要访问它们会抛出错误。

`arguments.callee` 属性在递归函数（递归函数是一个普通函数，通过它的签名指向自身）下很有用，尤其是函数的签名不可用时（也就是匿名函数）。因为匿名函数没有名字，唯一指向自身的方法就是通过 `arguments.callee`。

```js
var result = (function(n) {
  if (n <= 1) {
    return 1;
  } else {
    return n * arguments.callee(n - 1);
  }
})(4);   // 24
```

## 严格模式和非严格模式下的 arguments

在 ES5 非严格模式下， `arguments` 对象有一个不常用的特性：它保持和命名参数值同步。

```js
function foo(param) {
   console.log(param === arguments[0]);    // true
   arguments[0] = 500;
   console.log(param === arguments[0]);    // true
   return param
}
foo(200);    // 500
```

在函数内部，一个新的值赋给 `arguments[0]`。因为 `arguments` 一直和命名参数的值保持同步，`arguments[0]` 的改变也会引起 `param` 的改变。事实上，他们是同个变量的不同名称。在 ES5 严格模式下，这种令人迷惑的特性被移除了：

```js
"use strict";
function foo(param) {
   console.log(param === arguments[0]);    // true
   arguments[0] = 500;
   console.log(param === arguments[0]);    // false
   return param
}
foo(200);   // 200
```

这次，`arguments[0]` 的改变没有影响到 `param`，并且输出和期望一样。ES6下，输出结果和 ES5 的严格模式是一致的。但是请记住，在函数声明时使用了默认参数，`arguments` 不受影响。

```js
function foo(param1, param2 = 10, param3 = 20) {
   console.log(param1 === arguments[0]);    // true
   console.log(param2 === arguments[1]);    // true
   console.log(param3 === arguments[2]);    // false
   console.log(arguments[2]);    // undefined
   console.log(param3);    // 20
}
foo('string1', 'string2');
```


在这个函数中，尽管 `param3` 有默认值，但他和 `arguments[2]` 并不相等，因为只有两个参数传入了函数。也就是说，设置默认参数并不影响 arguments 对象。

## 结论

ES6 给 JavaScript 带来了许多大大小小的改进。越来越多的开发者开始使用 ES6，而且很多所有的特性都可以无障碍使用。本文我们学习了 ES6 是如何提升JavaScript 处理参数的能力的。但我们仅仅学了 ES6 的一点皮毛。更多的有趣的特性等着我们去挖掘！

* [ECMAScript 6 Compatibility Table](https://kangax.github.io/compat-table/es6/), Juriy Zaytsev
* “[ECMAScript 2015 Language Specification](http://www.ecma-international.org/ecma-262/6.0/),” ECMA International

--- 

看下时间现在正好是23：23，几乎用了一个下午和晚上把这篇文章读完又翻译完，这篇文章结合 ES5 和 ES6 来讲解，收益颇多。不过翻译水平有限，求多提意见多多指教 ~

原文地址： [How To Use Arguments And Parameters In ECMAScript 6](https://www.smashingmagazine.com/2016/07/how-to-use-arguments-and-parameters-in-ecmascript-6/)




**小广告**
欢迎关注我们的微信公众号:
![小前端FE(smallsfe)](http://blog.smallsfe.com/css/images/qrcode.jpg)
另外，也欢迎加入我们的微信群，添加`大大微信 zjz19910214`拉你入群。













