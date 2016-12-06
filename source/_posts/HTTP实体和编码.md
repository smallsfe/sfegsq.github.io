layout: postcd ..
title: HTTP实体和编码
date: 2016-4-29 13:18:20
tags: HTTP权威指南
category:
- 网络
---
# 实体和编码
每天都有数以亿计的各种媒体对象经由HTTP传送，如图像，文本，影片以及软件程序等。HTTP会确保它的报文被正确的传送，识别，提前以及适当的处理，则需要满足以下条件。
<!-- more -->

1. 可以被正确的识别(通过`Content-Type`首部说明媒体格式，`Content-Language`首部说明语言)，以便浏览器和其他客户端能够正确的处理内容
2. 可以被正确的解包(通过`Content-Length`首部和`Content-Encoding`首部)
3. 是最新的(通过实体验证码和缓存过期控制)
4. 符合用户的需要(基于`Accept`系列的内容协商首部)
5. 在网络上可以快速有效地传输(通过范围请求，差异编码以及其他数据压缩方法)
6. 完整到达，未被篡改过(通过传输编码首部和`Content-MD5`校验和首部)

为了实现上述目标，HTTP/1.1版本定义了以下10个基本实体首部字段。
- Content-Type
- Content-Length
- Content-Language
- Content-Encoding
- Content-Location
- Content-Range
- Content-MD5
- Last-Modified
- Expires
- Allow
- Etag
- Cache-Control

## Content-Length:实体的大小
Content-Length首部指示出报文中编码后实体主体的字节大小。使用Content-Length首部是为了能够检测出服务器崩溃而导致的报文截尾，并对共享持久连接的多个报文进行正确的分段。

Content-Length首部对于持久连接是必不可少的，如果响应通过持久连接传输，就可能有另一条HTTP响应紧随其后。客户端通过Content-Length首部就可以知道报文在何处结束，下一条报文从何处开始。因为连接是持久的，客户端无法依赖连接关闭来判别报文的结束。

在使用分块编码(`chunked encoding`)时，可以没有Content-Length，此时，数据是分为一系列的块来发送的，每块都有大小说明。

HTTP/1.1规范中建议对于带有主体但没有Content-Length首部的请求，服务器如果无法确定报文的长度，就应当发送400 Bad Request响应或411 Length Required响应，后一种表明服务器要求收到正确的Content-Length首部。

## 实体摘要
为检测实体主体的数据是否被修改过，发送方可以在生成初始的主体时，生成一个数据的校验和。Content-MD5首部是在对内容作了所有需要的内容编码之后，还没做任何传输编码之前，计算出来的。

## 媒体类型和字符集

Content-Type首部字段说明了实体主体的MIME类型，同时还支持可选的参数来进一步说明内容的类型。
`Content-Type: text/html; charset=iso-8859-4`

**多部分媒体类型**
MIME中的multipart电子邮件报文中包含多个报文，它们合在一起作为单一的复杂报文发送。每一部分都是独立的，有各自的描述其内容的集，不同的部分之间用分界字符串连接在一起。
HTTP也支持多部分主体。不过，通常只用在下列两种情形之一:提交填写好的表格，或是作为承载若干文档片段的范围响应。
HTTP使用Content-Type:multipart/form-data或Content-Type:multipart/mixed这样的首部以及多部分主体来发送这种请求。
## 内容编码 Content-Encoding
HTTP应用程序有时在发送之前需要对内容进行编码，当内容经过编码之后，编好码的数据就防止实体主体中，像往常一样发送给接收方。此时Content-Length变为编码后的长度。
同时，我们不希望服务器用客户端无法解码的方式来对内容进行编码，因此，客户端需要把自己能够支持的内容编码列表防止请求的`Accept-Encoding`首部。

## 传输编码和分块编码 Transfer-Encoding
使用传输编码是为了改变报文中的数据在网络上传输的方式。

**分块编码**
分块编码是HTTP规范唯一定义的传输编码方式。
分块编码把报文分割为若干个大小已知的块。块之间是紧挨着发送的，这样就不需要在发送之前就知道整个报文的大小了。

## 范围请求 Range
范围请求是指客户端实际上只请求文档的一部分，或者说某个范围。比如，下载电影下到一半网络故障，连接中断了，此时可利用范围请求来继续下载。
`Range: bytes=4000-`
代表客户端请求的是文档开头4000字节以后的步伐内容。

Range首部在流行的点对点(`Peer-to-Peer`)文件共享客户端软件中得到广泛的应用，他们从不同的对等实体同时下载多媒体文件的不同部分。

## 差异编码
差异编码是HTTP协议的一个扩展，它通过交换对象改变的部分而不是完整的对象来优化传输性能。

**请求报文**
```
A-IM: diffe   //Accept-Instance-Manipulation
If-None-Match: ababdisdksada //验证是否新鲜
```

**响应报文**
```
IM:diffe //差异编码的算法
Etag: zdsdsfsafsd  //更新后的版本号
Delta-base: ababdisdksada //差异算法基于的Etag
```
