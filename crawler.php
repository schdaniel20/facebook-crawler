<?php
require_once 'vendor/autoload.php';

use Facebook\Facebook;
use Cylex\Crawlers\Facebook\Crawler;
use Cylex\Crawlers\Facebook\DataSource;
use Cylex\Crawlers\Facebook\DataTarget;

$rargv = array_flip($argv);

$map = array(
	'-f' => null,
	'-c' => 50,
	'-p' => 0,
	'-s' => 12,
);

foreach($map as $key => $value)
{
	if(isset($rargv[$key]))
	{
		$v = $argv[$rargv[$key] + 1];
		
		if(is_int($value))
		{
			$v = (int) $v;
		}
		
		$map[$key] = $v;
	}
}


if(!isset($map['-f']) || !file_exists($map['-f']))
{	
	user_error('Invalid config file', E_USER_ERROR);
}


$conf = json_decode(file_get_contents($map['-f']), true);

$fb = $conf['facebook'];

$app = new Facebook(array(
	'app_id' => $fb['id'],
	'app_secret' => $fb['secret'],
	'default_access_token' => $fb['token'],
	'default_graph_version' => $fb['graph'],
));

$source = new DataSource($conf['source'], $conf['countryCode'], $conf['sessionID']);
$target = new DataTarget($conf['target']);


$crawler = new Crawler($source, $target);
$crawler->init($map['-s'], $map['-c'], $map['-p']);

echo 'Crawler started', PHP_EOL;
$crawler->run($app, $conf['fields']);
echo 'Crawler finished', PHP_EOL;

