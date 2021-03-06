<?php
class mini_log_manager
{
    
    private $logs = null;
    public function __construct()
    {
        $this->logs = new mini_struct_map();
        $this->init();
    }
    public function init()
    {
        $config = mini::getConfig();
        
        $logger = $config->logger;
         
        if(!empty($logger))
        {
            if(!isset($logger['logs'][0]) || !is_array($logger['logs'][0]))
            {
                $logger['logs'] = array($logger['logs']);
            }
            foreach($logger['logs'] as $log)
            {
                if(!class_exists($log['class']));
                {
                    $class =  mini::createComponent($log['class'], $log);
                    $this->logs->add($log['name'], $class);
                }
            }
        }
    }
    
    public function getLogs()
    {
        return $this->logs;
    }
    
}