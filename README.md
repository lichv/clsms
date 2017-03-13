### clsms创蓝短信
```php
<?php
require_once  'vendor/autoload.php';

$result = \Clsms\Sms::getInstance(['account'=>'myaccount','password'=>'mypasspord'])->send(['to'=>'15812345678','content'=>'亲爱的用户，您的活动验证码是123456，感谢您的参与此次活动。']);

```
