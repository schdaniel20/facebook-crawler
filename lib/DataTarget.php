<?php
namespace Cylex\Crawlers\Facebook;

use Opis\Database\Database;
use Opis\Database\Connection;

class DataTarget
{
    
    protected $db;
    
    protected $connection;
    
    protected $table;
    
    public function __construct(array $config)
    {
        $this->table = $config['table'];
        
        $this->connection = new Connection($config['dsn'], $config['user'], $config['password']);
        $this->connection->persistent();
        
        $this->db = new Database($this->connection);
    }
    
    public function init()
    {
        if(!$this->db->schema()->hasTable($this->table))
        {
            $this->db->schema()->create($this->table, function($table){
                
                $table->integer('id')->autoincrement();
                $table->integer('firnr')->unsigned()->notNull();
                $table->string('fbid', 128);
                $table->string('lang', 5);
                $table->binary('data');
                
            });
        }
    }
    
    public function save($firnr, $fbid, $lang, $data , $countryCode, $sid, $statusCode)
    {
        $statusCodeFromDb = $this->db
                    ->from($this->table)
                    ->where('firnr')->is($firnr)
                    ->andWhere('countryCode')->is($countryCode)
                    ->andWhere('sessionId')->is($sid)
                    ->select(['statusCode' => 'statusCode'])
                    ->first();
         
        if($statusCodeFromDb)
        {
            $statusCodeFromDb = explode(',', $statusCodeFromDb->statusCode);
            $statusCodeFromDb = $statusCodeFromDb[1] ?? $statusCodeFromDb[0];
            $statusCode = $statusCodeFromDb. "," . $statusCode;
        }

        $this->connection->command('replace `'.$this->table.'` (`firnr`, `fbid`, `lang`, `data` , `countryCode`, `sessionId`, `processed`, `statusCode`)
                                          VALUES (?,?,?,?,?,?,?,?)', array($firnr, $fbid, $lang, $data, $countryCode, $sid, '0', $statusCode));
       
    }
}
