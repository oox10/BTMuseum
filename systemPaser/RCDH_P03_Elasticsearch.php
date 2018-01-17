<?php
  
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  ob_start();
  
  require ROOT.'mvc/lib/vendor/autoload.php';
  ini_set("memory_limit", "2048M");
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  
  
  $index_name = strtolower(_SYSTEM_NAME_SHORT);
  
  $hosts = [
    '127.0.0.1:9200',         // IP + Port
    //'192.168.1.2',              // Just IP
    //'mydomain.server.com:9201', // Domain + Port
    //'mydomain2.server.com',     // Just Domain
    //'https://localhost',        // SSL to localhost
    //'https://192.168.1.3:9200'  // SSL to IP + Port
  ];
 
  $defaultHandler = Elasticsearch\ClientBuilder::defaultHandler();
  $singleHandler  = Elasticsearch\ClientBuilder::singleHandler();
  $multiHandler   = Elasticsearch\ClientBuilder::multiHandler();
  //$customHandler  = new MyCustomHandler();
  
  
  $client = Elasticsearch\ClientBuilder::create()
              ->setHandler($defaultHandler)
              ->setHosts($hosts)
			  ->setRetries(0)
			  ->build();
  
  
  //-- load meta assist from db 
  $db = new DBModule;
  $db->db_connect('PDO'); 
  
  $db_search = $db->DBLink->prepare("SELECT count(*) FROM metadata WHERE _keep=1;");
  if(!$db_search->execute()){	
    throw new Exception('insert fail');
  }	
  $total = $db_search->fetchColumn();
  
  $inedx_mode = 'bulk';
  
  echo "START INDEX [".$index_name."] : ".$total."..\n";
  
  
  $response = $client->indices()->delete(array('index' => $index_name));
  $response = $client->indices()->create(
    [  'index' => $index_name,
	   'body'  => [
	     //'settings' => [
		    //'index.cache.query.enable' => true,
            //'indices.cache.query.size' => '2%' 
		 //],
		 'mappings'=>[
		   '_default_'=>[
		     "date_detection"=>false
		    ],
			'my_type'=>[
			  '_source' => [
                    'enabled' => true
              ],
			  'properties' => [
                    'class' => [
                        'type' => 'keyword',
						'index' => true,
                    ],
					'zong'=> [  // 全宗代號
                        'type' => 'keyword',
						'index' => true
                    ],
					'fonds'=> [  // 全宗名稱
                        'type' => 'keyword',
						'index' => true
                    ],
					'data_type' => [
                        'type' => 'keyword',
						'index' => true,
                    ],
					'level'=> [
                        'type' => 'keyword',
						'index' => true
                    ],
					'series'=> [
                        'type' => 'keyword',
						'index' => true
                    ],
					'collection'=> [
                        'type' => 'text',
						'index' => true,
						"fielddata"=>true
                    ], 
					'identifier'=> [
                        'type' => 'text',
						'index' => true,
                        "fielddata"=>true
					],
					'applyindex'=> [
                        'type' => 'text',
						'index' => true,
                        "fielddata"=>true
					],
					'store_id'=> [
                        'type' => 'keyword',
						'index' => true,
					],
					'date_string' => [
                        'type' => 'text',
						"fielddata"=>true,
						'index' => true
                    ],
					'date_start' => [
                        'type' => 'date',
						//'index' => 'not_analyzed'
                    ],
					'date_end' => [
                        'type' => 'date',
						//'index' => 'not_analyzed'
                    ],
					
					
				    'title' => [
                        'type' => 'text',
						'index' => true
                    ],
					'categories' => [
                        'type' => 'text',
						'index' => true
                    ],
					'ethnic' => [
                        'type' => 'text',
						'index' => true
                    ],
					
					'acquire_type' => [
                        'type' => 'text',
						'index' => true
                    ],
					'acquire_info' => [
                        'type' => 'text',
						'index' => true
                    ],
					'status_code' => [
                        'type' => 'text',
						'index' => true
                    ],
					'status_descrip' => [
                        'type' => 'text',
						'index' => true
                    ],
					
					'store_date' => [
                        'type' => 'text',
						'index' => true
                    ],
					'store_location' => [
                        'type' => 'text',
						'index' => true
                    ],
					'store_number' => [
                        'type' => 'text',
						'index' => true
                    ],
					'store_boxid' => [
                        'type' => 'text',
						'index' => true
                    ],
					'store_information' => [
                        'type' => 'text',
						'index' => true
                    ],
					'count_dofiles' => [
                        'type' => 'integer',
                    ],
					'count_element' => [
                        'type' => 'integer',
                    ],
					'storeyear' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'savedyear' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'list_ethnic' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'list_store_type' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'list_status_code' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'list_store_location' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					'list_dotype' => [
                        'type' => 'keyword',
						'index' => true
                    ],
					
					'logout_flag' => [
                        'type' => 'boolean',
                    ],
					
					// system field
					'_flag_secret' => [
                        'type' => 'boolean',
                    ],
					'_flag_privacy' => [
                        'type' => 'boolean',
						
                    ],
					'_flag_open' => [
                        'type' => 'boolean',
						
                    ],
					'_flag_mask' => [
                        'type' => 'boolean',
						
                    ],
					'_flag_update' => [
                        'type' => 'date',
                    ],
					'_flag_view' => [
                        'type' => 'keyword',
						'index' => true
                    ],
               
			        
			   
			   ]
			]
		  ]
		]
    ]);	
  
  
  for( $i=0 ; $i<$total ; $i+=10000 ){
	  
    $db_search = $db->DBLink->prepare("SELECT * FROM metadata WHERE _keep=1 ORDER BY system_id ASC LIMIT ".$i.",10000;");
    if(!$db_search->execute()){	
      throw new Exception('insert fail');
    }	
  
    ob_flush();
    flush();
	
	
	$params = ['body' => []];
	$counter= $i+1;
	while($tmp = $db_search->fetch(PDO::FETCH_ASSOC)){
		
		if(!count($params['body'])) echo "\n START:".$tmp['system_id'];
		
		if($inedx_mode=='bulk'){
		  //-- bulk index
		  $params['body'][] = [
			'index' => [
				'_index' => $index_name,
				'_type'  => 'search',
				'_id'    => $tmp['system_id'],
			]
          ];
		
		  $search_array = json_decode($tmp['search_json'],true);
		  
		  $params['body'][] = $search_array;
		  $db->DBLink->query('UPDATE metadata SET _index=1 WHERE system_id='.$tmp['system_id'].';');
		  
		}else{
		  //-- singel index 
		  $params = array(
		    'index' => $index_name,
		    'type'  => 'search',
		    'id'    => $tmp['system_id'],
		    'body'  => json_decode($tmp['search_json'],true)
		  );
		  
		  $response = $client->index($params);	
		  ob_flush();
		  flush();
		
		  echo "\n".$tmp['system_id'].": ";
		
		  if(!$response['_shards']['successful']){
		    var_dump($tmp['search_json']);
		    exit(1);
		  }
		  echo "success.";	
		  $db->DBLink->query('UPDATE metadata SET _index=1 WHERE system_id='.$tmp['system_id'].';');
		
		}
		
		$counter = $tmp['system_id'];		
	}
	
	
	if($inedx_mode=='bulk'){ 
	  //-- bulk index
	
	  echo "- END:".$counter.": ";	
	  $responses = $client->bulk($params);
    
      if($responses['errors']){
	    var_dump($responses['total']);
	    exit(1);
      }
    
	  echo "success.";	
	  // erase the old bulk request
      $params = ['body' => []];
	  
	  // unset the bulk response when you are done to save memory
      unset($responses);
	}
	
	
  }
  
  // Send the last batch if it exists
  if (!empty($params['body'])) {
    $responses = $client->bulk($params);
  }
  
  
?>