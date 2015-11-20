<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaLibs\GenerateAdminClass;
use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaLibs\AdminUtils;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaUtils\MenuSelected;
use PhangoApp\PhaModels\Forms\SelectForm;
use PhangoApp\PhaModels\ModelForm;
use PhangoApp\PhaView\View;
use Chorizon\TheServers\Task;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

function TheTasksAdmin()
{

    $model=Webmodel::$m;

    settype($_GET['op'],'integer');
    settype($_GET['type'],'integer');

    switch($_GET['op'])
    {
    
        default:
        
            $arr_op[0]['text']=I18n::lang('theservers', 'tasks_in_progress', 'Task in progress');
            $arr_op[0]['link']=AdminUtils::set_admin_link('thetasks', array());
            
            $arr_op[1]['text']=I18n::lang('theservers', 'tasks_finished', 'Task finished');
            $arr_op[1]['link']=AdminUtils::set_admin_link('thetasks', array('type' => 1));
        
            MenuSelected::menu_selected($_GET['type'], $arr_op);
        
            $sql='';
        
            switch($_GET['type'])
            {
            
                default:
                
                    echo '<h3>'.I18n::lang('theservers', 'tasks_in_progress', 'Task in progress').'</h3>';
                    
                    $sql='WHERE status=0';
                
                break;
            
                case 1:
        
                    echo '<h3>'.I18n::lang('theservers', 'tasks_finished', 'Task finished').'</h3>';
                    
                    $sql='WHERE status=1';
                
                break;
            }
            
            Webmodel::$model['task']->components['status']->text_yes=I18n::lang('theservers', 'finished', 'Finished');
            
            Webmodel::$model['task']->components['status']->text_no=I18n::lang('theservers', 'in_progress', 'In progress');
        
        
            $admin=new SimpleList(Webmodel::$model['task']);
            
            $admin->where_sql=$sql;
            
            $admin->yes_options=0;
            
            $admin->arr_fields_showed=array('title', 'status', 'error');
            
            $admin->arr_extra_fields=array(I18n::lang('common', 'options', 'Options'));
            
            $admin->arr_extra_fields_func=array('options_process');
            
            //echo '<h2>'.I18n::lang('theservers', 'tasks_in_progress', 
            
            $admin->show();
        
        break;
        
        case 1:
        
            /*$action=AdminUtils::set_admin_link('mail_unix', array('op' => 2) );
            
            $hierarchy->update_links($link_parent, $action, I18n::lang('mail_unix', 'make_mail_task', 'Mail tasks progress'));
            
            echo '<p>'.$hierarchy->show($action).'</p>';*/
        
            settype($_GET['task_id'], 'integer');
            
            $arr_row=$model->task->select_a_row($_GET['task_id'], array('title'));
    
            $url_to_progress=AdminUtils::set_admin_link('thetasks', array('op' => 2, 'task_id' => $_GET['task_id']) );
    
            echo View::load_view(array('url_to_progress' => $url_to_progress, 'title' => $arr_row['title'], 'category' => 'mail', 'module' => 'mail_unix', 'script' => 'add_domain'), 'theservers/progress', 'chorizon/theservers');
            
            echo '<p><a href="'.AdminUtils::set_admin_link('thetasks', array('type' => $_GET['type'])).'">'.I18n::lang('common', 'go_back', 'Go Back').'</a>';
        
        break;
        
        case 2:
        
            ob_end_clean();
            
            echo Task::get_progress($_GET['task_id']);
            
            die;
        
        break;
        
        case 3:
            
            settype($_GET['task_id'], 'integer');
        
            echo '<h3>'.I18n::lang('theservers', 'tasks_in_progress', 'Task in progress').'</h3>';
        
            $admin=new SimpleList(Webmodel::$model['log_task']);
            
            $admin->where_sql='WHERE task_id='.$_GET['task_id'];
            
            $admin->yes_options=0;
            
            $admin->arr_fields_showed=array('MESSAGE', 'ERROR', 'CODE_ERROR');
            
            //echo '<h2>'.I18n::lang('theservers', 'tasks_in_progress', 
            
            $admin->show();
        
            echo '<p><a href="'.AdminUtils::set_admin_link('thetasks', array('type' => $_GET['type'])).'">'.I18n::lang('common', 'go_back', 'Go Back').'</a>';
        
        break;
        
        case 4:
        
            settype($_GET['task_id'], 'integer');

            $arr_row=$model->task->select_a_row($_GET['task_id']);
            
            //print_r($arr_row);
            
            foreach($model->task->components as $key => $component)
            {
            
                $model->task->components[$key]->form='PhangoApp\PhaModels\Forms\NoForm';
            
            }
            
            $model->task->create_forms();
            
            ModelForm::set_values_form($model->task->forms, $arr_row);
            
            echo View::load_view(array($model->task->forms, array('ip', 'title', 'category', 'module', 'script', 'status', 'error')), 'forms/modelform');
        
            echo '<p><a href="'.AdminUtils::set_admin_link('thetasks', array('type' => $_GET['type'])).'">'.I18n::lang('common', 'go_back', 'Go Back').'</a>';
        
        break;
        
    }

}

function options_process($arr_row)
{

    $arr_status[]='<a href="'.AdminUtils::set_admin_link('thetasks', array('op' => 4, 'task_id' => $arr_row['id'], 'type' => $_GET['type'])).'">'.I18n::lang('theservers', 'see_task', 'See task').'</a>';

    $arr_status[]='<a href="'.AdminUtils::set_admin_link('thetasks', array('op' => 1, 'task_id' => $arr_row['id'], 'type' => $_GET['type'])).'">'.I18n::lang('theservers', 'see_status', 'See status').'</a>';
    
    $arr_status[]='<a href="'.AdminUtils::set_admin_link('thetasks', array('op' => 3, 'task_id' => $arr_row['id'], 'type' => $_GET['type'])).'">'.I18n::lang('theservers', 'see_log', 'See log').'</a>';

    return implode('<br />', $arr_status);
    
}

?>