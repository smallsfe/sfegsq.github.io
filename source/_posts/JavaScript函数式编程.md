layout: postcd ..
title: JavaScript函数式编程
date: 2016-12-04 13:10:35
tags:
---
## 摘要
以往经常看到”函数式编程“这一名词，却始终没有花时间去学习，暑期实习结束之后一直忙于边养老边减肥，**81天成功瘦身30斤+** ，开始回归正常的学习生活。
便在看《JavaScript函数式编程》这本书，以系统了解函数式编程的知识。本文试图尽可能系统的描述JavaScript函数式编程。当然认识暂时停留于本书介绍的程度，如有错误之处，还请指正。
<!-- more -->
注：本书采用的函数式库Underscore。一下部分代码运行时，需引入Underscore。
## 函数式编程简介
我们用一句话来直白的描述函数式编程：
> 函数式编程通过使用函数来将值转换成抽象单元，接着用于构建软件系统。

概括的来说，函数式编程包括以下技术
- 确定抽象，并为其构建函数
- 利用已有的函数来构建更为复杂的抽象
- 通过将现有的函数传给其他函数来构建更加复杂的抽象

注：JavaScript并不仅限于函数式编程语言，以下是另外3种常用的编程方式。
- 命令式编程： 通过详细描述行为的编程方式
- 基于原型的面向对象编程： 基于原型对象及其实例的编程方式
- 元编程：对JavaScript执行模型数据进行编写和操作的编程方式
## 函数式编程的一些特性
### 纯函数
纯函数坚持以下属性（坚持纯度的标准不仅将有助于使程序更容易测试，也更容易推理。）
- 其结果只能从它的参数的值来计算
- 不能依赖于能被外部操作改变的数据
- 不能改变外部状态
### 不变性 —— 没有副作用
所谓"副作用"（side effect），指的是函数内部与外部互动（最典型的情况，就是修改全局变量的值），产生运算以外的其他结果。
函数式编程强调没有"副作用"，意味着函数要保持独立，所有功能就是返回一个新的值，没有其他行为，尤其是不得修改外部变量的值。

### 不修改状态
上一点已经提到，函数式编程只是返回新的值，不修改系统变量。因此，不修改变量，也是它的一个重要特点。在其他类型的语言中，变量往往用来保存"状态"（state）。不修改变量，意味着状态不能保存在变量中。
函数式编程使用参数保存状态，最好的例子就是递归。下面的代码是一个将字符串逆序排列的函数，它演示了不同的参数如何决定了运算所处的"状态"。
```
function reverse(string) {
　　　　if(string.length == 0) {
　　　　　　return string;
　　　　} else {
　　　　　　return reverse(string.substring(1, string.length)) + string.substring(0, 1);
　　　　}
　　}
```
### 函数是一等公民
“一等”这个术语通常用来描述值。当函数被看作“一等公民”时，那它就可以去任何值可以去的地方，很少有限制。比如数字在Javascript里就是一等公民，同程
作为一等公民的函数就会拥有类似数字的性质。
```
var fortytwo = function(){return 42} // 函数与数字一样可以存储为变量
var fortytwo = [32, function(){return 42}] // 函数与数字一样可以存储为数组的一个元素
var fortytwo = {number: 32, fun: function(){return 42}} // 函数与数字一样可以作为对象的成员变量
32 + (function(){return 42}) () // 函数与数字一样可以在使用时直接创建出来

// 函数与数字一样可以被传递给另一个函数
function weirdAdd(n, f){ return n + f()}
weirdAdd(32, function(){return 42})

// 函数与数字一样可以被另一个函数返回
return 32;
return function(){return 42}
```

## Applicative编程
Applicative编程是特殊函数式编程的一种形式。Applicative编程的三个典型例子是`map,reduce,filter`
> 函数A作为参数提供给函数B。 (即定义一个函数，让它接收一个函数，然后调用它)

```
_.find(["a","b",3,"d"], _.isNumber) // _.find与_.isNumber都是Underscore中的方法

// 自行实现一个Applicative函数
function exam(fun, coll) {
  return fun(coll);
}
// 调用
exam(function(e){
    return e.join(",")
}, [1,2,3])
// 结果 ”1,2,3“
```

## 高阶函数
定义:一个高阶函数应该可以执行以下至少一项操作。
- 以一个函数作为参数
- 返回一个函数作为结果

### 以其他函数为参数的函数
#### 关于传递函数的思考： max，finder，best
```
// max是一个高阶函数
var people = [{name: "Fred", age: 65}, {name: "Lucy", age: 36}];
_.max(people, function(p) { return p.age }); 
//=> {name: "Fred", age: 65}
```
但是，在某些方面这个函数是受限的，并不是真正的函数式。具体来说，对于`_.max`而言,比较总是需要通过大于运算符（>）来完成。

不过，我们可以创建一个新的函数finder。它接收两个函数：一个用来生成可比较的值，而另一个用来比较两个值并返回当中的”最佳“值。
```
function finder(valueFun, bestFun, coll) {
  return _.reduce(coll, function(best, current) {
    var bestValue = valueFun(best);
    var currentValue = valueFun(current);

    return (bestValue === bestFun(bestValue, currentValue)) ? best : current;
  });
}
```
在任何情况下，我们现在都可以用`finder`来找到不同类型的”最佳“值：
```
finder(function(e){return e.age}, Math.max, people) 
// => {name: ”Fred", age: 65}

finder(function(e){return e.name}, function(x, y){
    return (x.charAt((0) === "L") ? x : y),people}) // 偏好首字母为L的人
// => {name:"Lucy", age: 36}
``` 
**缩减一点**
函数`finder`短小精悍，并且能按照我们预期来工作，但为了满足最大程度的灵活性，它重复了一些逻辑。
```
//  在 finder函数中
return (bestValue === bestFun(bestValue, currentValue)) ? best : current;
// 在输入的函数参数中
return (x.charAt((0) === "L") ? x : y
```
你会发现上述两者的逻辑是完全相同的。finder的实现可以根据以下两个假设来缩减。
- 如果第一个参数比第二个参数“更好”，比较最佳值的函数返回为true
- 比较最佳值的函数知道如何“分解”它的参数

在以上假设的基础下，我们可以实现一个更简洁的best函数。
```
function best(fun, coll) {
  return _.reduce(coll, function(x, y) {
    return fun(x, y) ? x : y;
  });
}

best(function(x,y) { return x > y }, [1,2,3,4,5]);
//=> 5
```
#### 关于传递函数的更多思考：重复，反复和条件迭代
首先，从一个简单的函数`repeat`开始。它以一个数字和一个值为参数，将该值进行多次复制，并放入一个数组中：
```
function repeat(times, VALUE) {
  return _.map(_.range(times), function() { return VALUE; });
}

repeat(4, "Major");
//=> ["Major", "Major", "Major", "Major"]
```
**使用函数，而不是值**
通过将参数从值替换为函数，打开了一个充满可能性的世界。
```
function repeatedly(times, fun) {
  return _.map(_.range(times), fun);
}

repeatedly(3, function() {
  return Math.floor((Math.random()*10)+1);
});
//=> [1, 3, 8]
```
**再次强调，“使用函数，而不是值”**
我们常常会知道函数应该被调用多少次，但有时候也知道什么时候推出并不取决于“次数”，而是条件！因此我可以定义另一个名为`iterateUntil`的函数。
`iterateUntil`接收2个参数，一个用来执行一些动作，另一个用来进行结果检查。
```
function iterateUntil(fun, check, init) {
  var ret = [];
  var result = fun(init);

  while (check(result)) {
    ret.push(result);
    result = fun(result);
  }

  return ret;
};
```

### 返回其他函数的函数
```
function invoker (NAME, METHOD) { // 接收一个方法，并在任何给定的对象上调用它
  return function(target /* args ... */) {
    if (!existy(target)) fail("Must provide a target");

    var targetMethod = target[NAME];
    var args = _.rest(arguments);

    return doWhen((existy(targetMethod) && METHOD === targetMethod), function() {
      return targetMethod.apply(target, args);
    });
  };
};

var rev = invoker('reverse', Array.prototype.reverse);

_.map([[1,2,3]], rev);
//=> [[3,2,1]]
```
**高阶函数捕获参数**
高阶函数的参数是用来“配置”返回函数的行为的。对于`makeAdder`而言，它的参数配置了其返回函数每次添加数值的大小
```
function makeAdder(CAPTURED) {
  return function(free) {
    return free + CAPTURED;
  };
}
var add10 = makeAdder(10);

add10(32);
//=> 42
```
**捕获变量的好处**
用闭包来捕获增加值，并用作后缀。（但这样并不具有引用透明）
```
function makeUniqueStringFunction(start) {
  var COUNTER = start;

  return function(prefix) {
    return [prefix, COUNTER++].join('');
  }
};
var uniqueString = makeUniqueStringFunction(0);

uniqueString("dari");
//=> "dari0"

uniqueString("dari");
//=> "dari1"
```
## 由函数构建函数
### 函数式组合的精华
> 精华：使用现有的零部件来建立新的行为，这些新行为同样也成为了已有的零部件。
```
// 接收一个或多个函数，然后不断尝试依次调用这些函数的方法，直到返回一个非`undefined`的值
function dispatch(/* funs */) {
  var funs = _.toArray(arguments);
  var size = funs.length;

  return function(target /*, args */) {
    var ret = undefined;
    var args = _.rest(arguments);

    for (var funIndex = 0; funIndex < size; funIndex++) {
      var fun = funs[funIndex];
      ret = fun.apply(fun, construct(target, args));

      if (existy(ret)) return ret;
    }

    return ret;
  };
}

var str = dispatch(invoker('toString', Array.prototype.toString),
invoker('toString', String.prototype.toString));

str("a");
//=> "a"

str(_.range(10));
//=> "0,1,2,3,4,5,6,7,8,9"
```
在这里，我们想做的只是返回一个遍历函数数组，并`apply`给一个目标对象的函数，返回第一个存在的值。`dispatch`满足了多态JavaScript
函数的定义。这样简化了委托具体方法的任务。例如，在underscore的实现中，你经常会看到许多不同的函数重复这样的模式。
1. 确保目标的存在
2. 检查是否有原生版本，如果是则使用它
3. 如果没有，那么做一些实现这些行为的具体任务。
    - 做特定类型的任务（如适用）
    - 做特定参数的任务（如适用）
    - 做特定个参数的任务（如适用）

同样的模式也体现在`Underscore`的函数`_.map()`的实现中：
```
  _.map = _.collect = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length,
        results = Array(length);
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      results[index] = iteratee(obj[currentKey], currentKey, obj);
    }
    return results;
  };
```
使用`dispatch`可以简化一些这方面的代码，并且更容易扩展。想象一下，你正在写一个可以为数组和字符串类型生成字符描述的
函数。使用dispatch则可以优雅的实现：
```
var str = dispatch(invoker('toString', Array.prototype.toString),
invoker('toString', String.prototype.toString));

str("a");
//=> "a"

str(_.range(10));
//=> "0,1,2,3,4,5,6,7,8,9"
```
### 柯里化 Curring
> 柯里化函数为每一个逻辑参数返回一个新函数。

![柯里化图形描述](http://wilsonliu.cn/cdn/img/201612/5be45dbd2fea86d07c96b392b776101b.JPG)

例如：
```
// 除法
function divide(n,d){
  return n/d;
}

// 手动柯里化
function curryDivide(n) { 
  return function(d) {
    return n/d;
  };
}
```
`curryDivide`是手动柯里化函数，也就是说，我显示地返回对应参数数量的函数。

**自动柯里化参数**  

```
// 接收一个函数，并返回一个只接受一个参数的函数。
function curry(fun) { // 柯里化一个参数，虽然似乎没什么用
  return function(arg) {
    return fun(arg);
  };
}

function curry2(fun) { // 柯里化两个参数
  return function(secondArg) {
    return function(firstArg) {
        return fun(firstArg, secondArg);
    };
  };
}
function curry3(fun) { // 柯里化三个参数
  return function(last) {
    return function(middle) {
      return function(first) {
        return fun(first, middle, last);
      };
    };
  };
};
```
curry2函数接受一个函数并将其柯里化成两个深层参数的函数。可以用它来实现先前定义的除法函数。
```
var divide10 = curry2(div)(10) 

divide10(50)
// => 5
```
柯里化函数有利于指定JavaScript函数行为，并将现有函数“组合”为新函数。并且使用柯里化比较容易产生流利的函数式API。

### 部分应用
柯里化函数逐渐返回消耗参数的函数，直到所有参数都耗尽。然而，部分应用函数是一个“部分“执行，等待接收剩余的参数立即执行的函数。

![部分应用](http://wilsonliu.cn/cdn/img/201612/819ea5e142764c144202ad5c032a17da.JPG)

```
// 部分应用一个或两个已知的参数
function partial1(fun, arg1) {
  return function(/* args */) {
    var args = construct(arg1, arguments); // construct为拼接数组，在此代码略去
    return fun.apply(fun, args);
  };
}

function partial2(fun, arg1, arg2) {
  return function(/* args */) {
    var args = cat([arg1, arg2], arguments); // cat也为拼接数组，在此代码略去
    return fun.apply(fun, args);
  };
}

// 部分应用任意数量的参数
function partial(fun /*, pargs */) {
  var pargs = _.rest(arguments);

  return function(/* arguments */) {
    var args = cat(pargs, _.toArray(arguments));
    return fun.apply(fun, args);
  };
}
```
### 通过组合端至端的拼接函数
一种理想化的函数式程序是向函数流水线的一端输送的一块数据，从另一端输出一个全新的数据块。
`!_.isString(name)`
这个流水线由`_.isString`和`!`组成
- `_.isString`接收一个对象，并返回一个布尔值
- `!`接收一个布尔值，并返回一个布尔值

```
// 通过组合多个函数及其数据转换建立新的函数
function isntString(str){
return !_.isString(str)
}

isntString(1)
// => true

// 还可以使用Underscore的_.compose函数实现同样的功能
// _.compose函数从右往左执行。即最右边函数的结果会被送入其左侧的函数，一个接一个
var isntString = _.compose(function(x) { return !x }, _.isString);

isntString([]);
//=> true
```
## 递归
理解递归对理解函数式编程来说非常重要，原因有三。
- 递归的解决方案包括使用对一个普通问题子集的单一抽象的使用
- 递归可以隐藏可变状态
- 递归是一种实现懒惰和无限大结构的方法
### 自吸收函数
在编写自递归函数时，规则如下
- 知道什么时候停止
- 决定怎样算一个步骤
- 把问题分解成一个步骤和一个较小的问题
```
function myLength(ary) {
  if (_.isEmpty(ary)) // _.isEmpty何时停止 
    return 0; 
  else
    // 进行一个步骤 1+ ；
    return 1 + myLength(_.rest(ary)); // 小一些的问题 _.rest(ary)   
}
```

**尾递归**
尾递归与一般自递归的明显区别是，”一个步骤“和”缩小的问题“中的元素都要进行递归调用。
```
function tcLength(ary, n) {
  var l = n ? n : 0;

  if (_.isEmpty(ary))
    return l;
  else
    return tcLength(_.rest(ary), l + 1);
}

tcLength(_.range(10));
//=> 10
```
### 相互关联函数
两个或多个函数相互调用被称为相互递归。下面看一个例子，用谓词函数来检查偶数和奇数：

```

function evenSteven(n) {
  if (n === 0)
    return true;
  else
    return oddJohn(Math.abs(n) - 1);
}

function oddJohn(n) {
  if (n === 0)
    return false;
  else
    return evenSteven(Math.abs(n) - 1);
}
// 相互递归调用来回反弹彼此之间递减某个绝对的值，知道一方或另一方达到0
evenSteven(4)
//  => true
oddJohn(11)
// =>true
```
### 对递归的改进
尽管递归技术上是可行的，但是因为JavaScript引擎没有优化递归调用，因此，在使用或写递归函数时，可能会碰到如下错误
```
evenSteven(10000) 
// 栈溢出
```

递归应该被看作一个底层操作，应该尽可能地避免（很容易造成栈溢出）。普通的共识是，首先是要函数组合，仅当需要的时才使用递归和蹦床。
> 蹦床（tramponline）:使用蹦床展平调用，而不是深度嵌套的递归调用。

首先，看看如何手动修复`evenOline`和`oddOline`使得递归调用不会溢出。一个办法是返回一个函数，它包装调用，而不是直接直接调用。
```
function evenOline(n) {
  if (n === 0)
    return true;
  else
    return partial1(oddOline, Math.abs(n) - 1);
}

function oddOline(n) {
  if (n === 0)
    return false;
  else
    return partial1(evenOline, Math.abs(n) - 1);
}

oddOline(3)()() // 返回的只是一个函数调用
// => function(){return evenOline(Math.abs(n) - 1)}
oddOline(3)()()() // 将函数调用执行
// => true
oddOline(10000)()()()... // 10000个()去执行返回的函数调用
// => true
```
当然，我们不能直接向用户暴露这个API，可以提高另外一个函数`trampoline`，从程序执行来进行扁平化处理。
```
function trampoline(fun /*, args */) { // 不断调用函数的返回值，知道它不是一个函数为止
  var result = fun.apply(fun, _.rest(arguments));

  while (_.isFunction(result)) {
    result = result();
  }

  return result;
}

trampoline(oddOline, 10000)
// false
```

由于调用链的间接性，使用蹦床增加了相互递归函数的一些开销。然而满总比溢出要好。同样，你可能不希望强迫用户使用`trampoline`，只是为了避免堆栈溢出。我们可以进一步隐藏其外观。
```
function isEvenSafe(n) {
  if (n === 0)
    return true;
  else
    return trampoline(partial1(oddOline, Math.abs(n) - 1));
}

function isOddSafe(n) {
  if (n === 0)
    return false;
  else
    return trampoline(partial1(evenOline, Math.abs(n) - 1));
}
```

## 基于流的编程
### 链接
使用jQuery等库经常会使用链接，链接可以让我们的代码更加简洁，如下是链接的实现示例。
链接方法的原理在于。每个链接的方法都返回统一的宿主对象引用。
```
function createPerson() {
  var firstName = "";
  var age = 0;

  return {
    setFirstName: function(fn) {
      firstName = fn;
      return this;
    },
    setAge: function(a) {
      age = a;
      return this;
    },
    toString: function() {
      return [firstName, lastName, age].join(' ');
    }
  };
}

createPerson()
  .setFirstName("Mike")
  .setAge(108)
  .toString();

//=> "Mike 108"
```

**惰性链**

上述链接是直接执行，然而我们也可以实行惰性链，即使其先缓存待执行的函数，等到调用执行函数时一起执行。
封装了一些行为的函数通常被称为`thunk`，存储在`_calls`中的`thunk`期待将作为接受`force`方法调用的对象的中间目标。
```
function LazyChain(obj) {
  this._calls  = []; // 用于缓存待执行函数的数组 thunk
  this._target = obj; // 目标对象
}

LazyChain.prototype.invoke = function(methodName /*, args */) { // 将函数压入的方法
  var args = _.rest(arguments);

  this._calls.push(function(target) {
    var meth = target[methodName];

    return meth.apply(target, args);
  });

  return this;
};

LazyChain.prototype.force = function() { // 强制执行this._calls中的函数
  return _.reduce(this._calls, function(target, thunk) {
    return thunk(target);
  }, this._target);
};
// 使用，直到force方法被调用才将 concat, sort,join执行
new LazyChain([2,1,3])
    .invoke('concat', [8,5,7,6])
    .invoke('sort')
    .invoke('join',' ')
    .force();

// => "1 2 3 4 5 6 7 8"

```
### 管道
链接模式有利于给对象的方法调用创建流程的API，但是对于函数式API则未必。
方法连接有各种各样的缺点，包括紧耦合对象的set和get逻辑。主要问题是，函数链经常会做调用之间改变传递的共同引用。函数式API重点在操作值而不是引用。
一下是管道的具体实现
```
function pipeline(seed /*, args */) {
  return _.reduce(_.rest(arguments),
                  function(l,r) { return r(l); },
                  seed);
};
pipeline(42, function(n){return -n},function(n){return n+1})
// => -41
```
## 写在最后
本文更多的是对《JavaScript函数式编程》一书的摘要，并透过一段段代码试图阐述函数式编程的思想。
希望以后的工作中能够吸取函数式编程的好，并慢慢对其加深理解。从书中获取知识，最终还是要落于实践中去的。
同时，希望能够通过这篇文章帮助不了解函数式编程的小伙伴建立系统的认识。