<?php

namespace Cylex\Crawlers\Facebook;

use Opis\Database\Database;
use Opis\Database\Connection;

class DataTarget {
    
    protected $db;
    
    protected $table;
    
    protected $connection;
    
    protected $sessionID;
    
    protected $checked = false;

    protected $allowed = array(
        'FILIAL_NAME', 'BRAND_NAME', 'STORE_ID',
        'CITY', 'DISTRICT', 'ZIP',
        'ADDRESS', 'SPECIAL_ADDRESS', 'REGION',
        'STATE', 'FULL_ADDRESS', 'PHONE',
        'FAX', 'EMAIL', 'WEB',
        'COUNTRY', 'CATEGORY', 'CUI',
        'CAM_COMERT', 'CEO', 'DESCRIPTION',
        'GEO_LAT', 'GEO_LNG', 'OPENING_HOURS',
        'SHORTPROFILE', 'KEYWORDS', 'SOCIAL_MEDIA',
        'FACEBOOK', 'LINKEDIN', 'FOURSQUARE',
        'GOOGLEONE', 'TWITTER', 'YOUTUBE',
        'TUMBLR', 'FLICKR', 'PAYMENTMETHODS',
        'ACTIVE_SINCE', 'EMPLOYEES', 'SOCIAL_CAPITAL',
        'OFFICIAL_NAME', 'TRANSPORT_PARKING', 'FACILITIES',
        'SERVICES', 'PRODUCTS', 'BRANDS',
        'AREAS_SERVED', 'MAILING_ADDRESS', 'COMPANY_TYPE',
        'CONTACT_PERSON', 'DEPARTMENTS', 'MOBILE_PHONE',
        'LANGUAGES', 'CERTIFICATIONS', 'PAGE_SOURCE',
        'PAGELINK', 'STATUS', 'FIRNR' ,'FBID' ,'LANG','SESSIONID',
        'CMP_OVERVIEW' , 'FB_USERNAME' ,'AWARDS' , 'MISSION' ,
    );
    
    public function __construct(array $config, $sessionID)
    {
        $this->table = $config['table'];        
        $this->sessionID = $sessionID;
        
        $this->connection = new Connection($config['dsn'], $config['user'], $config['password']);
        $this->connection->persistent();
        $this->db = new Database($this->connection);
    }
    
    public function init()
    {
        $this->createTable();
    }
    
    protected function checkKeys(array $keys)
    {
        foreach($keys as $key)
        {
            if(!in_array(strtoupper($key), $this->allowed))
            {
                throw new e("Unknown table field $key");
            }
        }
        $this->checked = true;
    }
    
    public function save($data = array())
    {
        if(!$this->checked)
        {
            $this->checkKeys(array_keys($data));
        }
        
        $data = $this->cleanData($data);
		
		$keys = array_keys($data);
		$keys = '(`' . implode('`,`',$keys) .'`)';
		$value = "";
		$c = count($data);
		for($i = 0; $i < $c; $i++)
		{
			$value .= "?,";
		}
		
		$value = substr($value, 0, -1);
		$data = array_values($data);
		try{
            $this->connection->command("insert ignore`". $this->table ."` ". $keys. "values(" .$value. ")" , $data);
        }
        catch (\Exception $e)
        {
            print_r($e->getMessage());
            echo PHP_EOL;
        }
    }
	
	public function cleanData(array $input, array $filter = null, $callback = null)
    {
        if($callback === null)
        {
            $callback = function($value){
                $value = trim(preg_replace('/\s\s+/', ' ', preg_replace('/\s/', ' ', $value)));
                //convert 4byte utf-8 to simple utf-8
                $value = iconv('utf-8', 'us-ascii//TRANSLIT', $value);
                return $value;
            };
        }
        
        if($filter !== null)
        {
            $input = array_intersect_key($input, array_flip($filter));
        }
        
        return array_map($callback, $input) + $input;
    }
    /*
     *  Create the table, in case it doesn't exist already
     */
    protected function createTable()
    {
        $command = "CREATE TABLE IF NOT EXISTS`{$this->table}` (
                    `FBID` varchar(200) NOT NULL,
                    `LANG` varchar(45) NOT NULL,
                    `SESSIONID` int(11) DEFAULT NULL,
                    `FIRNR` text,
                    `FILIAL_NAME` varchar(200) DEFAULT NULL,
                    `BRAND_NAME` varchar(200) DEFAULT NULL,
                    `STORE_ID` varchar(50) DEFAULT NULL,
                    `CITY` varchar(80) DEFAULT NULL,
                    `DISTRICT` varchar(80) DEFAULT NULL,
                    `ZIP` varchar(500) DEFAULT NULL,
                    `ADDRESS` varchar(150) DEFAULT NULL,
                    `SPECIAL_ADDRESS` varchar(250) DEFAULT NULL,
                    `REGION` varchar(80) DEFAULT NULL,
                    `STATE` varchar(80) DEFAULT NULL,
                    `FULL_ADDRESS` text,
                    `PHONE` varchar(500) DEFAULT NULL,
                    `FAX` varchar(70) DEFAULT NULL,
                    `EMAIL` varchar(500) DEFAULT NULL,
                    `WEB` text,
                    `COUNTRY` varchar(20) DEFAULT NULL,
                    `CATEGORY` text,
                    `CUI` varchar(200) DEFAULT NULL,
                    `CAM_COMERT` varchar(200) DEFAULT NULL,
                    `CEO` varchar(200) DEFAULT NULL,
                    `DESCRIPTION` text,
                    `GEO_LAT` varchar(50) DEFAULT NULL,
                    `GEO_LNG` varchar(50) DEFAULT NULL,
                    `OPENING_HOURS` text,
                    `LINKS` text,
                    `CMP_OVERVIEW` text,
                    `FB_USERNAME` varchar(200) DEFAULT NULL,
                    `MISSION` text,
                    `AWARDS` text,
                    `SHORTPROFILE` text,
                    `KEYWORDS` text,
                    `SOCIAL_MEDIA` text,
                    `FACEBOOK` varchar(250) DEFAULT NULL,
                    `LINKEDIN` varchar(250) DEFAULT NULL,
                    `FOURSQUARE` varchar(250) DEFAULT NULL,
                    `GOOGLEONE` varchar(250) DEFAULT NULL,
                    `TWITTER` varchar(250) DEFAULT NULL,
                    `YOUTUBE` varchar(250) DEFAULT NULL,
                    `TUMBLR` varchar(250) DEFAULT NULL,
                    `FLICKR` varchar(250) DEFAULT NULL,
                    `XING` text,
                    `PINTEREST` text,
                    `PAYMENTMETHODS` varchar(400) DEFAULT NULL,
                    `ACTIVE_SINCE` varchar(120) DEFAULT NULL,
                    `EMPLOYEES` varchar(100) DEFAULT NULL,
                    `SOCIAL_CAPITAL` varchar(100) DEFAULT NULL,
                    `OFFICIAL_NAME` varchar(100) DEFAULT NULL,
                    `TRANSPORT_PARKING` varchar(250) DEFAULT NULL,
                    `FACILITIES` varchar(1000) DEFAULT NULL,
                    `SERVICES` text,
                    `PRODUCTS` text,
                    `BRANDS` text,
                    `AREAS_SERVED` varchar(150) DEFAULT NULL,
                    `MAILING_ADDRESS` varchar(150) DEFAULT NULL,
                    `COMPANY_TYPE` text,
                    `CONTACT_PERSON` text,
                    `DEPARTMENTS` text,
                    `MOBILE_PHONE` varchar(70) DEFAULT NULL,
                    `LANGUAGES` varchar(100) DEFAULT NULL,
                    `CERTIFICATIONS` varchar(100) DEFAULT NULL,
                    `PAGE_SOURCE` text,
                    `PAGELINK` text,
                    `STATUS` varchar(100) DEFAULT NULL,
                    `CDATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`FBID`,`LANG`),
                    UNIQUE KEY `count_constraint` (`SESSIONID`,`FBID`,`LANG`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                  ";
        $this->connection->command($command);
    }
    
}
