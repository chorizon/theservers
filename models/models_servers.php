<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\CoreFields\IntegerField;
use PhangoApp\PhaModels\CoreFields\CharField;
use PhangoApp\PhaModels\CoreFields\BooleanField;
use PhangoApp\PhaModels\CoreFields\ArrayField;
use PhangoApp\PhaModels\CoreFields\ForeignKeyField;
use PhangoApp\PhaModels\CoreFields\TextField;

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

$task->register('pid', new IntegerField(11), false);

$task->register('user_id', new IntegerField(11), true);

$task->register('ip', new CharField(255), true);

$task->register('title', new CharField(255), true);

$task->register('category', new CharField(255), true);

$task->register('module', new CharField(255), true);

$task->register('script', new CharField(255), true);

$task->register('arguments', new ArrayField(new CharField(255)), false);

$task->register('status', new BooleanField());

//array('MESSAGE' => "Executing script ${options['command']}...", 'ERROR' => 0, 'CODE_ERROR' => 0, 'PROGRESS' => 0)

$log_task=new Webmodel('log_task');

$log_task->change_id_default('id');

$log_task->register('task_id', new ForeignKeyField($task), true);

$log_task->register('MESSAGE', new TextField(), false);

$log_task->register('ERROR', new IntegerField(), false);

$log_task->register('CODE_ERROR', new IntegerField(), false);

$log_task->register('PROGRESS', new IntegerField(), false);

?>