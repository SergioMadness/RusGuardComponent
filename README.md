# RusGuard component


## Composer

RusGuard component is available through [composer](https://getcomposer.org/)

    composer require datalayerru/skud "dev-master"

Alternatively you can add the following to the `require` section in your `composer.json` manually:

```json
"datalayerru/skud": "dev-master"
```

Run `composer update` afterwards.

### In your PHP project
```php
$skud = new \datalayerru\skud\Skud();
$skud->url = 'http://skud/LNetworkServer/LNetworkService.svc?singleWsdl',
$skud->login = 'admin';
$skud->password = '';
```

### Add person to SKUD
```php
$personInfo = $skud->addEmployee('75f918bf-24fc-445f-8ff5-8fd11e1ad361', 'John', 'Johnov');
$skud->assignKey($personInfo->ID, '9874566321154');
```

### Set key is lost
```php
$skud->setKeyIsLost('9874566321154', $personInfo->ID);
```

### Get groups
```php
$skud->getGroups();
```

### Get person by key
```php
$skud->getEmployeeIdByKey('9874566321154');
```

### Remove employee
```php
$skud->removeEmployee($personInfo->ID);
```

### Remove employee by key number
```php
$skud->removeEmployeeByCardNumber('9874566321154');
```

### Get variable
```php
$skud->getVariable($name);
```

### Add employee's photo
```php
$skud->addEmployeePhoto($personInfo->ID, null, $base64Data);
```

### Get employee's photo
```php
$skud->getEmployeePhoto($personInfo->ID, 1);
```

### Get events
```php
$skud->getEvents($fromDate = null, $toDate = null, $inoutEventType = null, $page = 1, $pageSize = 20)
```

### Listen for notifications
```php
$result = $skud->getNotification();
```

## The MIT License

The MIT License (MIT)

Copyright (c) 2015 Sergey Zinchenko, DataLayer.ru

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.