<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\CoreFields\IntegerField;
use PhangoApp\PhaModels\CoreFields\CharField;
use PhangoApp\PhaModels\CoreFields\BooleanField;
use PhangoApp\PhaModels\CoreFields\ArrayField;
use PhangoApp\PhaModels\CoreFields\ForeignKeyField;
use PhangoApp\PhaModels\CoreFields\TextField;
use PhangoApp\PhaModels\CoreFields\UrlField;
use PhangoApp\PhaModels\CoreFields\IpField;

//| id | hostname                       | os_codename   | ip           | name                           | type  | profile |

$server=new Webmodel('server');

$server->change_id_default('id');

$server->register('hostname', new CharField(255), true);
$server->register('os_codename', new CharField(255), true);
$server->register('ip', new CharField(255), true);
$server->register('name', new CharField(255), true);
$server->register('type', new CharField(255), true);
$server->register('profile', new CharField(255), true);
$server->register('status', new BooleanField());

$server_modules=new Webmodel('server_modules');
$server_modules->change_id_default('id');
$server_modules->register('name', new CharField(255), true);
$server_modules->register('git_url', new UrlField(255), true);

$server_modules_related=new Webmodel('server_modules_related');

$server_modules_related->change_id_default('id');

$server_modules_related->register('server_ip', new IpField(255), true);
$server_modules_related->components['server_ip']->indexed=1;
$server_modules_related->register('module_id', new ForeignKeyField($server_modules), true);

//$server_module->register('server', new ForeignKeyField($server), true);

//'category' => 'mail', 'module' => 'mail_unix', 'script' => 'add_domain', 'arguments' => $arguments

$task=new Webmodel('task');

$task->change_id_default('id');

$task->register('pid', new IntegerField(11), false);

$task->register('user_id', new IntegerField(11), true);

$task->register('ip', new CharField(255), true);

$task->register('title', new CharField(255), true);

$task->register('category', new CharField(255), true);

$task->register('module', new CharField(255), true);

$task->register('script', new CharField(255), true);

$task->register('arguments', new ArrayField(new CharField(255)), false);
$task->register('extra_arguments', new ArrayField(new CharField(255)), false);

$task->register('status', new BooleanField());

//0 not error registered, 1 error registered

$task->register('error', new BooleanField());

//array('MESSAGE' => "Executing script ${options['command']}...", 'ERROR' => 0, 'CODE_ERROR' => 0, 'PROGRESS' => 0)

$log_task=new Webmodel('log_task');

$log_task->change_id_default('id');

$log_task->register('task_id', new ForeignKeyField($task), true);

$log_task->register('MESSAGE', new TextField(), false);

$log_task->register('ERROR', new BooleanField(), false);

$log_task->register('CODE_ERROR', new IntegerField(), false);

$log_task->register('PROGRESS', new IntegerField(), false);

$log_task->register('EXTRADATA', new TextField(), false);

?>