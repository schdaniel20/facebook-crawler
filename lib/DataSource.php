<?php
namespace Cylex\Facebook\Parser;

use Opis\Database\Database;
use Opis\Database\Connection;

class DataSource {
    
    protected $db;
    
    protected $table;
    
    protected $connection;
    
    protected $sessionID;
    
    public function __construct(array $config, $sessionID)
    {
        $this->table = $config['table'];        
        $this->sessionID = $sessionID;
        
        $this->connection = new Connection($config['dsn'], $config['user'], $config['password']);
        $this->connection->persistent();
        $this->db = new Database($this->connection);
    }
    
    public function getNext(&$result , $key = 'id') 
    {        
        if(isset($result['ids']))
        {
            $update = $this->db
                    ->update($this->table)                    
                    ->where($key)->in($result['ids'])
                    ->set(array(
                    "processed" => 1
                    ));  
        }
        
        $result = $this->getResult($key);
        
        if(count($result['result']) > 0) return;

        $command = "UPDATE ".$this->table." set PROCESSED = 2 where processed = 0 and data is not null limit 1000";
        $data = $this->connection->query($command);

        $result = $this->getResult($key);
    }
    
    protected function getResult($key = 'id')
    {
        $ids = [];
        $result = $this->db->from($this->table)			
                    ->where('processed')->is(2)
                    ->andWhere('data')->notNull()
                    ->limit(1000)
                    ->distinct()
                    ->select([
                     'firnr',
                     'fbid', 
                     'data',
					 'lang',
                    ])
            ->all(function($firnr, $fbid, $data, $lang) use (&$ids, $key){
                $ids[] = $$key;
                return [
                     'firnr' => $firnr,
                     'fbid' => $fbid, 
                     'data' => $data,
					 'lang' => $lang,
                    ];
            });
        
        return [
            'ids'    => $ids,
            'result' => $result
            ];
    }
}