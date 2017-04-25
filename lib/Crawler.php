<?php
namespace Cylex\Crawlers\Facebook;

use Exception;
use Facebook\Facebook;
use Cylex\Crawlers\Facebook\ErrorHandler;
use Facebook\Authentication\AccessToken;


class Crawler
{    
    protected $source;
    
    protected $target;
    
    protected $sleepTime;
    
    protected $processed;
    
    protected $chunk;
    
    public function __construct(DataSource $source, DataTarget $target)
    {
        $this->source = $source;
        $this->target = $target;
    }
    
    public function init($sleepTime = 12, $chunk = 50, $processed = 0)
    {
        $this->sleepTime = $sleepTime;
        $this->processed = $processed;
        $this->chunk = $chunk;
    }    
    
    public function run(Facebook $app, array $fields)
    {   
        $this->source->init($this->chunk, $this->processed);
        $this->target->init();
        $this->source->sessionStarted();
         
        $queryFields = implode(',', $fields);

        while(1)
        {
            $batch = array();
            
            $data = $this->source->getNext();
            
            if(!isset($data['data'])) 
            {
                if(isset($data['allfirnr']))
                {
                    $this->source->updateProcessed($data['allfirnr'], 1);
                }
                
                break;
            }

            foreach($data['data'] as $firnr => &$fb)
            {
                
                $batch[$firnr] = $app->request('GET', '/' . $fb['id']. '?fields=' . $queryFields . '&locale=' . $fb['locale']);
            }
            
            try
            {
                $responses = $app->sendBatchRequest($batch);
            }
            catch(Exception $e)
            {
                print_r($e);
                continue;
            }
            
            foreach($responses as $firnr => $response)
            {
                $body = null;
                $data['data'][$firnr]['statusCode'] = 0;
                $body = $response->getBody();
                
                if($response->isError())
                {
                    $body = json_decode($body, true);
                    print_r($body);
                    $errorHandler = new ErrorHandler($firnr, $data['data'][$firnr], $body['error'], $this->source);
                    $body = null;
                    
                    $data['data'][$firnr]['statusCode'] = $errorHandler->getStatusCode();
                    
                    if($errorHandler->handleError())
                    {                        
                       unset($data['allfirnr'][$firnr]);
                    }
                }               
                
                $this->target->save($data['data'][$firnr]['crw_fir_nr'], $data['data'][$firnr]['id'], $data['data'][$firnr]['locale'], $body, $data['countryCode'], $data['sid'], $data['data'][$firnr]['statusCode']);
                die;
            }
            
            $this->source->updateProcessed($data['allfirnr'], 1);            
            sleep($this->sleepTime);
        }
        
        $this->source->sessionEnded();
    }
    
}
