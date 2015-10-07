<?php

namespace Chorizon\TheServers;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use Symfony\Component\Process\Process;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

class Task {

	static public function begin_task($arr_data_task)
	{
	
        //Insert the new task in db
        
        $arr_data_task['status']=0;
        
        $arr_data_task['pid']=0;
        
        $arr_server=Webmodel::$model['server']->select_a_row($arr_data_task['server']);
                    
        $arr_data_task['ip']=$arr_server['ip'];
        
        if(Webmodel::$model['task']->insert($arr_data_task))
        {
	
            //Execute the script and daemonize it
            $category=basename(Utils::slugify($arr_data_task['category']));
            $module=basename(Utils::slugify($arr_data_task['module']));
            $script=basename(Utils::slugify($arr_data_task['script']));
            $parameters='';
            
            $process = new Process('php '.Routes::$base_path.'/console.php -m '.$category.'/'.$module.' -c load --command=\''.$script.'\' ');
            
            //$process->run();
            
        }
        else
        {
        
            echo 'Cannot insert the new task in database...->'.Webmodel::$model['task']->std_error;
        
        }
        
        //Obtain pid from daemon
	
	}
	
	static public function get_progress($idtask)
	{
	
        
	
	}
	
	static public function daemonize($callback)
    {
    
        $pid = pcntl_fork();
        
        if ($pid == -1)
        {
            echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'CANNOT FORK, check php configuration', 'CODE_ERROR' => PASTA_ERROR_FORK));
            exit(1);
        }
        elseif ($pid)
        {
            echo json_encode(array('PID' => $pid));
            exit(0);
        }
        else
        {
        
            //Daemonizing this element
        
            $sid = posix_setsid();
        
            return true;

        }

    }
            
}

?>
