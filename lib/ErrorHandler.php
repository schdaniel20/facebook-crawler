<?php

namespace Cylex\Crawlers\Facebook;

class ErrorHandler {
    
    protected $id;
    protected $data;
    protected $error;
    protected $source;
    const REDIRECT = 21;

    public function __construct(int $id, array $data, array $error, $source)
    {
        $this->id = $id;
        $this->data = $data;
        $this->error = $error;
        $this->source = $source;
    }
    
    public function handleError()
    {
        if($this->error['code'] == self::REDIRECT)
        {
            //in case of redirect: 1. get the new fbid, 2. save it            
            preg_match('~ID\s*(\d+)\.~ims', $this->error['message'], $newID);
            
            if($newID) 
            {
                $link = "https://www.facebook.com/" . $newID[1];
                return $this->source->setFacebookLink($this->id, $link);
            }
        }
        // in any other case, just pass back true and save the error code
        return false;        
    }
    
    public function getStatusCode() 
    {
        return $this->error['code'];
    }
}

