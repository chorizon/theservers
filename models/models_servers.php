<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\CoreFields\IntegerField;
use PhangoApp\PhaModels\CoreFields\CharField;
use PhangoApp\PhaModels\CoreFields\BooleanField;
use PhangoApp\PhaModels\CoreFields\ArrayField;

//| id | hostname                       | os_codename   | ip           | name                           | type  | profile |

$server=new Webmodel('server');

$server->change_id_default('id');

$server->register('hostname', new CharField(255), true);
$server->register('os_codename', new CharField(255), true);
$server->register('ip', new CharField(255), true);
$server->register('name', new CharField(255), true);
$server->register('type', new CharField(255), true);
$server->register('profile', new CharField(255), true);

//'category' => 'mail', 'module' => 'mail_unix', 'script' => 'add_domain', 'arguments' => $arguments

$task=new Webmodel('task');

$task->change_id_default('id');

$task->register('uuid', new CharField(255), true);

$task->register('ip', new CharField(255), true);

$task->register('category', new CharField(255), true);

$task->register('module', new CharField(255), true);

$task->register('script', new CharField(255), true);

$task->register('arguments', new ArrayField(new CharField(255)), true);

$task->register('status', new BooleanField());

?>