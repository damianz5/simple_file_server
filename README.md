# Simple file server

[![Build status on Linux](https://travis-ci.org/damianz5/simple_file_server.svg?branch=master)](http://travis-ci.org/damianz5/simple_file_server)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/damianz5/simple_file_server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/damianz5/simple_file_server/?branch=master)

[![Code Coverage](https://scrutinizer-ci.com/g/damianz5/simple_file_server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/damianz5/simple_file_server/?branch=master)

[![StyleCi.io](https://styleci.io/repos/78035427/shield?style=plastic&branch=master)](https://styleci.io/repos/78035427)

[![Latest Stable Version](https://poser.pugx.org/damianz5/simple-file-server/v/stable.png)](https://packagist.org/packages/damianz5/simple-file-server)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8dff597a-369f-4ec8-8ac4-787f5feb3f05/big.png)](https://insight.sensiolabs.com/projects/8dff597a-369f-4ec8-8ac4-787f5feb3f05)

Installation
------------
```
composer create-project damianz5/simple-file-server
```
___


Test run
------------

```
symfony server:start --port 8083 --no-tls
```

simple vanilla php client:
```
php example/simpleClient.php
```
should return (container name will be different)
```
response: {"status":"ok","collection_name":"5985f402600fd3d864de043a44f31b5f","files":["data\/container-5985f402600fd3d864de043a44f31b5f\/9cf3a175-test1.png"]}
```

Buzz Client (requires `kriswallsmith/buzz`):
in progress

___
add new files to new collection:
```
curl -H 'AUTHKEY:supersecretcode1@' \
-F "image=@screenshot.png" -F "xx=@content.html" \
http://127.0.0.1:8083/api/upload
```

___
add new files to existing collection:
```
curl -H 'AUTHKEY:supersecretcode1@' \
-F "image=@screenshot.png" -F "xx=@content.html" \
http://127.0.0.1:8083/api/upload/ddf59d090e70094429414935c5ea53a1
```

___

list files:

```
curl -H 'AUTHKEY:supersecretcode1@' \
http://127.0.0.1:8083/api/list/ddf59d090e70094429414935c5ea53a1
```

using https://httpie.org/
```
http GET http://127.0.0.1:8083/api/list/beefbeefbeefbeefbeefbeefbeefbeef AUTHKEY:supersecretcode1@
```

IMPORTANT
------------
 - change credentials keys in `app/config/parameters.yml`
 - check `upload_max_filesize` and `post_max_size` in your `php.ini`

TODO
------------
 - add `more` tests
 - invalid uploaded files should return more information
