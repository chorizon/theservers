<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaLibs\GenerateAdminClass;
use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaLibs\AdminUtils;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaModels\Forms\SelectForm;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

function TheServersAdmin()
{

    settype($_GET['op'],'integer');

    switch($_GET['op'])
    {
    
        default:
        
            ?>
            <p><a href="<?php echo AdminUtils::set_admin_link('theservers', array('op' => 3)); ?>"><?php echo I18n::lang('theservers', 'add_modules', 'Add server modules'); ?></a></p>
            <?php
        
            $url=AdminUtils::set_admin_link('theservers', array());
        
            $admin=new SimpleList(Webmodel::$model['server']);
            
            Webmodel::$model['server']->distinct=1;
            
            $admin->arr_fields_showed=array('profile');
            $admin->arr_fields=array('profile');
            
            $admin->set_yes_id(0);
            
            //$admin->yes_options=0;
            
            $admin->arr_extra_fields=array(I18n::lang('common', 'options', 'Options'));
            
            $admin->arr_extra_fields_func=array('ServerOptionsListModel');
            
            $admin->show();
        
        break;
        
        case 1:
        
            settype($_GET['profile'], 'string');
            
            $_GET['profile']=Utils::form_text($_GET['profile']);
            
            settype($_GET['type'], 'string');
            
            $_GET['type']=Utils::form_text($_GET['type']);
            
            $conditions_server=['WHERE profile=?', [$_GET['profile']]];
            
            $arr_url=array('op' => 1, 'profile' => $_GET['profile']);
            
            if($_GET['type']!='')
            {
            
                $arr_url['type']=$_GET['type'];
                
                $conditions_server=['WHERE profile=? and type=?', [$_GET['profile'], $_GET['type']]];
            
            }
        
            Webmodel::$model['server']->set_conditions($conditions_server);
        
            Webmodel::$model['server']->distinc=1;
        
            $arr_server_type=Webmodel::$model['server']->select_to_array(array('type'));
        
            $select=new SelectForm('type', $_GET['type']);
            
            $select->arr_select['']='';
            
            foreach($arr_server_type as $arr_type)
            {
            
                $type=$arr_type['type'];
            
                $select->arr_select[$type]=$type;
            
            }
            
            echo '<p><form method="get">'.I18n::lang('theservers', 'select_type', 'Select type').': '.$select->form().'<input type="submit" value="'.I18n::lang('common', 'send', 'Send').'" /></form></p>';
            
            $url=AdminUtils::set_admin_link('theservers', $arr_url);
            
            Webmodel::$model['server']->distinc=0;
            
            $admin=new SimpleList(Webmodel::$model['server']);
            
            $admin->where_sql=$conditions_server;
            
            $admin->arr_fields_showed=array('hostname', 'ip', 'type', 'os_codename', 'status');
     
            $admin->options_func='ServerModulesOptionsModel';
     
            $admin->show();
     
        break;
        
        case 2:
        
            //Load the last log from the server using a task of protozoo.
        
        break;
        
        /*
        case 3:
        
            $url=AdminUtils::set_admin_link('theservers', array('op' => 3));
        
            $admin=new GenerateAdminClass(Webmodel::$model['server_modules'], $url);
        
            $admin->show();
        
        break;
        
        case 4:
        
            settype($_GET['ip'], 'string');
            
            $ip=Webmodel::$model['server_modules_related']->components['server_ip']->check($_GET['ip']);
            
            if($ip!==false)
            {
            
                Webmodel::$model['server']->set_conditions(['WHERE ip=?', [$ip]]);
            
                $c=Webmodel::$model['server']->select_count();
            
                if($c>0)
                {
                
                    settype($_GET['action'], 'integer');
                
                    Webmodel::$model['server_modules_related']->components['server_ip']->form='PhangoApp\PhaModels\Forms\HiddenForm';
                    Webmodel::$model['server_modules_related']->components['module_id']->form='PhangoApp\PhaModels\Forms\SelectModelForm';
                    
                    Webmodel::$model['server_modules_related']->components['module_id']->name_field_to_field='name';
                    
                    Webmodel::$model['server_modules_related']->create_forms();
                    
                    Webmodel::$model['server_modules_related']->forms['server_ip']->default_value=$ip;
                    
                    Webmodel::$model['server_modules_related']->forms['module_id']->model=&Webmodel::$model['server_modules'];
                    Webmodel::$model['server_modules_related']->forms['module_id']->field_value='id';
                    Webmodel::$model['server_modules_related']->forms['module_id']->field_name='name';
            
                    switch($_GET['action'])
                    {
            
                        default:
            
                        $admin=new SimpleList(Webmodel::$model['server_modules_related']);
                
                        $admin->arr_fields_showed=array('module_id');
                        
                        $admin->set_yes_id(0);
                        
                        $admin->arr_extra_fields=array(I18n::lang('common', 'options', 'Options'));
                
                        $admin->arr_extra_fields_func=array('ModulesOptionsListModel');
                        
                        $admin->show();
                        
                        break;
                        
                        case 1:
                        
                            //Execute script for add new module
                        
                        break;
                        
                    }
            
                    $url=AdminUtils::set_admin_link('theservers', array('op' => 4, 'ip' => $ip));
                
                    $admin=new GenerateAdminClass(Webmodel::$model['server_modules_related'], $url);
                
                    $admin->list->arr_fields_showed=array('module_id');
                
                    $admin->show();
                    
                    
                
                }
                
            
            }
        
        break;
        
        case 5:
        
            
        
        break;*/
    
    }

}

function ServerOptionsListModel($arr_row)
{

    return '<a href="'.AdminUtils::set_admin_link('theservers', array('op' => 1, 'profile' => $arr_row['profile'])).'">'.I18n::lang('theservers', 'see_servers', 'See servers').'</a>';

}

function ModulesOptionsListModel($arr_row)
{

    return '<a href="'.AdminUtils::set_admin_link('theservers', array('op' => 4, 'action' => 1, 'ip' => $arr_row['server_ip'])).'">'.I18n::lang('theservers', 'delete', 'Delete module in server').'</a>';

}

function ServerModulesOptionsModel($url_options, $model_name, $id, $arr_row)
{

    //$arr_options=PhangoApp\PhaLibs\SimpleList::BasicOptionsListModel($url_options, $model_name, $id);

    $arr_options[]='<a href="'.AdminUtils::set_admin_link('theservers', array('op' => 2, 'ip' => $arr_row['ip'])).'">'.I18n::lang('theservers', 'see_status', 'See status').'</a>';
    
    return $arr_options;

}

//function ServerOptions(

?>