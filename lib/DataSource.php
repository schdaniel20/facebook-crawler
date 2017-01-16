<?php
namespace Cylex\Crawlers\Facebook;

use Opis\Database\Database;
use Opis\Database\Connection;
use \DateTime;


class DataSource
{
    
    protected $db;
    
    protected $table;
    
    protected $count;
    
    protected $processed;
    
    protected $chunk;
    
    protected $fileds;
    
    protected $countryCode;
    
    protected $sessionID;
    
    const STARTTED = 1;
    const FINISHED = 2;
    
    public function __construct(array $config, $countryCode, $sessionID)
    {
        $this->table = $config['table'];
        $this->countryCode = $countryCode;
        $this->sessionID = $sessionID;
        
        if(!isset($config['fields']))
        {
            $config['fields'] = array();
        }
        
        $config['fields'] += array(
            'Url' => 'url',
            'id' => 'id',
            'lang' => 'lang',
            'country_code' => 'country_code',
            'sessionId' => 'sessionId',
            'crw_fir_nr' => 'crw_fir_nr'
        );
        
        $this->fileds = $config['fields'];
        
        $connection = new Connection($config['dsn'], $config['user'], $config['password']);
        $connection->persistent();
        $this->db = new Database($connection);
    }
    
    public function init($chunk = 50, $processed = 0)
    {
        $this->chunk = $chunk;
        $this->processed = $processed;
    }
    
    public function hasData()
    {
        if($this->count === null)
        {
            $this->count = $this->db->from($this->table)
                            ->where($this->fileds['country_code'])->is($this->countryCode)
                            ->andWhere($this->fileds['sessionId'])->is($this->sessionID)
                            ->andWhere('processed')->is(0)
                            ->distinct()
                            ->count();
        }
        return !($this->processed >= $this->count);
    }
    
    public function getNext()
    {
        $result = $this->db
                       ->from($this->table)                       
                       ->where($this->fileds['sessionId'])->is($this->sessionID)
                       ->andWhere('processed')->is(0)                       
                       ->limit($this->chunk)
                       ->distinct()
                       ->select(array(
                            $this->fileds['Url'] => 'url',
                            $this->fileds['id'] => 'id',
                            $this->fileds['lang'] => 'lang',
                            $this->fileds['crw_fir_nr'] => 'crw_fir_nr',
                       ))
                       ->all();
        $extr = array();
        
        foreach($result as $firm)
        {
            $matches = array();
           
            $lang = $firm->lang === null ? 'en' : $firm->lang;
            $lang = strtolower($lang);

            if(preg_match('`^(.+\.?)?facebook.com/pages/[^/]+/(?P<fb>[0-9]+).*`', $firm->url, $matches))
            {
                $extr['data'][$firm->id] = array(
                    'crw_fir_nr' => $firm->crw_fir_nr,
                    'id' => $matches['fb'],
                    'locale' => $lang,
                );
            }
            elseif(preg_match('`^(.+\.?)?facebook.com/.*/pages/[^/]+/(?P<fb>[0-9]+).*`', $firm->url, $matches))
            {
                $extr['data'][$firm->id] = array(
                    'crw_fir_nr' => $firm->crw_fir_nr,
                    'id' => $matches['fb'],
                    'locale' => $lang,
                );
            }
            elseif(preg_match('`^(.+\.?)?facebook.com/(?:\#!?/)?(?P<fb>[0-9a-zA-Z-_\.]+).*`', $firm->url, $matches))
            {
                if($matches['fb'] !== 'pages')
                {
                    $extr['data'][$firm->id] = array(
                        'crw_fir_nr' => $firm->crw_fir_nr,
                        'id' => $matches['fb'],
                        'locale' => $lang,
                    );
                }
                elseif(preg_match('`^(.+\.?)?facebook.com/pages/.+?/(?P<fb>[0-9]+).*`', $firm->url, $matches))
                {
                    if($matches['fb'] !== 'pages')
                    {
                        $extr['data'][$firm->id] = array(
                            'crw_fir_nr' => $firm->crw_fir_nr,
                            'id' => $matches['fb'],
                            'locale' => $lang,
                        );
                    }
                }
            }
            
            $extr['allfirnr'][$firm->id]= $firm->id;
            $extr['countryCode'] = $this->countryCode;
            $extr['sid'] = $this->sessionID;            
        }
        
        $this->processed += $this->chunk;
        return $extr;
    }
    
    public function updateProcessed(array $firNr , $value)
    {
        $now = new DateTime();
        if(!empty($firNr)) {
            $result = $this->db
                ->update($this->table)
                 ->where('id')->in($firNr)
                 ->andwhere($this->fileds['sessionId'])->is($this->sessionID)
                 ->andWhere($this->fileds['country_code'])->is($this->countryCode)
                 ->set(array(
                    "processed" => $value,
                    "extractDate" => $now->format('Y-m-d h:i:s')
                 ));
        }
    }
    
    public function sessionStarted()
    {
        $now = new DateTime();
        
        $update = $this->db
            ->update('fb_sessions')
             ->where($this->fileds['sessionId'])->is($this->sessionID)
             ->andWhere($this->fileds['country_code'])->is($this->countryCode)
             ->set(array(
                "status" => self::STARTTED,
                "start_date" => $now->format('Y-m-d'),
                "end_date" => NULL
             ));
    }
    
    public function sessionEnded()
    {
        $now = new DateTime();
        
        $update = $this->db
            ->update('fb_sessions')
             ->where($this->fileds['sessionId'])->is($this->sessionID)
             ->andWhere($this->fileds['country_code'])->is($this->countryCode)
             ->set(array(
                "status" => self::FINISHED,
                "end_date" => $now->format('Y-m-d')
             ));
    }
    
    public function setFacebookLink(int $id, string $link)
    {
        try{
            $result = $this->db
                ->update($this->table)
                 ->where('id')->is($id)
                 ->andwhere($this->fileds['sessionId'])->is($this->sessionID)
                 ->andWhere($this->fileds['country_code'])->is($this->countryCode)
                 ->set(array(
                    "url" => $link,
                 ));
            return true;
        }
        catch(\Exception $e) {
            print_r($e->getMessage());
        }
        return false;
    }
    
}

