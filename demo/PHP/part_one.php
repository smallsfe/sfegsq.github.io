<?php
 $html = file_get_contents("http://wilsonliu.cn");
    print_r($http_response_header);

    $fp = fopen("http://blog.wilsonliu.cn", "r");
    print_r(stream_get_meta_data($fp));
    fclose($fp);

    wget https://github.com/downloads/libevent/libevent/libevent-2.0.15-stable.tar.gz
    wget http://memcached.googlecode.com/files/memcached-1.4.9.tar.gz