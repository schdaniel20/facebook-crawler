<?php

require_once 'vendor/autoload.php';

use Cylex\Crawlers\Facebook\DataSource;
use Cylex\Crawlers\Facebook\Parser;
use Cylex\Crawlers\Facebook\DataTarget;

//source config
$source = [
    "table" => "all_facebook_RESULTS",
    "user" => "root",
    "password" => "",
    "dsn" => "mysql:host=;dbname=facebook_crawler;charset=utf8"    
];

//target config
$target = [
    "table" => "FB_UNITED_KINGDOM_PROCESSED",
    "user" => "root",
    "password" => "",
    "dsn" => "mysql:host=;dbname=UK_Business_Directory_DataSYNC_2015;charset=utf8"    
];

$sessionID = 13308;

//$source = new DataSource($source, $sessionID);
$parser = new Parser($sessionID);
//$target = new DataTarget($target, $sessionID);

$json['data'] = ' {"id":"135083343199196","about":"007 Caf\u00e9 & Restaurant \tFresh Food & Fast Service","can_post":false,"category":"Food\/Beverages","category_list":[{"id":"2252","name":"Food\/Beverages"}],"checkins":2066,"cover":{"cover_id":"698963850144473","offset_x":0,"offset_y":39,"source":"https:\/\/scontent.xx.fbcdn.net\/hphotos-xap1\/v\/t1.0-9\/1482906_698963850144473_522843854_n.jpg?oh=3ff33e1fad8fa109ed79726a9f37e3bd&oe=569C2433","id":"698963850144473"},"description":"007 Cafe & Restaurant \nFresh Food & Fast Service","has_added_app":false,"is_community_page":false,"is_published":true,"likes":4695,"link":"https:\/\/www.facebook.com\/007cafe","location":{"city":"Alexandria","country":"Egypt","latitude":31.234396734444,"longitude":29.949002601296,"street":"256 El Geish Road (Beside San Giovanni Hotel), Stanley, Alexandria ","zip":"08544"},"name":"OO7 Cafe & Restaurant","parking":{"lot":1,"street":1,"valet":1},"payment_options":{"amex":0,"cash_only":1,"discover":0,"mastercard":0,"visa":0},"phone":"   035414067.  01004102452","restaurant_services":{"delivery":0,"catering":0,"groups":1,"kids":1,"outdoor":1,"reserve":1,"takeout":0,"waiter":1,"walkins":0},"restaurant_specialties":{"breakfast":1,"coffee":1,"dinner":1,"drinks":1,"lunch":1},"talking_about_count":28,"username":"007cafe","website":"www.007cafe.dotdsl.com","were_here_count":2066}';
$parser->init($json);
$st = $parser->parse();

print_r($st);die;


$container = [];

while(1) {
    
    $source->getNext($container, 'firnr');
    
    if(count($container['ids']) == 0) break;
    
    foreach ($container['result'] as $res)
    {
        $parser->init($res);
        $store = $parser->parse($res);        
        $target->save($store);
    }
}

