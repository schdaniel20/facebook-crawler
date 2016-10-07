<?php

namespace Cylex\Facebook\Parser;

class Parser {
    
    protected $data;
    protected $default;
    protected $firnr;
    protected $fbid;
    protected $lang;
    protected $sessionID;
    
    protected $services= array( 
        "delivery"  => "Delivery",
        "catering"  => "Catering",
        "groups"    => "Groups",
        "kids"      => "Kids",
        "outdoor"   => "Outdoor",
        "reserve"   => "Reserve",
        "takeout"   => "Takeout",
        "waiter"    => "Waiter",
        "walkins"   => "Walkins"
      );
    protected $paymentmethods= array( 
        "amex"          => "Amex",
        "cash_only"     => "Cash",
        "discover"      => "Discover",
        "mastercard"    => "Mastercard",
        "visa"          => "Visa"
      );
    protected $specialities= array( 
        "breakfast" => "breakfast",
        "coffee"    => "coffee",
        "dinner"    => "dinner",
        "drinks"    => "drinks",
        "lunch"     => "lunch"
            );
    protected $parkings= array( 
        "lot"       => "lot",
        "street"    => "street",
        "valet"     => "valet"
            );
    protected $days = [
        'Monday'    => 'mon',
        'Tuesday'   => 'tue',
        'Wednesday' => 'wed',
        'Thursday'  => 'thu',
        'Friday'    => 'fri',
        'Saturday'  => 'sat',
        'Sunday'    => 'sun'
    ];
    
    public function __construct(int $sessionID)
    {
        $this->sessionID = $sessionID;
    }

    public function init(array $data, $defaultValue = null)
    {   
        $this->data = json_decode($data['data'], true);
        $this->default = $defaultValue;
        $this->firnr = $data['firnr'];
        $this->fbid = $data['fbid'];
        $this->lang = $data['lang'];
    }
    
    protected function get($key, $def = null)
    {
        $path = explode('.', $key);    
        $value = $this->data;

        foreach($path as $p)
        {
            if(array_key_exists($p, $value))
            {
                $value = $value[$p];
            }
            else 
            {
                return $def;
            }
        }
        return $value;
    }
    
    protected function convertOh() 
    {
        $OhString = "";
        
        foreach($this->days as $key => $value)
        {
            $interval1 = $this->get('hours.' . $value . '_1_open'); 
            
            if($interval1)
            {
                $interval1 .= " - " . $this->get('hours.' . $value . '_1_close');
            }
            
            $interval2 = $this->get('hours.' . $value . '_2_open');
            
            if($interval2) {
                $interval2 .= " - " . $this->get('hours.' . $value . '_2_close');
                $interval1 .= " / " . $interval2;
            }
            
            if($interval1)
            {
                $OhString .= $key . " ";
            }
                
            $OhString .= $interval1 . " ";
        }
        return $OhString;
    }
    
    protected function fromArray(array $valuesMap, $jsonKey, $delimiter = '#')
    {
        $result = [];
        foreach($valuesMap as $key => $value)
        {
            if($this->get("{$jsonKey}.{$key}"))
            {
                $result[]=$value;
            }
        }
        return implode($delimiter, $result);
    }

    public function parse()
    { 
        $store = array();
        
        $store['CITY'] = $this->get('location.city', $this->default);        
        $store['COUNTRY'] = $this->get('location.country', $this->default);
        $store['GEO_LAT'] = $this->get('location.latitude', $this->default);
        $store['GEO_LNG'] = $this->get('location.longitude', $this->default);
        $store['STATE'] = $this->get('location.state', $this->default);
        $store['ADDRESS'] = $this->get('location.street', $this->default); 
        $store['ZIP'] = $this->get('location.zip', $this->default);
        
        $store['PAGELINK'] = $this->get('link', $this->default);
        $store['FILIAL_NAME'] = $this->get('name', $this->default);
        $store['PHONE'] = $this->get('phone', $this->default);
        $store['WEB'] = $this->get('website', $this->default);
        
        $store['OPENING_HOURS'] = $this->convertOh();
        
        $store['ACTIVE_SINCE'] = $this->get('founded', $this->default);
        $store['SHORTPROFILE'] = $this->get('about', $this->default);
        $store['DESCRIPTION'] = $this->get('description', $this->default);
        $store['PRODUCTS'] = $this->get('products', $this->default);        
        $store['CMP_OVERVIEW'] = $this->get('company_overview', $this->default);
        $store['FB_USERNAME'] = $this->get('username', $this->default);
        $store['AWARDS'] = $this->get('awards', $this->default);
        $store['MISSION'] = $this->get('mission', $this->default);
        $store['FIRNR'] = $this->firnr;
        $store['FBID'] =  $this->fbid;
        $store['LANG'] = $this->lang;
        $store['SESSIONID'] = $this->sessionID;
        
        $store['SERVICES'] = $this->fromArray($this->services, 'restaurant_services');
        $store['FACILITIES'] = $this->fromArray($this->specialities, 'restaurant_specialties');       
        $store['PAYMENTMETHODS'] = $this->fromArray($this->paymentmethods, 'payment_options');        
        $store['TRANSPORT_PARKING'] = $this->fromArray($this->parkings, 'parking');
        
        $categories = $this->get('category_list');
        $store['CATEGORY'] ='';
        
        if($categories)
        {
            foreach($categories as $cat)
            {
               $store['CATEGORY'] .= $cat['name'] . '#';
            }
        }
        $category = $this->get('category');
        
        if($category && !strpos($store['CATEGORY'], $category))
        {
            $store['CATEGORY'] .= $category;
        }
        
        return $store;
    }
}