# native-ping
Native ping command without any dependencies. Through ICMP protocol. Writed on PHP.


#install
`composer require jovixv/native-ping`

#Usage Example

```php
include '../vendor/autoload.php';

use jovixv\Ping\Ping;

$test = new Ping();

$pingEntity = $test->ping('dataforseo.com', 500, 4, 32);

var_dump($pingEntity);
```
