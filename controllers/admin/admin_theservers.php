<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaLibs\GenerateAdminClass;
use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaLibs\AdminUtils;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaUtils\Utils;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

function TheServersAdmin()
{

    settype($_GET['op'],'integer');

    switch($_GET['op'])
    {
    
        default:
        
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
        
            $_GET['profile']=Utils::slugify($_GET['profile']);
        
            $url=AdminUtils::set_admin_link('theservers', array('op' => 1, 'profile' => $_GET['profile']));
            
            $admin=new GenerateAdminClass(Webmodel::$model['server'], $url);
            
            $admin->list->where_sql=['WHERE profile=?', [$_GET['profile']]];
     
            $admin->show();
     
        break;
    
    }

}

function ServerOptionsListModel($arr_row)
{

    return '<a href="'.AdminUtils::set_admin_link('theservers', array('op' => 1, 'profile' => $arr_row['profile'])).'">'.I18n::lang('theservers', 'see_servers', 'See servers').'</a>';

}

?>