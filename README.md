# native-ping
Native ping command without any dependencies. Through ICMP protocol. Writed on PHP.

# install

`This package working only with composer 2+` [Read more about our Composer 1.x deprecation policy.](https://blog.packagist.com/deprecating-composer-1-support/)

`composer require jovixv/native-ping`

# Usage Example

```php
include '../vendor/autoload.php';

use jovixv\Ping\Ping;

$test = new Ping();

$pingEntity = $test->ping('dataforseo.com', 500, 4, 32);

var_dump($pingEntity);
```
