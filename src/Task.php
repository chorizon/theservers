<?php

namespace Chorizon\TheServers;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use Symfony\Component\Process\Process;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

$insert_id=0;
$process;

define('ERROR_UPDATING_TASK', 1);
define('ERROR_FORK', 2);
define('NEED_TASK_ID', 3);

class Task {

	static public function begin_task($arr_data_task)
	{
	
        global $insert_id;
        global $process;
	
        Utils::load_config('config', __DIR__.'/../settings/');
        Utils::load_config('configerrors', __DIR__.'/../settings/');
        
        if(!isset($settings['logs']))
        {
            $settings['logs']='./logs';
        }
	
        //Check that no other same processes is active
	
        //Insert the new task in db
        
        $arr_data_task['status']=0;
        
        $arr_data_task['pid']=0;
        
        $arr_server=Webmodel::$model['server']->select_a_row($arr_data_task['server']);
                    
        $arr_data_task['ip']=$arr_server['ip'];
        
        $arr_data_task['user_id']=$_SESSION['IdUser_admin'];
        
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
            
            $process = new Process('php '.Routes::$base_path.'/console.php -m '.$category.'/'.$module.' -c '.$script.' --id '.$insert_id);
            
            //echo 'php '.Routes::$base_path.'/console.php -m '.$category.'/'.$module.' -c '.$script;
            
            $process->run(function ($type, $buffer) {
            
                global $insert_id, $process;
                
                $arr_buffer=json_decode($buffer, true);
                
                settype($arr_buffer['PID'], 'integer');
                
                if($arr_buffer['PID']>0)
                {
            
                    Webmodel::$model['task']->reset_require();
                    
                    Webmodel::$model['task']->conditions='WHERE id='.$insert_id;
                    
                    if(!Webmodel::$model['task']->update(array('pid' => $arr_buffer['PID'])))
                    {
                    
                        //echo Webmodel::$model['task']->std_error;
                        //Save in database
                        Task::log_progress(array('task_id' => $insert_id, 'MESSAGE' => 'Error, cannot update the task', 'ERROR' => ERROR_UPDATING_TASK, 'CODE_ERROR' => 1));
                        
                    
                    }
                    else
                    {
                    
                        Task::log_progress(array('task_id' => $insert_id, 'MESSAGE' => 'Begin script execution...', 'ERROR' => 0, 'CODE_ERROR' => 0));
                    
                    }

                }
            
                die;
            
            });
            
        }
        else
        {
        
            //echo 'Cannot insert the new task in database...->'.Webmodel::$model['task']->std_error;
            
            throw new \Exception('Cannot insert the new task in database...->'.Webmodel::$model['task']->std_error);
        
        }
        
        //Obtain pid from daemon
	
	}
	
	//arr_log : array('MESSAGE' => "A message...", 'ERROR' => 0, 'CODE_ERROR' => 0, 'PROGRESS' => 0)
	
	static public function log_progress($arr_log)
    {
        
        Webmodel::$model['log_task']->insert($arr_log);
        
        echo Webmodel::$model['log_task']->std_error;
    
    }
	
	static public function get_progress($idtask)
	{
	
        
	
	}
	
	static public function daemonize()
    {
    
        $options=get_opts_console('', $arr_opts=array('id:'));
        
        settype($options['id'], 'integer');
        
        if($options['id']>0)
        {
    
            $pid = pcntl_fork();
            
            if ($pid == -1)
            {
                echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'CANNOT FORK, check php configuration', 'CODE_ERROR' => ERROR_FORK));
                exit(1);
            }
            elseif ($pid)
            {
                echo json_encode(array('PID' => $pid, 'ERROR' => 0, 'MESSAGE' => 'Running tasks...', 'PROGRESS' => 0));
                exit(0);
            }
            else
            {
            
                //Daemonizing this element
            
                $sid = posix_setsid();
            
                return $options['id'];

            }
            
        }
        else
        {
        
            echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'Need a task id for execute this script', 'CODE_ERROR' => NEED_TASK_ID));
            exit(1);
        
        }

    }
            
}

?>
