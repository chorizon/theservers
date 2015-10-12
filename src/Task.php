<?php

namespace Chorizon\TheServers;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client;
use \Exception;

Webmodel::load_model('vendor/chorizon/theservers/models/models_servers');

$insert_id=0;
$process;
$return_url='';

define('ERROR_UPDATING_TASK', 1);
define('ERROR_FORK', 2);
define('NEED_TASK_ID', 3);

class Task {

	static public function begin_task($arr_data_task)
	{
	
        global $insert_id;
        global $process;
        global $return_url;
	
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
        
        $return_url=$arr_data_task['return'];
        
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
            
                global $insert_id, $process, $return_url;
                
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
                        Task::log_progress(array('task_id' => $insert_id, 'MESSAGE' => 'Error, cannot update the task', 'ERROR' => ERROR_UPDATING_TASK, 'CODE_ERROR' => 1, 'PROGRESS' => 100));
                        
                    
                    }
                    /*else
                    {
                    
                        Task::log_progress(array('task_id' => $insert_id, 'MESSAGE' => 'Begin script execution...', 'ERROR' => 0, 'CODE_ERROR' => 0));
                    
                    }*/

                }
            
                header('Location: '.Routes::add_get_parameters($return_url, array('task_id' => $insert_id)));
            
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
        
        return Webmodel::$model['log_task']->insert($arr_log);
    
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
                
                $arr_task=Webmodel::$model['task']->select_a_row($options['id']);
            
                settype($arr_task['id'], 'integer');
                
                if($arr_task['id']==0)
                {
            
                    //Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Need a task id for execute this script', 'ERROR' => 1, 'CODE_ERROR' => NEED_TASK_ID, 'PROGRESS' => 100));
                    
                    echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'Need a task id for execute this script', 'CODE_ERROR' => NEED_TASK_ID));
                    exit(1);
            
                }
                else
                {
                
                    return array($options['id'], $arr_task);

                }
                    
            }
            
        }
        else
        {
        
            echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'Need a task id for execute this script', 'CODE_ERROR' => NEED_TASK_ID));
            exit(1);
        
        }

    }
    
    static public function make_simple_petition($arr_petition)
    {

        list($task_id, $arr_task)=Task::daemonize();

        if($task_id!=0)
        {
        
            Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Begin script execution...', 'ERROR' => 0, 'CODE_ERROR' => 0));
        
            //use guzzle for send message to server with ca.crt and ca.key
            
            //Save results in database, when you go to 100, kill the script saving the result. 
            //If no answered, error.
            
            try {
            
                $client = new Client(['base_uri' => 'https://'.$arr_task['ip'].':'.PASTAFARI_PORT.'/pastafari/'.SECRET_KEY_PASTAFARI]);
                
                //?category=email&module=email&script=add_account
                
                $arr_args=unserialize($arr_task['arguments']);
                
                $arr_query=$arr_petition;
                        
                foreach($arr_args as $key_task => $task)
                {
                
                    $arr_query[$key_task]=$task;
                
                }
                
                $response = $client->request('GET', '', [ 'query' => $arr_query, 'verify' => PASTAFARI_SSL_VERIFY, 'cert' => PASTAFARI_SSL_CERT ]);
                
                $code = $response->getStatusCode(); // 200
                $reason = $response->getReasonPhrase(); // OK
                $uuid='';
                
                if($code!=200)
                {
                
                    Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Error, cannot execute the task: '.$reason."\n".$response->getBody(), 'ERROR' => 1, 'CODE_ERROR' => 1, 'PROGRESS' => 100));
                
                }
                else
                {
                
                    $done=false;
                
                    $body = $response->getBody();
                    
                    if(($arr_body=json_decode($body, true)))
                    {
                    
                        settype($arr_body['ERROR'], 'integer');
                    
                        $arr_body['task_id']=$task_id;
                        
                        settype($arr_body['UUID'], 'string');
                        
                        $uuid=$arr_body['UUID'];
                        
                        Task::log_progress($arr_body);
                        
                        if($arr_body['ERROR']>0)
                        {
                        
                            //Error not make more 
                        
                            $done=true;
                        
                        }
                    
                    }
                    else
                    {
                    
                        Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Error, i don\'t understand the message from server: '.$body, 'ERROR' => 1, 'CODE_ERROR' => NO_JSON_RETURNED, 'PROGRESS' => 100));
                        
                        die;
                    
                    }
                    
                    //If all fine, make loop and send message for obtain progress. 500 miliseconds.
                    
                    
                    
                    $client_progress = new Client(['base_uri' => 'https://'.$arr_task['ip'].':'.PASTAFARI_PORT.'/pastafari/check_process/'.SECRET_KEY_PASTAFARI.'/'.$uuid]);
                    
                    $progress=0;
                    
                    while(!$done)
                    {
                    
                        //If timeout is excesive, kill the script?.
                    
                        sleep(1);
                        
                        //Create method for obtain progress
                        
                        $response = $client_progress->request('GET', '', [ 'verify' => PASTAFARI_SSL_VERIFY, 'cert' => PASTAFARI_SSL_CERT ]);
                    
                        if($code!=200)
                        {
                        
                            Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Error, cannot execute the task: '.$reason, 'ERROR' => 1, 'CODE_ERROR' => 1, 'PROGRESS' => 100));
                            
                            die;
                        
                        }
                        else
                        {
                        
                            $body = $response->getBody();
                            
                            if(($arr_body=json_decode($body, true)))
                            {
                            
                                $arr_body['task_id']=$task_id;
                            
                                settype($arr_body['PROGRESS'], 'integer');
                                
                                if($arr_body['PROGRESS']!=$progress)
                                {
                                    
                                    Task::log_progress($arr_body);
                                
                                    $progress=$arr_body['PROGRESS'];
                                
                                }
                                
                                //If 100, the script is finished and i can die
                                
                                if($arr_body['PROGRESS']==100)
                                {
                                
                                    if($arr_body['ERROR']==0)
                                    {
                                    
                                        //Set status task to done
                                        
                                        //Webmodel::$model['task']
                                    
                                    }
                                    
                                    Task::log_progress($arr_body);
                                
                                    $done=true;
                                
                                }
                                
                                if($arr_body['ERROR']>0)
                                {
                                    
                                    $arr_body['PROGRESS']=100;
                                
                                    Task::log_progress($arr_body);
                                
                                    $done=true;
                                
                                }
                            
                            }
                            else
                            {
                            
                                Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Error, i don\'t understand the message from server: '.$body, 'ERROR' => 1, 'CODE_ERROR' => NO_JSON_RETURNED, 'PROGRESS' => 100));
                                
                                die;
                            
                            }
                    
                        }
                    
                    }
                
                }
            }
            catch (Exception $e) {
                
                Task::log_progress(array('task_id' => $task_id, 'MESSAGE' => 'Error, cannot execute the task: '.$e->getMessage(), 'ERROR' => 1, 'CODE_ERROR' => NO_JSON_RETURNED, 'PROGRESS' => 100));
                
                die;
            }

        }
    
    }
            
}

?>
