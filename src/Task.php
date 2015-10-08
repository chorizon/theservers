<?php

namespace Chorizon\TheServers;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use Symfony\Component\Process\Process;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

$insert_id=0;
$process;

class Task {

	static public function begin_task($arr_data_task)
	{
	
        global $insert_id;
        global $process;
	
        //Check that no other same processes is active
	
        //Insert the new task in db
        
        $arr_data_task['status']=0;
        
        $arr_data_task['pid']=0;
        
        $arr_server=Webmodel::$model['server']->select_a_row($arr_data_task['server']);
                    
        $arr_data_task['ip']=$arr_server['ip'];
        
        if(Webmodel::$model['task']->insert($arr_data_task))
        {
        
            $insert_id=Webmodel::$model['task']->insert_id();
	
            //Execute the script and daemonize it
            //$category=basename(Utils::slugify($arr_data_task['category']));
            
            $arr_cat=explode('/', $arr_data_task['category']);
            
            foreach($arr_cat as $key => $cat)
            {
                $arr_cat[$key]=basename(Utils::slugify($cat));
            }
            
            $category=implode('/', $arr_cat);
            $module=basename(Utils::slugify($arr_data_task['module']));
            $script=basename(Utils::slugify($arr_data_task['script']));
            $parameters='';
            
            $process = new Process('php '.Routes::$base_path.'/console.php -m '.$category.'/'.$module.' -c '.$script);
            
            //echo 'php '.Routes::$base_path.'/console.php -m '.$category.'/'.$module.' -c '.$script;
            
            $process->run(function ($type, $buffer) {
            
                global $insert_id, $process;
                
                $arr_buffer=json_decode($buffer, true);
                
                settype($arr_buffer['PID'], 'integer');
                
                if($arr_buffer['PID']>0)
                {
            
                    Webmodel::$model['task']->reset_require();
                    
                    Webmodel::$model['task']->conditions='WHERE id='.$insert_id;
                    
                    if(!Webmodel::$model['task']->update(array('pid' => $process->getPid())))
                    {
                    
                        echo Webmodel::$model['task']->std_error;
                    
                    }
                }
            
                die;
            
            });
            
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
	
	static public function daemonize()
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
