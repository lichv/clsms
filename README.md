### clsms创蓝短信
```php
<?php
require_once  'vendor/autoload.php';

$result = \Clsms\Sms::getInstance(['account'=>'szsgsy','password'=>'Gsy2J0av1a8'])->send(['to'=>'15814058249','content'=>'亲爱的用户，您的活动验证码是123456，感谢您的参与此次活动。']);

```
