<?php

  class Meta_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	   
	}
	
	protected $ResultCount;   // 查詢結果數量
	protected $PageNow;       // 當前頁數 
	protected $LengthEachPage;// 每頁筆數
	
	protected $Metadata;
	
	protected $SourceTableIndexFild = 'store_no';  //資料索引欄位  , 用來做為跨資料互相參照index的欄位
	
	/*[ Meta Function Set ]*/ 
    
	
	
	//-- Admin Meta Get source db table 
	// [input] : NONE
    public function ADMeta_Get_Zong_List(){
		
	  $result_key = parent::Initial_Result('zongs');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$group_2_zong = ['adm'=>['*'],'btm'=>['*'],'sln'=>['002'],'tea'=>['003'],'lay'=>['004']];	//'adm'=>'*',
	    $user_allow_zongs = [];
	    if(isset($this->USER->PermissionQue)&&is_array($this->USER->PermissionQue)){
		  $user_groups = array_keys($this->USER->PermissionQue);
	      foreach($user_groups as $g){
		    if(!isset($group_2_zong[$g])) continue;
            $user_allow_zongs = array_merge($user_allow_zongs,$group_2_zong[$g]);
		  }
	    }
		
		$user_allow_zongs = array_unique($user_allow_zongs);
		
		// 取得全宗資訊
		$zongs = [];
		$DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_ZONG_LIST());
		if($DB_GET->execute()){
		  while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			if(!in_array('*',$user_allow_zongs) && !in_array($tmp['zid'],$user_allow_zongs)) continue;
			$zongs[$tmp['zid']] = $tmp;    
		  }
		}
		
		$result['data']   = $zongs;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Meta Get source db table 
	// [input] : NONE
    public function ADMeta_Get_Table_Fields(){
		
	  $result_key = parent::Initial_Result('dbfield');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$fields_config = ['volume'=>[],'element'=>[]];
		
		// 取得資料表欄位資訊
		$DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_TABLE_FORMAT());
		
		// get source_digiarchive
		$DB_GET->bindValue(':dbtable'    , 'source_digiarchive');
		if($DB_GET->execute()){
		  while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			$fields_config['volume'][$field['dbcolumn']] = $field;
		  }
		}
		
		// get source_digielement
		$DB_GET->bindValue(':dbtable'    , 'source_digielement');
		if($DB_GET->execute()){
		  while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			$fields_config['element'][$field['dbcolumn']] = $field;
		  }
		}
		
		$result['data']   = $fields_config;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	//-- Admin Meta Process Search Filter 
	// [input] : $SearchConfig => 搜尋設定  (string)base64_decode();
	public function ADMeta_Process_Filter($SearchConfig=''){
	  
	  $result_key = parent::Initial_Result('filter');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $group_2_zong = ['adm'=>['*'],'btm'=>['*'],'sln'=>['002'],'tea'=>['003'],'lay'=>['004']];	 
	  $user_allow_zongs = [];
	  if(isset($this->USER->PermissionQue)&&is_array($this->USER->PermissionQue)){
		  $user_groups = array_keys($this->USER->PermissionQue);
	      foreach($user_groups as $g){
		    if(!isset($group_2_zong[$g])) continue;
            $user_allow_zongs = array_merge($user_allow_zongs,$group_2_zong[$g]);
		  }
	  }
	  $user_allow_zongs = array_unique($user_allow_zongs);
	  
	  try{
	    
		// 搜尋參數
		/* 
		zongs = [ ]
		limit[  // 條件篩選
		  none : 不限制   
		  secret : 密件  private: 隱私  mask : 遮頁資料  close : 未開放  newest : 最新資料
		]
		search[  // 一般搜尋
		  date_start : 
		  date_end : 
          condition :
		]
		order[
		  modify_time
		  identifier
		  date_start
		]
		
		logout => 0/1  //註銷
		
		*/
		
		
		
	    
  	    $data_search = json_decode(base64_decode(str_replace('*','/',rawurldecode($SearchConfig))),true); 
		
		// 處理搜尋條件
		$time_query = array(); // 時間參數
		$term_query = array(); // 條件參數
		$order_conf = array(); // 排序參數
		
		$heightline = array();
		
 
		$term_query = [];
		
		//$data_search['search']['condition'] = "蘇貞昌";
		 
		if(isset($data_search['search'])&&count($data_search['search'])){
		  foreach($data_search['search'] as $i => $search_set ){
			
            switch($search_set['field']){
              case 'date_start':
			    if(strtotime($searchstring)){
				  $time_query['start']	= date('Y-m-d',strtotime($searchstring));	
				}
				break;
				
              case 'date_end':
                if(strtotime($searchstring)){
				  $time_query['end']	= date('Y-m-d',strtotime($searchstring));	
				}
				break;
				
			  default:  
				
				if(trim($search_set['value'])=='') continue;
				
				$search_field    = $search_set['field'] =='_all' ? '' : $search_set['field'].':';
				$search_and_sets = preg_split('/[&]/',$search_set['value']);
				foreach($search_and_sets as $termset){
				  
				  $attr = $search_set['attr'];
				  if(preg_match('/^\-/',$termset)){
					$termset = preg_replace('/^-/','',$termset);  
					$attr = '-';
				  }
				  
				  $search_or_sets = preg_split('/[|\s+]/',$termset);		  
				  
				  if($attr == '+'){
					$term_query[] = $search_field.'("'.join('" | "',$search_or_sets).'")';   
				  }else{
					$term_query[] = $search_field.'(-"'.join('" & "-',$search_or_sets).'")';    
				  }
				
				  $heightline = array_merge($heightline,$search_or_sets);
				}
			  break;
			}			
		  }
		  
		  // 將時間搜尋加入條件
		  /*
		  if(count($time_query)){
			$term_query[] = "date_start:[ ".(isset($time_query['start'])?$time_query['start']:'*')." TO ".(isset($time_query['end'])?$time_query['end']:'*')." ]"; 
		  }
		  */
		}
		
		
		if(isset($data_search['data_zong'])&&count($data_search['data_zong'])){
		  $search_zong = [];
		  foreach($data_search['data_zong'] as $z){
			if( in_array('*',$user_allow_zongs) || in_array($z,$user_allow_zongs)){
			  $search_zong[] = $z;	
			}
		  }
		  $term_query[] = 'zong:('.join(' ',$search_zong).')';	
		}else{
		  $term_query[] = 'zong:('.join(' ',$user_allow_zongs).')';	
		}
		
		if(isset($data_search['logout'])&&$data_search['logout']){
		  $term_query[] = 'logout_flag:1';	
		}
		
		$type_query = $data_search['data_type']=='element' ? 'data_type:element':'data_type:collection';
		
		
		
		$params =[
			"size" => 20,
			"from" => 0,
			'index' => strtolower(_SYSTEM_NAME_SHORT),
			'type' => 'search',
			'body' => [
			  'query'=>[
				 "query_string" => [
					//"query"=> "(\"蔣中正\") AND (\"顧祝\") AND location:(*漢口* *江西*) AND in_store_no:00200000*",
					"query"=> $type_query." ".( count($term_query) ? " AND ".join(" AND ",$term_query) : '' ),
				 ],
			  ],
			  "sort"=>[
			    "collection"=>["order"=>"asc"],
				"identifier"=>["order"=>"asc","missing"=>"_last"]
			  ],
			  "post_filter"=>[
			    "bool" =>[
				  "must"=>[
				   // ["terms"=>['person'=>['陳誠']]]
				  ]
				]
			  ],
			  "aggs"=>[
				"pq_list_store_type"=>[
				  "terms"=>[
					"field"=>"list_store_type",
					"size" => "50"	
				  ]
				],
				"pq_list_ethnic"=>[
				  "terms"=>[
					"field"=>"list_ethnic",
					"size" => "50"	
				  ]
				],/*
				"pq_savedyear"=>[
				  "terms"=>[
					"field"=>"savedyear",
					"size" => "50"	
				  ]
				],*/
				"pq_list_store_location"=>[
				  "terms"=>[
					"field"=>"list_store_location",
					"size" => "50",
					"order"=>[
					  "_term" => "asc" 
					]		
				  ]
				],
				"pq_list_status_code"=>[
				  "terms"=>[
					"field"=>"list_status_code",
					"size" => "50",
					"order"=>[
					  "_term" => "asc" 
					]		
				  ]
				],
				"pq_list_dotype"=>[
				  "terms"=>[
					"field"=>"list_dotype",
					"size" => "50",
					"order"=>[
					  "_term" => "asc" 
					]		
				  ]
				]
			  ],
			 
			] 
		];
		
		$post_filter = [];
		if(isset($data_search['pquery'])){
		  foreach($data_search['pquery'] as $pfield => $pterms){
			$post_filter[] = ["terms"=>[$pfield=>$pterms]];  
		  }
		}
		
		if(count($post_filter)){
		  $params['body']['post_filter']['bool']['must'] = $post_filter;	
		}
		 
		/* 
		echo "<pre>";
	    var_dump($params);
		exit(1);
		*/
		$result['action'] = true;
		$result['data']['submit']   = $data_search;
	    $result['data']['esparams'] = $params;
		$result['data']['termhit']  = array_unique($heightline);
	    
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Meta Search Query 
	// [input] : $DataType => _all / 檔案 / 公報 / 議事錄 / 議事影音 /  議員傳記  /  活動照片;
	// [input] : $SearchConfig => 搜尋設定  (string)base64_decode();
	
	public function ADMeta_Execute_Search($SearchPattern=array(),$Pageing){
	  
	  $result_key = parent::Initial_Result('search');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		 
        $work_folder = []; // 工作資料夾  
		 
        $Pageing = trim($Pageing);
		if(!preg_match('/^\d+\-\d+$/', $Pageing )) $Pageing = '1-50';	
		list($p_start,$p_end) = explode('-',$Pageing);
		
		// 防止網址竄改
		if($p_start > 10000){
		  $p_start =  9901;
          $p_end   = 10000;
		}else if( $p_start >=  $p_end){
		  $p_end = $p_start+20;	
		}else if( $p_end-$p_start > 999){
          $p_end = $p_start+999;
		}
		
	    $params = $SearchPattern;  
		$params['size'] = $p_end - $p_start + 1;
		$params['from'] = $p_start - 1;
		
		$this->PageNow     	  = intval($p_end/$params['size']);   
	    $this->LengthEachPage = $params['size'];
		
		$hosts = [
		  '127.0.0.1:9200',         // IP + Port
	    ];
	    //require _SYSTEM_ROOT_PATH.'mvc/lib/vendor/autoload.php';
		$defaultHandler = Elasticsearch\ClientBuilder::defaultHandler();
		//$singleHandler  = Elasticsearch\ClientBuilder::singleHandler();
		//$multiHandler   = Elasticsearch\ClientBuilder::multiHandler();
	    //$customHandler  = new MyCustomHandler();
		
	    $client = Elasticsearch\ClientBuilder::create()
				  ->setHandler($defaultHandler)
				  ->setHosts($hosts)
				  ->setRetries(0)
				  ->build();
		$response = $client->search($params);
        
		//file_put_contents('logs.txt',print_r($response,true));
		
		$this->ResultCount   = intval($response['hits']['total']);   // 查詢結果數量
		
		$result_source  = isset($response['hits']['total']) && intval($response['hits']['total']) ? $response['hits']['hits']:array();
		$result_indexs  = count($result_source) ? array_map(function($document){return $document['_id'];},$result_source ) : array();
		
		// 取得db資料
		$data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SEARCH_META());
		foreach($result_source as $key => $search){
		  if( !$DB_GET->execute(array('system_id'=> $search['_id']))){
		    continue;
		  }  
		  
		  $meta = $DB_GET->fetch(PDO::FETCH_ASSOC);
		  $source = json_decode($meta['source_json'],true);
		  
		  $result_source[$key]['_search'] = json_decode($meta['search_json'],true); 
		  $result_source[$key]['@thumb']  = $source['collection']['cover_page'] ? $source['collection']['cover_page'] :  $source['collection']['store_no'].'-002.jpg' ; 
		  $result_source[$key]['_dbsource'] = json_decode($meta['source_json'],true);
		  
		  
		  $work_folder[] = $search['_id'];
		
		}
		
		
		// 設定後分類
		$pquery = array();
		foreach($response['aggregations'] as $pqfield => $arrset){
		  $pquery[preg_replace('/^pq_/','',$pqfield)] = $arrset['buckets'];	
		}
		
		 
		/*
		// 頁面參數
		$this->ResultZongAgg = isset($response['aggregations']['pq_zong'])  ? $response['aggregations']['pq_zong']['buckets'] : array();
		$this->ResultYearAgg = isset($response['aggregations']['pq_yearnum']) ? $response['aggregations']['pq_yearnum']['buckets'] : array();
		*/
		
		$result['action'] = true;
		$result['data']['list']   = $result_source;
		$result['data']['count']  = isset($response['hits']['total']) && intval($response['hits']['total']) ? $response['hits']['total']:0;
		$result['data']['range']  = '1-'.($p_end-$p_start+1);
		$result['data']['start']  = $p_start;
		$result['data']['limit']  = array('start'=>$p_start,'length'=>$params['size'],'range'=>$Pageing);	
		$result['data']['pterm']  = $pquery;	
		$result['data']['folder'] = $work_folder;
	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	
	}
	
	
	//-- Admin Get User folders 
	public function ADMeta_Get_Folder_List(){
	  
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $result['session']['_ADMETA_FOLDERS'] = [];
	  if(isset($this->ModelResult['search']['data']['folder']) && count($this->ModelResult['search']['data']['folder'])){
		 $result['session']['_ADMETA_FOLDERS']['search'] = $this->ModelResult['search']['data']['folder'];  
	  }
	  
	  
	  try{
		
		$folder = [];
		
		$editor_config_array=[];
	    $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
	    if(file_exists($editor_config_file)){
		  $editor_config_array = json_decode(file_get_contents($editor_config_file),true);     
		}else{
		  $editor_config_array = [];  	
		} 
		
		if(!isset($editor_config_array['folders'])){
		  $editor_config_array['folders'] = [
		    'myfolder'=>[
		      'name'=>'我的工作區',
			  'timeupdate'=>'',
			  'records'=>[],
			  'remark'=>''
			  
		    ]	
		  ];
		  file_put_contents($editor_config_file,json_encode($editor_config_array));   
		}
		
		foreach($editor_config_array['folders'] as $fid => $folder_config){
          
		  $folder[$fid] = $folder_config;
		  $folder[$fid]['result'] = [];
		  
		  $result['session']['_ADMETA_FOLDERS'][$fid] = [];
		  
		  if(!is_array($folder_config['records']) || !count($folder_config['records'])){
			continue;  
		  }
		  // 取得db資料
		  $result_source = [];
		  $work_folder   = [];
		  $DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_SELECTED($folder_config['records']));
		  if( !$DB_GET->execute()){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		  }
		  while($meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
            $key    = $meta['system_id'];
			$source = json_decode($meta['source_json'],true);
            $result_source[$key]['_id'] = $key;			
			$result_source[$key]['_search'] = json_decode($meta['search_json'],true); 
			$result_source[$key]['@thumb']  = $source['collection']['cover_page'] ? $source['collection']['cover_page'] :  $source['collection']['store_no'].'-002.jpg' ; 
			$result_source[$key]['_db']['@time'] = $meta['@time'];
			$result_source[$key]['_db']['@user'] = $meta['@user'];
			$result_source[$key]['_db']['count_element'] = $source['collection']['count_element'];
			$result_source[$key]['_db']['count_dofiles'] = $source['collection']['count_dofiles'];
			$work_folder[] = $key;
		  }
		  $folder[$fid]['result'] = $result_source;
		  $result['session']['_ADMETA_FOLDERS'][$fid] = $work_folder;
		}
		
		$result['session']['_ADMETA_FOLDERS']['_focus'] = 'search';
		$result['action'] = true;
		$result['data']   = $folder;
		
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Meta Page Data OList 
	// [input] : $PagerMaxNum => int // 頁面按鈕最大數量
	public function ADMeta_Get_Page_List( $PagerMaxNum=1 ){
	  
	  $result_key = parent::Initial_Result('page');
	  $result  = &$this->ModelResult[$result_key];
      
	  try{
        
		
		$page_show_max = intval($PagerMaxNum) > 0 ? intval($PagerMaxNum) : 1;
		
		
	    $pages = array();
		
		$pages['all'] = array(1=>'');
		
		
		// 必要參數，從ADMeta_Get_Meta_List而來
		$this->ResultCount;   // 查詢結果數量
	    $this->PageNow;   
	    $this->LengthEachPage;
		
		$total_page = ( $this->ResultCount / $this->LengthEachPage ) + ($this->ResultCount%$this->LengthEachPage ? 1 :0 );
		
		// 建構分頁籤
		for($i=1;$i<=$total_page;$i++){
		  $pages['all'][$i] = (($i-1)*$this->LengthEachPage+1).'-'.($i*$this->LengthEachPage);
		}
		
		$pages['top']   = reset($pages['all']);
		$pages['end']   = end($pages['all']);
		$pages['prev']  = ($this->PageNow-1 > 0 ) ? $pages['all'][$this->PageNow-1] : $pages['all'][$this->PageNow];
		$pages['next']  = ($this->PageNow+1 < $total_page ) ? $pages['all'][$this->PageNow+1] : $pages['all'][$this->PageNow];
		$pages['now']   = $this->PageNow;  
		
		$check = ($page_show_max-1)/2;
	    if($total_page < $page_show_max){
		  $pages['list'] = $pages['all'];  	
		}else {  
          if( ($this->PageNow - $check) <= 1 ){    // 抓最前面 X 個
            $start = 0;
		  }else if( ($this->PageNow + $check) > $total_page ){  // 抓最後面 X 個
            $start = $total_page-(2*$check)-1;    
		  }else{
            $start = $this->PageNow - $check -1;
		  }
	      $pages['list'] = array_slice($pages['all'],$start,$page_show_max,TRUE);
		}
		
		// 建構選項
		$effect_page = count($pages['all']);
		
		if(count($pages['all']) > 500 ){
			for($x=1;$x<=$effect_page;$x++){
			  if($x==1 || $x==$effect_page || abs($x-$this->PageNow)<20){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)<100 && $x%10===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)<1000 && $x%200===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)>=1000 &&  abs($x-$this->PageNow)<10000 && $x%1000===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)>=10000 && $x%10000===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }
			}
		  
		}else{
		  $pages['jump'] = $pages['all'];	
		}
		unset($pages['all']);
		
		
		$result['data']   = $pages;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	
	}
	
	
	//-- Admin Meta Execute User Select Batch
	// [input] : SelectedSids  :  encoed array string;
	// [input] : Action        :  open / view / ;  !strtolower-Y
	// [input] : Setting       :  (open):0/1 (view):開放/限閱/會內/關閉  ;
	
	public function ADMeta_Execute_Batch($SelectedSids,$Action,$Setting){
		
	  $result_key = parent::Initial_Result('batch');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 允許之批次設定
		$accept_action = array('open','view','export');
		$accept_view_config = array('開放','限閱','會內','關閉');
		
		$data_batch_counter = 0;
		$data_selected = json_decode(base64_decode(str_replace('*','/',rawurldecode($SelectedSids))),true); 
		
		// check permission
		if(  !intval($this->USER->PermissionNow['group_roles']['R00']) && !intval($this->USER->PermissionNow['group_roles']['R02']) ){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		} 
		
		// check data
		if(!count($data_selected)) throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		if(!in_array($Action,$accept_action)) throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		
		// set config 
		$meta_batch = array();
		switch($Action){
		  case 'open': $meta_batch['_flag_open'] = intval($Setting); break;
          case 'view':
		    if(in_array($Setting,$accept_view_config)){
			  $meta_batch['_view'] = $Setting ; 
			}
			break;
		}
		if(!count($meta_batch)) throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	
		// get data set
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_SELECTED($data_selected));
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		while($meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  
		  // 取得原始資料
		  $source = array();
		  $DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_META($meta['zong']));
		  $DB_SOURCE->bindParam(':id'   , $meta['identifier'] );	
		  if( !$DB_SOURCE->execute() || !$source = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
		    continue;
		  }
          
		  // 補充系統欄位
		  $meta_batch['_userupdate'] = $this->USER->UserID;
			
		  // 執行修改
		  $DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($meta_batch),$meta['zong']));
		  $DB_SAVE->bindValue(':id'    , $meta['identifier']);
		  foreach($meta_batch as $uf=>$uv){
			$DB_SAVE->bindValue(':'.$uf , $uv);
		  }
		  if( !$DB_SAVE->execute()){
			continue;
		  }
		  
		  // 執行logs
		  $DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		  $DB_LOGS->bindValue(':zong' 	   , $meta['zong']);
		  $DB_LOGS->bindValue(':sysid' 	   , $meta['system_id']);
		  $DB_LOGS->bindValue(':identifier', $meta['identifier']);
		  $DB_LOGS->bindValue(':method'    , 'BATCH:'.$Action);
		  $DB_LOGS->bindValue(':source'    , json_encode($source));
		  $DB_LOGS->bindValue(':update'    , "");
		  $DB_LOGS->bindValue(':user' , $this->USER->UserID);
		  $DB_LOGS->bindValue(':result' , 1);
		  $DB_LOGS->execute();
		  
		  // 執行更新
		  $active = self::ADMeta_Process_Meta_Update($meta['system_id']);
		  if(!$active['action']){
			continue;  
		  }
		  
		  $data_batch_counter++;
		}
		
		// final
		$result['action'] = true;
		$result['data'] = $data_batch_counter;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	//-- Admin Meta Execute User Select Batch
	// [input] : SelecteExportEncode   :  encoed array string  [records=>[] , efields => [volume=> [] , element=>[] ]];
	/*
	   輸出原始資料excel
	   架構相關格式
	   註冊匯出金鑰
	   輸出參考資源
	*/
	
	public function ADMeta_Export_Selected($SelecteExportEncode){
		
	  $result_key = parent::Initial_Result('batch');
	  $result  = &$this->ModelResult[$result_key];
	  
	  function colnum2code($colnumber){ //COL欄位轉換
		/*range A - ZZ */
		$col_range = range('A','Z');
		$col_index = intval($colnumber);
		$col_code  = '';
		if(intval($col_index/26)){
		  $col_code = $col_range[intval($col_index/26)-1];
		  $col_code .= ($col_index%26) ? $col_range[($col_index%26-1)]:'A'; 
		}else{
		  $col_code	= $col_range[$col_index];
		}
		return $col_code;
	  }
	  
	  
	  try{  
		
		
		$data_batch_counter = 0;
		$data_export_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($SelecteExportEncode))),true); 
		
		// check permission
		if(  !intval($this->USER->PermissionNow['group_roles']['R00']) && !intval($this->USER->PermissionNow['group_roles']['R02']) ){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		} 
		
		$data_export_package['records']; // [1 2 3 4 ...]
		$data_export_package['efields']; // [volume=>[] element=>[]]
		
		
		// check data
		if(!count($data_export_package['records'])) throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		
		
		//== 註冊匯出序號
		$export_key = md5($this->USER->UserID.':'.microtime().':'.count($data_export_package['records']));
		
		
		//== 取得匯出資料 ==
		// get data set
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_SELECTED($data_export_package['records']));
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		$excel = [];
		$DB_LOGS = $this->DBLink->prepare( SQL_AdMeta::LOGS_META_EXPORT());
		while($meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  
		  if(!isset($excel[$meta['class']])) $excel[$meta['class']]=['volume'=>[],'element'=>[]];
		  
		  $export_meta = NULL;
		  $source_meta = json_decode($meta['source_json'],true);
		  
		  if(!isset($excel[$meta['class']]['volume'][$source_meta['collection']['store_no']])){
			$export_meta = $source_meta['collection'];
			$excel[$meta['class']]['volume'][$source_meta['collection']['store_no']] = $export_meta; 
		  }
		  
		  if(isset($source_meta['element'])&&isset($source_meta['element']['store_no'])){
			$export_meta = $source_meta['element'];  
			$excel[$meta['class']]['element'][$source_meta['element']['store_no']] = $export_meta; 
		  }
		  
		  if($export_meta){
			$data_batch_counter++;
		    // 註冊匯出紀錄
		    $DB_LOGS->bindValue(':exportkey',$export_key);
		    $DB_LOGS->bindValue(':system_id',$meta['system_id']);
		    $DB_LOGS->bindValue(':meta_version',$export_meta['_timeupdate']);
		    $DB_LOGS->bindValue(':user',$this->USER->UserID);
		    $DB_LOGS->execute();
		  }
		  
		}
		
		//儲存資料匯出版本控制檔
		$export_store = _SYSTEM_DIGITAL_FILE_PATH.'METADATA/export/'.$export_key.'.json';
		file_put_contents($export_store,json_encode($excel,JSON_UNESCAPED_UNICODE));
		
		
		//== 設定匯出資料格式 ==
		$fstructure = ['volume'=>[],'element'=>[]];  // 資料欄位設計
		$freference = ['volume'=>[],'element'=>[]];  // 資料匯出參考
		$fformat = [];  // 欄位檢測 
		$excel_template = 'template_btm_source_metadata.xlsx';
		
		
		
		foreach($excel as $sheet=>$data_export ){	
		  
		  
		  // 設定資料集相關
		  switch($sheet){
		    
			case 'relic': // 
			  
			  // 取得資料表欄位資訊
			  $DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_DBTABLE_DESCRIP());
			  $DB_GET->bindValue(':dbname'   , _SYSTME_DB_NAME);
				
			  // get source_digiarchive
			  $DB_GET->bindValue(':table'    , 'source_digiarchive');
			  if($DB_GET->execute()){
				while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
				  if(preg_match('/^[\_@]/',$field['COLUMN_NAME']) && !preg_match('/^_count/',$field['COLUMN_NAME']) ) continue;
				  $fstructure['volume'][$field['COLUMN_NAME']] = $field;
				}
			  }
				
			  // get source_digielement
			  $DB_GET->bindValue(':table'    , 'source_digielement');
			  if($DB_GET->execute()){
				while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
				  if(preg_match('/^[\_@]/',$field['COLUMN_NAME'])) continue;
				  $fstructure['element'][$field['COLUMN_NAME']] = $field;
				}
			  }
			  
			  
			  // 取得卷欄位檢測參考表  meta_format
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digiarchive');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat['volume'][$field['dbcolumn']] = $field;
				  if($field['module']=='S' && $field['pattern']){
                    $freference['volume'][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				  }
			  }
			  
			  // 取得件欄位檢測參考表  meta_format 
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digielement');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat['element'][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference['element'][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  break;
			
			default: break;
		  }
		  
		  
		  // 取得 CLASS ZONGS
		  $freference['volume']['zong'] = [];
		  $DB_ZONG = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_INFO());
		  $DB_ZONG->bindParam(':zclass', $sheet );	
		  if( !$DB_ZONG->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  while($tmp = $DB_ZONG->fetch(PDO::FETCH_ASSOC)){
			$freference['volume']['zong'][$tmp['zid']] = $tmp['zname'];	
		  }
			
		  // 取得全宗分類資料
		  $freference['volume']['level'] = [];
		  $zclass = [];
		  $DB_CLASS = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_CLASS());
		  $DB_CLASS->bindParam(':zclass', $sheet );	
		  if( !$DB_CLASS->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  $zclass =  $DB_CLASS->fetch(PDO::FETCH_ASSOC);
			
		  // 建構全宗分類
		  $DB_ZLV = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_LEVEL());
		  function builtzongclass($dblink,$bindno,$znamepre,$zlevel){
			$dblink->bindParam(':mcno', $bindno );	
			$dblink->execute();          
			$lvs = $dblink->fetchAll(PDO::FETCH_ASSOC);
			  
			if(count($lvs)){
			  foreach($lvs as $lv){
				$zlevel[$lv['class_code']] = $znamepre.'/'.$lv['class_name'];	
				$zlevel = $zlevel+builtzongclass($dblink,$lv['mcno'],$znamepre.'/'.$lv['class_name'],$zlevel);    
			  }  
			}
			return  $zlevel; 
		  }
			
		  if(isset($zclass['mcno'])){
			$freference['volume']['level'] = builtzongclass($DB_ZLV,$zclass['mcno'],$zclass['class_name'],[]);	
		  }
		  
		  
		  //php excel initial
		  $objReader = PHPExcel_IOFactory::createReader('Excel2007');
		  $objPHPExcel = $objReader->load(_SYSTEM_ROOT_PATH.'mvc/templates/'.$excel_template);
		   
          $ref_col = 0;
		  $refer_colindex = [];
		  
				  
		  //填入入資料
		  foreach($data_export as $data_level => $data_list){
			
			// 設定 excel sheet  
			$col = 0 ;
			$row_start = 6 ;
			$active_sheet = null;
			
			if($data_level == 'volume' ){  
			  $active_sheet = $objPHPExcel->setActiveSheetIndex(0);
		      $row_max   = $row_start+count($data_list);
			}else{
			  $active_sheet = $objPHPExcel->setActiveSheetIndex(1);	
			}
		    
			//取得excel與DB欄位位置對應
			$col = 0; $row=5;    // 系統預設excel格式，第4行為欄位ID
			$dbf2colindex=['d2x'=>[],'x2d'=>[]];    // db欄位對應xls row 位置  
			
			// 檢視是否存在表欄位設定
			if(!isset($data_export_package['efields'][$data_level])){
			  continue;	
			}
			
			// 將欄位填入excel 並設定對應
			$fcol = 0; $fraw=4; // 3 欄位名稱 4 欄位id 
			foreach($data_export_package['efields'][$data_level] as $field_id){
			  if(!isset($fstructure[$data_level][$field_id])) continue;
              $active_sheet->getCellByColumnAndRow($fcol, 5)->setValueExplicit($fstructure[$data_level][$field_id]['COLUMN_NAME'], PHPExcel_Cell_DataType::TYPE_STRING);   			  
              $active_sheet->getCellByColumnAndRow($fcol, 4)->setValueExplicit($fstructure[$data_level][$field_id]['COLUMN_COMMENT'], PHPExcel_Cell_DataType::TYPE_STRING);   			  
			  //if(!isset($fformat[$data_level][$field])) continue;  
			  $dbf2colindex['d2x'][$field_id] = $fcol;
			  $dbf2colindex['x2d'][$fcol] = $field_id;
			  $fcol++;
			}
			
			// 填入內容
			$row = $row_start;  
			foreach( $data_list as $data){
				$col = 0;
				foreach($data as $f=>$v){
				  if( $f=='class') continue;
				  if( (preg_match('/^_/',$f) && !preg_match('/^_count/',$f))  ||  !isset($dbf2colindex['d2x'][$f]) )  continue; 
				  if(!trim($active_sheet->getCellByColumnAndRow($col,5))) break;
				  if(is_array($v)) $v = join(';',$v);
				  $active_sheet->getCellByColumnAndRow($dbf2colindex['d2x'][$f], $row)->setValueExplicit($v, PHPExcel_Cell_DataType::TYPE_STRING);  	
				  $col++; 
				}
				$row++;
			}
			
			// 設定顯示樣式
		    foreach($dbf2colindex['d2x'] as $dbf => $xlscol){
				$col_code  = colnum2code($xlscol);  
				
			    $cell_style = array();
			    
				// index
				if(isset($fformat[$data_level][$dbf]['autonew']) && $fformat[$data_level][$dbf]['autonew']) continue;
				
				
				// 必填
				if(isset($fformat[$data_level][$dbf]['nessary']) && $fformat[$data_level][$dbf]['nessary'] ){
					$cell_style['borders'] = array(
								'allborders' => array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
									'color' => array('rgb' => '4682b4')
								)
							);		
				}
			    
				// 系統生成
				if(isset($fformat[$data_level][$dbf]['fromsys']) && $fformat[$data_level][$dbf]['fromsys'] ){
					$cell_style['fill'] = array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'eee8aa')
							    );
				}
				
				if(count($cell_style)){
				  $active_sheet->getStyle($col_code.$row_start.':'.$col_code.($row_max+100))->applyFromArray($cell_style);	
				}
		    
			    $active_sheet->getColumnDimension($col_code)->setAutoSize(true);
			  
			
			}// endof style set
			
			
		    // 放入參考資料
		    $reference_sheet = $objPHPExcel->setActiveSheetIndexByName('REFERENCE'); 
			$refer = $freference[$data_level];
			
			foreach($refer as $rf => $rset){
				$row = 3;
				if($rf=='zong'){        // 特殊欄位參考
				  $refer_colindex['zong'] = ['index'=>$ref_col,'set'=>array_keys($refer[$rf])];
				  $refer_colindex['fonds'] = ['index'=>$ref_col+1,'set'=>array_values($refer[$rf])];
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['zong']['index'], 2)->setValueExplicit('zong', PHPExcel_Cell_DataType::TYPE_STRING);  
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['fonds']['index'], 2)->setValueExplicit('fonds', PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($refer_colindex['zong']['index'], $row)->setValueExplicit($rid, PHPExcel_Cell_DataType::TYPE_STRING);  
					$reference_sheet->getCellByColumnAndRow($refer_colindex['fonds']['index'], $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				  $ref_col+=1;
				}else if($rf=='level'){
				  $refer_colindex['level'] = ['index'=>$ref_col,'set'=>array_keys($refer[$rf])];
				  $refer_colindex['series'] = ['index'=>$ref_col+1,'set'=>array_values($refer[$rf])];
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['level']['index'], 2)->setValueExplicit('level', PHPExcel_Cell_DataType::TYPE_STRING);  
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['series']['index'], 2)->setValueExplicit('series', PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($refer_colindex['level']['index'], $row)->setValueExplicit($rid, PHPExcel_Cell_DataType::TYPE_STRING);  
					$reference_sheet->getCellByColumnAndRow($refer_colindex['series']['index'], $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				  $ref_col+=1;
				}else{  				// 普通欄位參考  			
				  $refer_colindex[$rf] = ['index'=>$ref_col,'set'=>$rset];
				  $reference_sheet->getCellByColumnAndRow($ref_col, 2)->setValueExplicit($rf, PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($ref_col, $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				}
                $ref_col++;
				
			}// end of data insert
			
			
			// 設定參考
			// 設訂欄位參考選單與對應
			/* 
			foreach($dbf2colindex['d2x'] as $field => $data_col){
				  
				if(!isset($refer_colindex[$field])) continue;
				  
				$refer_col_code  = colnum2code($refer_colindex[$field]['index']);  // 參考欄位COL代號
				  
				$objValidation01 = $active_sheet->getCellByColumnAndRow($data_col,$row_start)->getDataValidation();
				$objValidation01->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
				$objValidation01->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
				$objValidation01->setAllowBlank(false);
				$objValidation01->setShowDropDown(true);
				$objValidation01->setFormula1('REFERENCE!$'.$refer_col_code.'$3:$'.$refer_col_code.'$'.(3+count($refer_colindex[$field]['set'])));
				
				for($i=($row_start) ; $i<$row_max ; $i++){
					$active_sheet->getCellByColumnAndRow($data_col,$i)->setDataValidation(clone $objValidation01);
					
					if($field == 'fonds' || $field == 'series'){
					  
					  $col_main = colnum2code(($dbf2colindex['d2x'][$field]));	 		// 設定連動之欄位COL代號
					  $col_rele = colnum2code(($dbf2colindex['d2x'][$field]-1));	 		// 設定連動之欄位COL代號
					  $col_search_s = colnum2code(($refer_colindex[$field]['index']-1)); // 連動參考起始欄位 
					  $col_search_e = colnum2code($refer_colindex[$field]['index']);     // 連動參考結束欄位
					  
					  $row_refer_s = 3;
					  $row_refer_e = ($row_refer_s+count($refer_colindex[$field]['set']));
					  
					  // 正向連動 查A回B
					  //$active_sheet->setCellValue($col_rele.$i,'=VLOOKUP('.$col_main.$i.',REFERENCE!'.$col_search_s.'3:'.$col_search_e.(3+count($refer_colindex[$field]['set'])).',1,FALSE)');
					
					  // 反向連動 查B回A
					  $active_sheet->setCellValue($col_rele.$i,'=INDEX(REFERENCE!$'.$col_search_s.'$'.$row_refer_s.':$'.$col_search_s.'$'.$row_refer_e .',MATCH('.$col_main.$i.',REFERENCE!$'.$col_search_e.'$'.$row_refer_s.':$'.$col_search_e.'$'.$row_refer_e.',0))');
					
					}
					
				}
			}//end of active refernece  
			*/
		  }
		}
		 
		// 設定序號
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 2)->setValueExplicit(date('Y-m-d H:i:s'), PHPExcel_Cell_DataType::TYPE_STRING);  	
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 3)->setValueExplicit($export_key, PHPExcel_Cell_DataType::TYPE_STRING);  	
		
        $excel_file_name =  _SYSTEM_NAME_SHORT.'_export_'.date('Ymd');
		$objPHPExcel->setActiveSheetIndex(0);
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$excel_file_name.'.xlsx'); 
		unset($objPHPExcel);
		
		// final
		$result['data']['fname']   = $excel_file_name;
		$result['data']['count']   = $data_batch_counter;
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta :Meta Batch Export XLSX 
	// [input] : FileName  : logs_digital.note	
	public function ADMeta_Access_Export_File( $FileName=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
	    
		if(!$FileName){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		} 
		
		$file_path = _SYSTEM_USER_PATH.$this->USER->UserID.'/'.$FileName.'.xlsx';
		if(!file_exists($file_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// final 
		$result['data']['name']  = $FileName.'.xlsx';
		$result['data']['size']  = filesize($file_path);
		$result['data']['location']  = $file_path;
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Meta Execute User Select Batch
	// [input] : SelecteExportEncode   :  encoed array string  [records=>[] , pfields =>[] , rowseach=>1-15   , hasthumb => 0/1  ];
	public function ADMeta_PrintOut_Selected($SelecteExportEncode){
		
	  $result_key = parent::Initial_Result('print');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		$data_batch_counter = 0;
		$data_export_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($SelecteExportEncode))),true); 
		
		$data_export_package['records'];  // [1 2 3 4 ...]
		$data_export_package['pfields'];  // []
		$data_export_package['rowseach']; // 1-15
		$data_export_package['hasthumb']; // 0/1
		
		// check data
		if(!count($data_export_package['records'])) throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		
		// 取得資料表欄位資訊
		// get source_digiarchive
		$DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_TABLE_FORMAT());
		$DB_GET->bindValue(':dbtable'    , 'source_digiarchive');
		if($DB_GET->execute()){
		  while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			$fields_config['volume'][$field['dbcolumn']] = $field;
		  }
		}
		
		//== 取得匯出資料 ==
		// get data set
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_SELECTED($data_export_package['records']));
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$print_records = [];
		while($meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $source_meta = json_decode($meta['source_json'],true);
		  $print_meta = []; 
		  $print['thumb']  = 'thumb.php?src='.$source_meta['collection']['zong'].'/thumb/'.$source_meta['collection']['store_no'].'/'.($source_meta['collection']['cover_page'] ? $source_meta['collection']['cover_page']:$source_meta['collection']['store_no'].'-002.jpg');
		  $print['header'] = $source_meta['collection']['store_id'].' / '.$source_meta['collection']['title'];
		  $print['fields'] = [];
		  foreach($data_export_package['pfields'] as $pfield){
            if(!$fields_config['volume'][$pfield]['can_printout']) continue;
			$print['fields'][]=[
			  'f'=>$fields_config['volume'][$pfield]['descrip'],
			  'v'=>isset($source_meta['collection'][$pfield]) ? $source_meta['collection'][$pfield]:' - ',
			];
		  } 
		  $print_records[] = $print;
		}
		
		// final
		$result['data']['records']   = $print_records;
		$result['data']['rowseach']  = intval($data_export_package['rowseach']) ? intval($data_export_package['rowseach']) : 8;
		$result['data']['hasthumb']  = intval($data_export_package['hasthumb']) ? 1  : 0;
		$result['data']['count']     = count($print_records);
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	 
	//-- Admin Folder Create New Folder 
	// [input] : FolderId     :  folder id ;
	// [input] : FolderQueue  :  $_SESSION[][]
	public function ADMeta_Folder_Switch($FolderId='', $FolderQueue=[]){
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		// 確認資料夾是否存在
		if(!isset($FolderQueue[$FolderId])){
		  throw new Exception('_META_FOLDER_CONFIG_UNDEFIND');		  	
		}
		
		$FolderQueue['_focus'] = $FolderId;
		
		// final
		$result['action'] = true;
	    $result['session']['_ADMETA_FOLDERS'] = $FolderQueue;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Folder Create New Folder 
	// [input] : FolderPackageEncode  :  base encode array [ name=>,records=>[] ]  ;
	// [input] : FolderQueue          :  $_SESSION[][]
	public function ADMeta_Folder_Initial($FolderPackageEncode='', $FolderQueue=[]){
	  
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $editor_config_array=[];
	  $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
	  
	  if(file_exists($editor_config_file)){
		$editor_config_array = json_decode(file_get_contents($editor_config_file),true);  
	  }
	  
      if(!isset($editor_config_array['folders'])){	  
		$editor_config_array['folders'] = [
		  'myfolder'=>[
		    'name'=>'我的工作區',
			'timeupdate'=>'',
			'records'=>[],
			'remark'=>''
		  ]
		];
	  }
	  
	  try{  
		
		$data_folder_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($FolderPackageEncode))),true);  
		
		$folder_name = trim($data_folder_package['name']);
		$folder_save = isset($data_folder_package['records']) ? $data_folder_package['records'] : [];
		
		if(!trim($folder_name)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 重複資料夾偵測
		$duplicate_check = false;
		foreach($editor_config_array['folders'] as $folder){
		  if($folder['name'] == $folder_name){
			$duplicate_check = true;  
		  }
		}
		if($duplicate_check){
		  throw new Exception('_META_FOLDER_CREATE_DUPLICATE');			
		}
		
		$ticket = time();
		
		// initial user folders
		$editor_config_array['folders'][$ticket] = [
		  'name'=>$folder_name,
		  'timecreate'=>date('Y-m-d H:i:s'),
		  'timeupdate'=>date('Y-m-d H:i:s'),
		  'records'=>[],
		  'remark'=>''
		];
		
		// check folder elements
		if(is_array($folder_save) && count($folder_save)){
		  $editor_config_array['folders'][$ticket]['records'] = array_filter(array_unique($folder_save));     
		}
		
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		$FolderQueue[$ticket] = [];
		
		// final
		$result['action'] = true;
		$result['data']['ticket']  = $ticket ;
		$result['data']['name']    = $folder_name ;
		$result['data']['records'] = $folder_save ;
		$result['data']['remark']  = '';
		$result['session']['_ADMETA_FOLDERS'] = $FolderQueue;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Folder Delete Saved Folder
	// [input] : FolderPackageEncode  :  base encode array [ ticket=> '', name=>,records=>[] ]  ;
	// [input] : FolderQueue          :  $_SESSION[][]
	public function ADMeta_Folder_Delete($FolderPackageEncode='',$FolderQueue=[]){
      
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		$editor_config_array=[];
	    $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		if(!file_exists($editor_config_file)){
	      throw new Exception('_META_FOLDER_CONFIG_UNDEFIND');		
	    }
		
		$editor_config_array = json_decode(file_get_contents($editor_config_file),true); 
		
		$data_folder_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($FolderPackageEncode))),true);  
		$folder_ticket = isset($data_folder_package['ticket']) ? trim($data_folder_package['ticket']) : '';
		
		if(!$folder_ticket ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		if(!isset($editor_config_array['folders'][$folder_ticket])){
		  throw new Exception('_META_FOLDER_TARGET_UNDEFIND');			
		}
		
		if($folder_ticket =='myfolder'){
		  $editor_config_array['folders'][$folder_ticket]['records'] = [];	
		  $folder_status = 'clean';
		}else{
		  $folder_status = 'remove';
		  unset($editor_config_array['folders'][$folder_ticket]);	
		}
		
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		if(isset($FolderQueue[$folder_ticket])){
		  unset($FolderQueue[$folder_ticket]);	
		}
		$FolderQueue['_focus'] = 'search';
		
		// final
		$result['action'] = true;
		$result['data']['ticket']    = $folder_ticket ;
		$result['data']['status']    = $folder_status ;
		
		$result['session']['_ADMETA_FOLDERS'] = $FolderQueue;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Folder Add Data to Folder
	// [input] : FolderPackageEncode  :  base encode array [ name=>,records=>[] ]  ;
	public function ADMeta_Folder_DataAdd($FolderPackageEncode='',$FolderQueue=[]){
		
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $editor_config_array=[];
	  $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
	  
	  if(file_exists($editor_config_file)){
		$editor_config_array = json_decode(file_get_contents($editor_config_file),true);  
	  }
	  
      if(!isset($editor_config_array['folders'])){	  
		$editor_config_array['folders'] = [
		  'myfolder'=>[
		    'name'=>'我的工作區',
			'timeupdate'=>'',
			'records'=>[],
			'remark'=>''
		  ]
		];
	  }
	  
	  try{  
		
		$data_folder_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($FolderPackageEncode))),true);  
		
		$folder_id   = trim($data_folder_package['ticket']);
		$folder_name = trim($data_folder_package['name']);
		$folder_save = isset($data_folder_package['records']) ? array_unique(array_filter($data_folder_package['records'])) : [];
		
		if(!isset($editor_config_array['folders'][$folder_id])){
		  throw new Exception('_META_FOLDER_TARGET_UNDEFIND');	
		}
		
		if(!count($folder_save)){
		  throw new Exception('_META_FOLDER_SELECT_ISEMPTY');  	
		}
		
		$folder_records = array_merge($editor_config_array['folders'][$folder_id]['records'],$folder_save);
		$folder_records = array_unique($folder_records); 
		$editor_config_array['folders'][$folder_id]['records'] = $folder_records ;
		
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		
		$FolderQueue[$folder_id] =  $folder_records;
		
		// final
		$result['action'] = true;
		$result['data']['ticket']  = $folder_id ;
		$result['data']['name']    = $folder_name ;
		$result['data']['newadd']  = $folder_save ;
		$result['data']['count']   = count($folder_records);
		
		$result['session']['_ADMETA_FOLDERS'] = $FolderQueue;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Folder Delete Data From Folder
	// [input] : FolderPackageEncode  :  base encode array [ name=>,records=>[] ]  ;
	public function ADMeta_Folder_Remove($FolderPackageEncode='',$FolderQueue=[]){
		
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $editor_config_array=[];
	  $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
	  
	  if(file_exists($editor_config_file)){
		$editor_config_array = json_decode(file_get_contents($editor_config_file),true);  
	  }
	  
      if(!isset($editor_config_array['folders'])){	  
		$editor_config_array['folders'] = [
		  'myfolder'=>[
		    'name'=>'我的工作區',
			'timeupdate'=>'',
			'records'=>[],
			'remark'=>''
		  ]
		];
	  }
	  
	  try{  
		
		$data_folder_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($FolderPackageEncode))),true);  
		
		$folder_id   = trim($data_folder_package['ticket']);
		$folder_name = trim($data_folder_package['name']);
		$folder_out = isset($data_folder_package['records']) ? array_unique(array_filter($data_folder_package['records'])) : [];
		
		if(!isset($editor_config_array['folders'][$folder_id])){
		  throw new Exception('_META_FOLDER_TARGET_UNDEFIND');	
		}
		
		if(!count($folder_out)){
		  throw new Exception('_META_FOLDER_SELECT_ISEMPTY');  	
		}
		
		//目前資料夾
		$folder_elements = $editor_config_array['folders'][$folder_id]['records'];
		foreach($folder_out as $sid){
		  $key = array_search($sid,$folder_elements);	
		  if( $key!==false ){
			unset($folder_elements[$key]);
		  }
		}
		
		// 更新暫存
		$editor_config_array['folders'][$folder_id]['records'] = $folder_elements;
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		// 更新buffer
		$FolderQueue[$folder_id] =  $folder_elements;
		
		// final
		$result['action'] = true;
		$result['data']['ticket']  = $folder_id ;
		$result['data']['name']    = $folder_name ;
		$result['data']['count']   = count($folder_elements);
		
		$result['session']['_ADMETA_FOLDERS'] = $FolderQueue;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Folder Save Folder Remark
	// [input] : FolderPackageEncode  :  base encode array [ name=>,records=>[] ]  ;
	public function ADMeta_Folder_Remark($FolderPackageEncode=''){
		
	  $result_key = parent::Initial_Result('folder');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $editor_config_array=[];
	  $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
	  
	  if(file_exists($editor_config_file)){
		$editor_config_array = json_decode(file_get_contents($editor_config_file),true);  
	  }
	  
      if(!isset($editor_config_array['folders'])){	  
		$editor_config_array['folders'] = [
		  'myfolder'=>[
		    'name'=>'我的工作區',
			'timeupdate'=>'',
			'records'=>[],
			'remark'=>'',
		  ]
		];
	  }
	  
	  try{  
		
		$data_folder_package = json_decode(base64_decode(str_replace('*','/',rawurldecode($FolderPackageEncode))),true);  
		
		$folder_id   = trim($data_folder_package['ticket']);
		$folder_note = trim($data_folder_package['remark']);
		
		if(!isset($editor_config_array['folders'][$folder_id])){
		  throw new Exception('_META_FOLDER_TARGET_UNDEFIND');	
		}
		
		$editor_config_array['folders'][$folder_id]['remark'] = $folder_note ;
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta New Volume
	// [input] : ;
	public function ADMeta_Volume_Create( ){
		
	  $result_key = parent::Initial_Result('volume');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 新增source_digiarchive
		$DB_NEW	= $this->DBLink->prepare(SQL_AdMeta::INSERT_VOLUME_DATA());
		// 新增件資料
		$DB_NEW->bindValue(':class'  , 'relic');
		$DB_NEW->bindValue(':zong'   , '001');
		$DB_NEW->bindValue(':fonds'  , '館藏文物');
		$DB_NEW->bindValue(':user' , $this->USER->UserID);
		if( !$DB_NEW->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');
		}
		
		$new_sno       = $this->DBLink->lastInsertId('source_digiarchive');
		$new_store_no  = 'BM'.str_pad($new_sno,8,'0',STR_PAD_LEFT);
		
		$source_array = ['collection'=>[],'element'=>[]];
		
		// 新增meta資料
		$DB_NEWMETA = $this->DBLink->prepare(SQL_AdMeta::INSERT_NEW_METADATA());	
	    
		// 註冊meta資料
		$DB_NEWMETA->bindValue(':class'		,'relic');
		$DB_NEWMETA->bindValue(':zong'		, '001');
		$DB_NEWMETA->bindValue(':data_type'	,'collection');
		$DB_NEWMETA->bindValue(':collection'	,$new_store_no);
		$DB_NEWMETA->bindValue(':identifier'	,'');
		$DB_NEWMETA->bindValue(':applyindex'	,$new_store_no.'-000');
		$DB_NEWMETA->bindValue(':source_json'	,json_encode($source_array,JSON_UNESCAPED_UNICODE));
		$DB_NEWMETA->bindValue(':search_json'	,'[]');
		$DB_NEWMETA->bindValue(':dobj_json'		,json_encode(array('dopath'=>'001'.'/','folder'=>$new_store_no)));
		$DB_NEWMETA->bindValue(':refer_json'	,json_encode(array()));
		$DB_NEWMETA->bindValue(':page_count'	,0);
		$DB_NEWMETA->bindValue(':lockmode'		,'');
		$DB_NEWMETA->bindValue(':auditint'		,0);
		$DB_NEWMETA->bindValue(':checked'		,1);
		$DB_NEWMETA->bindValue(':digited'		,1);
		$DB_NEWMETA->bindValue(':open'			,0);
		$DB_NEWMETA->bindValue(':view'			,'');
		if(!$DB_NEWMETA->execute()){
		  throw new Exception('新增資料失敗'); 	
		}
		$system_id = $this->DBLink->lastInsertId('metadata');
		
		// 執行更新
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array('store_no'),'source_digiarchive','sno'));
		$DB_UPD->bindValue(':id'        , $new_sno);
		$DB_UPD->bindValue(':store_no'  , $new_store_no );
		
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		
		// 執行mdlogs
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , '001');
		$DB_LOGS->bindValue(':sysid' 	  , $system_id);
		$DB_LOGS->bindValue(':identifier' , $new_store_no);
		$DB_LOGS->bindValue(':method'     , 'CREATE');
		$DB_LOGS->bindValue(':source' 	  , '[]');
		$DB_LOGS->bindValue(':update' 	  , '[]');
		$DB_LOGS->bindValue(':user' 	  , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		
		// final
		$result['data']['source_id']   = $new_store_no;
		$result['data']['system_id']   = $system_id;
		$result['data']['store_no']    = $new_store_no;
		
		$result['action'] = true;
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta Delete Volume
	// [input] : ;
	public function ADMeta_Volume_Delete( $DataNo ){
		
	  $result_key = parent::Initial_Result('volume');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\d]+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 目標系統資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SEARCH_META());
		$DB_GET->bindParam(':system_id'   , $DataNo );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		// 取得卷所屬單件列表
		$elements = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_ELEMENTS('source_digielement'));
		$DB_GET->bindParam(':collection' , $meta['collection']);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  // 刪除所有件資料
		  self::ADBuilt_Dele_Element_Data($meta['collection'],$tmp['store_no']); 
		}
		
		
		// 更新卷資料
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array('_metakeep'),'source_digiarchive','store_no'));
		$DB_UPD->bindValue(':id'        , $meta['collection']);
		$DB_UPD->bindValue(':_metakeep'  , 0 );
		
		// 執行DB record 刪除標記
		$DB_DEL= $this->DBLink->prepare(SQL_AdMeta::MARK_DB_DELETE('source_digiarchive'));
		$DB_DEL->bindValue(':id'    , $meta['collection']);
		$DB_DEL->bindValue(':user'  , $this->USER->UserID);
	    if( !$DB_DEL->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	 
		}
		
		$active = self::ADMeta_Process_Meta_Update($DataNo);
		
		if(!$active['action']){
		  // 索引更新不成功
		  $DB_RESTORE= $this->DBLink->prepare(SQL_AdMeta::UNDO_DB_DELETE('source_digiarchive'));
		  $DB_DEL->bindValue(':id'    , $ElementId);
		  $DB_DEL->execute();
		  throw new Exception();
		}
		
		// 執行mdlogs
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['collection']);
		$DB_LOGS->bindValue(':method'     , 'DELETE');
		$DB_LOGS->bindValue(':source' 	  , '[]');
		$DB_LOGS->bindValue(':update' 	  , '[]');
		$DB_LOGS->bindValue(':user' 	  , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	/***== [ Built Function Set : 編輯頁面模組 ] ==***/
	/*------------------------------------------------------------------------------------------------------------------------*/
	
	//-- Admin Meta : Get Meta Resouce
	// 目標：取得建檔資料環境資源，全宗資料、卷級資料、單件列表、數位檔案、參考資源
	// [input] : DataNo  	:  \d+;    系統序號
	public function ADMeta_Get_Editor_Resouse($DataNo=0,$Mode='edit'){
      
	  $result_key = parent::Initial_Result('resouse');
	  $result  = &$this->ModelResult[$result_key];
	  $lib_imagemagic =  _SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.3-7-portable-Q16-x64/convert.exe ';
      $lib_ffmpeg 	  =  _SYSTEM_ROOT_PATH.'mvc/lib/ffmpeg-20161122-d316b21-win64-static/bin/ffmpeg.exe ';
	  $lib_ffprobe    =  _SYSTEM_ROOT_PATH.'mvc/lib/ffmpeg-20161122-d316b21-win64-static/bin/ffprobe.exe ';
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d]+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 目標系統資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SEARCH_META());
		$DB_GET->bindParam(':system_id'   , $DataNo );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$meta['data_type'];
		$meta['zong'];
		$meta['collection'];
		$meta['identifier'];
		$meta['dobj_json'];
		$meta['refer_json'];
		
		// 設定對應表單
		switch($meta['zong']){
		  default:
		    $db_volume_table  = 'source_digiarchive'; 
		    $db_element_table = 'source_digielement'; 
		    break;	
		}
		
		// 取得卷資料
		$collection = array();
		$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_META($db_volume_table,'store_no'));
		$DB_SOURCE->bindParam(':id'   , $meta['collection'] );	
		if( !$DB_SOURCE->execute() || !$source = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		// 轉置欄位設定
		foreach($source as $sfield=>$svalue){
		  $collection['META-V-'.$sfield] = $svalue;	
		}
		
		
		
		// 取得卷所屬單件列表
		$elements = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_ELEMENTS($db_element_table));
		$DB_GET->bindParam(':collection' , $meta['collection']);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $item = [];
		  foreach($tmp as $ef=>$ev){
			$item['META-E-'.$ef] = $ev;  
		  }
		  $item['META-E-file_store_id'] = $source['store_id'].'-'.$tmp['file_no']; 
		  $elements[$tmp['store_no']] = $item;	
		}
		
		
		
		// 取得數位檔案設定
		$digital_object_path = _SYSTEM_DIGITAL_FILE_PATH;
		$file_list = array();
		$dobj_list = array();
		$meta_dobj = json_decode($meta['dobj_json'],true);
		
		if(isset($meta_dobj['dopath'])){
		  
          $digital_object_path.= $meta_dobj['dopath'];
		  $digital_object_path.= 'browse/';
		  $digital_object_path.= $meta['collection'].'/';
		
		  // 取得數位物件設定
		  $doprofileread = '';
		  $doprofilepath =  _SYSTEM_DIGITAL_FILE_PATH.$meta_dobj['dopath'].'profile/'.$meta['collection'].'.conf'; 
		  if(is_file( $doprofilepath )){
			$doprofileread = file_get_contents($doprofilepath);	  
		  }
		  
		  $dobj_profile = json_decode($doprofileread,true);
		  
		  // 若無數位檔案規劃設定
		  if( !$dobj_profile || ( !isset($dobj_profile['items']) || !count($dobj_profile['items']))){
			
			// 掃描實體檔案 
            if(is_dir($digital_object_path)){
              $file_list = array_slice(scandir($digital_object_path),2);			
		    }
		  
		    $dobj_list = $file_list;	
            
			// 建立數位物件設定檔
			$do_conf = array('store'=>$digital_object_path  , 'saved'=>date('Y-m-d H:i:s') , 'items'=>[] ,'dotype'=>['文物卡','整理照','出版照','相片','底片','翻拍','其他']);
			
			foreach($dobj_list as $i=>$do){
			  if(!file_exists($digital_object_path.$do)) continue;
			  
			  $file_type = strtolower(pathinfo($digital_object_path.$do,PATHINFO_EXTENSION));
			  
			  switch($file_type){
				case 'jpg': case 'jpeg': case 'png': case 'gif':  
				  list($imgw, $imgh) = getimagesize($digital_object_path.$do);
			      $do_conf['items'][] = [
			        'file' => $do,
				    'width'=> $imgw,
			        'height'=> $imgh,
				    'size'=> filesize($digital_object_path.$do),
					'dotype'=>'整理照'
			      ]; 
				  break;
				case 'mp3':
				  
				  $fconfig = [
				    'file'   => $do,
				    'width'  => 150,
				    'height' => 150,
				    'length' => 0,
					'duration'=> 0,
				    'thumb'  => $do.'.png',
				    'order'  => ++$i,
				    'update' => date('Y-m-d H:i:s'),
				    'editor' => 'RCDH'
				  ];  
					
				  $second=[];
				  exec($lib_ffprobe .'-v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '.$digital_object_path.$do ,$second); 
				  $fconfig['length'] = intval(ceil($second[0]));
				  $fconfig['duration'] = str_pad(intval($fconfig['length']/3600),2,'0',STR_PAD_LEFT).':'.str_pad(intval(intval($fconfig['length']%3600)/60),2,'0',STR_PAD_LEFT).':'.str_pad(intval($fconfig['length']%60),2,'0',STR_PAD_LEFT);
				  $do_conf['items'][] = $fconfig;  
				  break;
				  
				case 'mp4':
				  
				  $fconfig = [
				    'file'   => $do,
					'width'  => 0,
					'height' => 0,
					'length' => 0,
					'duration'=> 0,
					'thumb'  => $do.'.jpg',
					'order'  => ++$i,
					'update' => date('Y-m-d H:i:s'),
					'editor' => 'RCDH'
				  ];
			      
				  $result=[];
				  exec($lib_ffprobe .'-v error -of flat=s=_ -select_streams v:0 -show_entries stream=height,width '.$digital_object_path.$do ,$result); 
				  foreach($result as $attr){
					list($a,$v) = explode('=',$attr);	
					if(preg_match('/width/',$a)){
					  $fconfig['width'] = intval($v);  
					}else{
					  $fconfig['height'] = intval($v);    
					}
				  }  
					
				  $second=[];
				  exec($lib_ffprobe .'-v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '.$digital_object_path.$do ,$second); 
				  //echo "\n".$dopath.' : '. $second[0];
				  $fconfig['length'] = intval(ceil($second[0]));
				  $fconfig['duration'] = str_pad(intval($fconfig['length']/3600),2,'0',STR_PAD_LEFT).':'.str_pad(intval(intval($fconfig['length']%3600)/60),2,'0',STR_PAD_LEFT).':'.str_pad(intval($fconfig['length']%60),2,'0',STR_PAD_LEFT);
				  $do_conf['items'][] = $fconfig;
				  
				  break;
				
				default: break;
			  }
			}
			
			// 取得數位物件設定
		    $doprofilepath =  _SYSTEM_DIGITAL_FILE_PATH.$meta_dobj['dopath'].'profile/'.$meta['collection'].'.conf'; 
		    file_put_contents($doprofilepath,json_encode($do_conf));
			
			$dobj_config = $do_conf['items'];
		  }else{
			
			$do_conf     = $dobj_profile; 
			$dobj_config = $dobj_profile['items'];  
		  
		  }
		  
		  $dobj_config = $dobj_config ? $dobj_config : $dobj_list;
		  $dobj_resave = [];
		  foreach($dobj_config as $do){
            $dofolder = isset($do['dotype']) ? $do['dotype'] : '';
            if(!isset($dobj_resave[$dofolder])) $dobj_resave[$dofolder] = [];
            $dobj_resave[$dofolder][] = $do;
		  }
		  
		  $result['data']['dobj_config']['root']   = $meta_dobj['dopath'];
		  $result['data']['dobj_config']['files']  = $dobj_config;
		  $result['data']['dobj_config']['folders']= $dobj_resave;
		  $result['data']['dobj_config']['dotypes']= $do_conf['dotype'];
		  
		}else{
		  // 尚未建構數位檔案
		  $result['data']['dobj_config']['root']   = $meta['zong'].'/';
		  $result['data']['dobj_config']['files']  = [];	
		  $result['data']['dobj_config']['folders']= [];
		  $result['data']['dobj_config']['dotypes']= ['文物卡','整理照','出版照','相片','底片','翻拍','其他'];
		}
		
		$result['data']['dobj_config']['folder'] = $meta['collection'];
		 
		
		// 取得介面設定
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
		
		
		// recount 
		if(!$collection['META-V-count_dofiles'] || !$collection['META-V-count_element']){
			if(!$collection['META-V-count_dofiles']){	// 計算數量總數
			  $DB_RECOUNT	= $this->DBLink->prepare(SQL_AdMeta::UPDATE_VOLUME_DOFILE_COUNT());
			  $DB_RECOUNT->bindValue(':volume_id',$meta['collection']);
			  $DB_RECOUNT->bindValue(':docount',count($dobj_config));
			  $DB_RECOUNT->execute();	
			}
			
			if(!$collection['META-V-count_element']){	// 計算檔案總數
			  $DB_RECOUNT	= $this->DBLink->prepare(SQL_AdMeta::UPDATE_VOLUME_ELEMENT_COUNT());
			  $DB_RECOUNT->bindValue(':volume_id',$meta['collection']);
			  $DB_RECOUNT->execute();
			}
			
			// 執行更新
			$active = self::ADMeta_Process_Meta_Update($meta['system_id']);
		}
		
		
		// final
		$result['action'] = true;
		$result['data']['active_systemid'] = $DataNo;
		$result['data']['meta_collection'] = $collection;
		$result['data']['meta_elements']   = $elements ;
		$result['data']['editor_config']   = $editor_config_array ;
		
		
		$result['data']['form_mode']   = $meta['zong'];
		$result['data']['edit_mode']   = $Mode;
		
		 
		
		//$result['session']['METACOLLECTION']  = json_decode($collection_meta,true);
		//$result['session']['DOBJCOLLECTION']  = $collection_dobj;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Meta : Get DOBJ profile
	// [input] : DataType  : ARCHIVE....
	// [input] : DataFolder: collection id // file folder 
	public function ADMeta_Read_Dobj_Profile( $DataType='' , $DataFolder=''  ){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// final 
		$result['data']   = count($dobj_profile['items']) ? $dobj_profile['items'] : [] ;
    	$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Built : Temp Editor Modify 
	// [input] : KeeperMethod	:  push 暫存 / clean 清空
	// [input] : EditorIndex	:  卷號 + 件號? 
	// [input] : PassEncodeString	:  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位
	public function ADMeta_Editor_Keeper( $KeeperMethod='', $EditorIndex='' , $PassEncodeString=''){
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($PassEncodeString))),true); 
	  
	  try{   
	    
		// 取得介面設定
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
		if(!isset($editor_config_array['editkeep'])) $editor_config_array['editkeep'] = [];
		
		$editor_index  = $EditorIndex;
		
		switch($KeeperMethod){
		  case 'push':	
			$data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($PassEncodeString))),true); 
		    $editor_config_array['editkeep'][$editor_index] = [
		      'time'=>date('Y-m-d H:i:s'),
		      'user'=>$this->USER->UserID,
		      'keep'=>$data_modify
		    ];
			break;
		  
		  case 'clean':
		    unset($editor_config_array['editkeep'][$editor_index]);
			break;
		}
		file_put_contents($editor_config_file,json_encode($editor_config_array));   
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Built : Get Volume Source Metadata
	// [input] : VolumeId  :   // source_digiarchive.store_no
	public function ADBuilt_Get_Volume_Meta($VolumeId=''){
		
	  $result_key = parent::Initial_Result('meta');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 取得編輯資料
		$source_meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digiarchive','collection'));
		$DB_GET->bindParam(':id'   , $VolumeId );	
		if( !$DB_GET->execute() || !$source_meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$form_meta = [];
		$form_prehead = 'META-V-';
		if(count($source_meta)){
		  foreach($source_meta as $mfield => $mvalue){
			$form_meta[$form_prehead.$mfield] = $mvalue;  
		  }
		}
		
		// 取得卷所屬單件列表
		$elements = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_ELEMENTS('source_digielement'));
		$DB_GET->bindParam(':collection' , $VolumeId);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $item = [];
		  foreach($tmp as $ef=>$ev){
			$item['META-E-'.$ef] = $ev;  
		  }
		  $item['META-E-file_store_id'] = $source_meta['store_id'].'-'.$tmp['file_no']; 
		  $elements[$tmp['store_no']] = $item;	
		}
		
		
		$form_meta['META-V-_NumOfItem'] = count($elements);
		
		
		// 取得卷所屬研究資訊
		$research = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_LINK_META('source_research'));
		$DB_GET->bindParam(':collection' , $VolumeId);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $item = [];
		  foreach($tmp as $ef=>$ev){
			$item['META-R-'.$ef] = $ev;  
		  }
		  $research[$tmp['srno']] = $item;	
		}
		
		 
		// 取得卷所屬異動資訊
		$movement = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_LINK_META('source_movement').' ORDER BY smno DESC');
		$DB_GET->bindParam(':collection' , $VolumeId);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $item = [];
		  foreach($tmp as $ef=>$ev){
			$item['META-M-'.$ef] = $ev;  
		  }
		  $movement[$tmp['smno']] = $item;	
		}
		
		
		// 取得卷所屬展覽資訊
		$display=[];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_LINK_META('source_display').' ORDER BY sdno DESC');
		$DB_GET->bindParam(':collection' , $VolumeId);	
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $item = [];
		  foreach($tmp as $ef=>$ev){
			$item['META-D-'.$ef] = $ev;  
		  }
		  $display[$tmp['sdno']] = $item;	
		}
		
		
		//讀取暫存器
		$buffer = [];
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
	    if(isset($editor_config_array['editkeep'][$VolumeId.'@'])){
		  $buffer = $editor_config_array['editkeep'][$VolumeId.'@']['keep'];  
		}
		
		// final
		$result['action'] = true;
		$result['data']['source'] = $form_meta;
		$result['data']['record'] = $elements;
		$result['data']['buffer'] = $buffer;
		
	    $result['data']['research']   = $research ;
		$result['data']['movement']   = $movement ;
		$result['data']['display']    = $display ;
		
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	//-- Admin Built : Save Volume Meta
	// [input] : SourceStoreNo  	:  store_no
	// [input] : PassEncodeString	:  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	public function ADBuilt_Save_Volume_Meta( $SourceStoreNo='' , $PassEncodeString=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($PassEncodeString))),true); 
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$SourceStoreNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查修改欄位資料
		if(!count($data_modify)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 取得資料表欄位檢測
		$set_field_checker = [];
		$DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_TABLE_FORMAT());
		$DB_GET->bindValue(':dbtable' , 'source_digiarchive');
		if($DB_GET->execute()){
		  while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			$set_field_checker[$field['dbcolumn']] = $field;
		  }
		}
		
		// 取得卷資料
		$volume = array();
		$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digiarchive','collection'));
		$DB_SOURCE->bindParam(':id'   , $SourceStoreNo );	
		if( !$DB_SOURCE->execute() || !$volume = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		// 更新資料前處理
		$meta_ethnic = [];
		if(isset($data_modify['META-V-ethnic_1'])&&$data_modify['META-V-ethnic_1']){
    	  if( $data_modify['META-V-ethnic_1']=='其他' && isset($data_modify['META-V-ethnic_2']) && $data_modify['META-V-ethnic_2'] ){
			$meta_ethnic[]=$data_modify['META-V-ethnic_2'];    
		  }else if($data_modify['META-V-ethnic_1']=='原住民族'){
			$meta_ethnic[]=$data_modify['META-V-ethnic_1'];  
			if(isset($data_modify['META-V-ethnic_2']) && $data_modify['META-V-ethnic_2']=='其他' &&  isset($data_modify['META-V-ethnic_3'])){
			  $meta_ethnic[]=$data_modify['META-V-ethnic_3'];  	
			}else{
			  $meta_ethnic[]=$data_modify['META-V-ethnic_2'];  		
			}
		  }else{
			$meta_ethnic[]=$data_modify['META-V-ethnic_1'];   
		  }
		}
		$data_modify['META-V-ethnic'] = count($meta_ethnic) ? join('/',$meta_ethnic) : '';
		
		// 檢查更新欄位是否合法
		$source_update = [];
		$source_origin = [];
		
		$modify_detect = [];
		
		foreach($data_modify as $edit_filed=>$edit_value){
		  $db_field = str_replace('META-V-','',$edit_filed);  
		  $update_value = is_array($edit_value) ? join(',',$edit_value) : $edit_value;
		  
		  if(!isset($volume[$db_field]) ) continue;  
		  if( $volume[$db_field] == $update_value) continue;
	      
		  if($set_field_checker[$db_field]['module'] == 'R' && !preg_match( $set_field_checker[$db_field]['pattern'],$update_value) ){
			$modify_detect[$edit_filed] = $set_field_checker[$db_field]['descrip'].'格式錯誤';  
		  }else if($set_field_checker[$db_field]['module'] == 'V' && !$set_field_checker[$db_field]['pattern']!=$update_value){
			$modify_detect[$edit_filed] = $set_field_checker[$db_field]['descrip'].'內容錯誤'; 
		  }
		  
		  $source_update[$db_field] = $update_value;	
		  $source_origin[$db_field] = $volume[$db_field];
		  
		  //新增選項條件
	      if(isset($set_field_checker[$db_field]) &&  ($set_field_checker[$db_field]['module']=='S' || $set_field_checker[$db_field]['module']=='E') ){
			
			$field_value_module= $set_field_checker[$db_field]['module']=='S' ? 'set' : 'enum';
			$field_value_check = explode(';',$set_field_checker[$db_field]['pattern']);
			$field_value_setad = $field_value_check;
			
			if(is_array($edit_value)){
			  foreach($edit_value as $ev){
                if(in_array($ev,$field_value_check)) continue;			  
			    $field_value_setad[] = $ev;
			  }
			}else{
			  if(!in_array($edit_value,$field_value_check)){
			    $field_value_setad[] = $edit_value; 
			  }	
			}
			
			// 更新source資料表
			if(count($field_value_setad) != count($field_value_check)){
			  
			  $DB_UPDB = $this->DBLink->prepare( SQL_AdMeta::UPDATE_TABLE_FORMAT());	
			  $DB_UPDB->bindValue(':pattern',join(';',$field_value_setad));
			  $DB_UPDB->bindValue(':dbtable','source_digiarchive');
			  $DB_UPDB->bindValue(':dbcolumn',$db_field);
			  $DB_UPDB->execute();
			  
			  $DB_UPDB = $this->DBLink->prepare( SQL_AdMeta::SET_DBTABLE_SETFIELD('source_digiarchive',$db_field,$field_value_module,$field_value_setad));	
			  $DB_UPDB->execute();
			}
		  }
		
		}
		
		if(count($modify_detect)){
		  $result['data'] = $modify_detect;	
		  throw new Exception('_SYSTEM_ERROR_FILE_CHECK_FAIL');	 
		}
		
		
			
		$store_id_set  = [];  // 典藏號聚合集
		$store_id_set[] = isset($source_update['store_year']) ? $source_update['store_year'] : $volume['store_year'];
		$store_id_set[] = isset($source_update['store_type']) ? $source_update['store_type'] : $volume['store_type'];
		$store_id_set[] = isset($source_update['store_no1'])  ? $source_update['store_no1']  : $volume['store_no1'];
		$store_id_set[] = isset($source_update['store_no2'])  ? $source_update['store_no2']  : $volume['store_no2'];
		$store_id_set[] = isset($source_update['store_no3'])  ? $source_update['store_no3']  : $volume['store_no3'];
		
		if(count($source_update)){
			// 補充系統欄位
			$source_update['store_id']    = join('-',array_filter($store_id_set));
			$source_update['_userupdate'] = $this->USER->UserID;
			
			// 執行原始資料更新
			$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($source_update),'source_digiarchive'));
			$DB_SAVE->bindValue(':id' , $SourceStoreNo);
			foreach($source_update as $uf=>$uv){
			  $DB_SAVE->bindValue(':'.$uf , $uv);
			}
			if( !$DB_SAVE->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
			}
		}
		
		
		
		// 執行詮釋資料更新
		$meta_update['_index']=0;
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_METADATA_DATA(array_keys($meta_update)));
		$DB_SAVE->bindValue(':sid'   , $volume['system_id']);
		foreach($meta_update as $uf=>$uv){
	      $DB_SAVE->bindValue(':'.$uf , $uv);
		}
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行mdlogs
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $volume['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $volume['system_id']);
		$DB_LOGS->bindValue(':identifier' , $volume['store_no']);
		$DB_LOGS->bindValue(':method'    , 'UPDATE');
		$DB_LOGS->bindValue(':source' 	  , json_encode($source_origin));
		$DB_LOGS->bindValue(':update' 	  , json_encode($source_update));
		$DB_LOGS->bindValue(':user' , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		
		 
		// 清空keeper
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
	    if(isset($editor_config_array['editkeep'][$volume['store_no'].'@'])){
		  unset($editor_config_array['editkeep'][$volume['store_no'].'@']);
		  file_put_contents($editor_config_file,json_encode($editor_config_array));
		}
		
		// final 
		$result['data']   = $volume['system_id'];
    	$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
   
	
	//-- Admin Built : Get Target Element
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : ElementId :   // source_digielement.store_no
	public function ADBuilt_Get_Element_Data($VolumeId='',$ElementId=''){
		
	  $result_key = parent::Initial_Result('meta');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$ElementId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得卷資料
		$volume_meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digiarchive','collection'));
		$DB_GET->bindParam(':id'   , $VolumeId );	
		if( !$DB_GET->execute() || !$volume_meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		// 取得編輯資料
		$source_meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digielement','identifier'));
		$DB_GET->bindParam(':id'   , $ElementId );	
		if( !$DB_GET->execute() || !$source_meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$form_meta = [];
		$form_prehead = 'META-E-';
		if(count($source_meta)){
		  foreach($source_meta as $mfield => $mvalue){
			$form_meta[$form_prehead.$mfield] = $mvalue;  
		  }
		}
		$form_meta[$form_prehead.'file_store_id'] = $volume_meta['store_id'].'-'.$source_meta['file_no'];
		
		//讀取暫存器
		$buffer = [];
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
	    if(isset($editor_config_array['editkeep'][$VolumeId.'@'.$ElementId])){
		  $buffer = $editor_config_array['editkeep'][$VolumeId.'@'.$ElementId]['keep'];  
		}
		
		// final
		$result['action'] = true;
		$result['data']['source'] = $form_meta;
		$result['data']['buffer'] = $buffer;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	//-- Admin Built : Element Save / Newa
	// [input] : VolumeId	:  volumn source store_no
	// [input] : MetaNo		:  metadata.system_id / _addnew
	// [input] : MetaEncode	:  rcdh encode string 
	public function ADBuilt_Save_Element_Data( $VolumeId='', $SourceStoreNo=0, $MetaEncode=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result 	  = &$this->ModelResult[$result_key];
	  
	  $data_modify= is_array($MetaEncode) ? $MetaEncode : json_decode(base64_decode(str_replace('*','/',rawurldecode($MetaEncode))),true); 
	  
	  try{  
		
		// 欄位資料
		if(!is_array($data_modify) || count($data_modify) ){
          //throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$element_store_no = '';
		if( $SourceStoreNo == '_addnew' ){
			
		  // 檢查序號
	      if(!preg_match('/^[\w\d]+$/',$VolumeId)){
		    throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		  }	
			
		  $active = self::ADBuilt_Newa_Element_Data($VolumeId);
		  if(!$active['action']) throw new Exception('');	  // 新增失敗
		  $element_store_no = $active['data']['store_no']; // source seno
		
		}else{
		  $element_store_no =  $SourceStoreNo;
		}
		
		
		// 取得資料表欄位檢測
		$set_field_checker = [];
		$DB_GET= $this->DBLink->prepare(SQL_AdMeta::GET_TABLE_FORMAT());
		$DB_GET->bindValue(':dbtable' , 'source_digielement');
		if($DB_GET->execute()){
		  while($field = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			$set_field_checker[$field['dbcolumn']] = $field;
		  }
		}
		
		// 取得卷資料
		$volume = array();
		$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digiarchive','collection'));
		$DB_SOURCE->bindParam(':id'   , $VolumeId );	
		if( !$DB_SOURCE->execute() || !$volume = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 取得source element
		$element_meta = [];
		$DB_GET	= $this->DBLink->prepare(SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digielement','identifier'));
		$DB_GET->bindParam(':id' , $element_store_no);
		if( !$DB_GET->execute() || !$element_meta=$DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$system_id = $element_meta['system_id'];
		$volume_id = $element_meta['collection_id'];
		
		
		// 檢查更新欄位是否合法
		$source_update = [];
		$source_origin = [];
		
		foreach($data_modify as $edit_filed=>$edit_value){
		  $db_field = preg_replace('/^META-(R|E)-/','',$edit_filed); 
		  $update_value = is_array($edit_value) ? join(',',$edit_value) : $edit_value;
		  
		  if(!isset($element_meta[$db_field]) ) continue;  
		  if( $element_meta[$db_field] == $update_value) continue;
	      
		  $source_update[$db_field] = $update_value;	
		  $source_origin[$db_field] = $element_meta[$db_field];
		  
		  //新增選項條件
	      if(isset($set_field_checker[$db_field]) &&  ($set_field_checker[$db_field]['module']=='S' || $set_field_checker[$db_field]['module']=='E') ){
			
			$field_value_module= $set_field_checker[$db_field]['module']=='S' ? 'set' : 'enum';
			$field_value_check = explode(';',$set_field_checker[$db_field]['pattern']);
			$field_value_setad = $field_value_check;
			
			if(is_array($edit_value)){
			  foreach($edit_value as $ev){
                if(in_array($ev,$field_value_check)) continue;			  
			    $field_value_setad[] = $ev;
			  }
			}else{
			  if(!in_array($edit_value,$field_value_check)){
			    $field_value_setad[] = $edit_value; 
			  }	
			}
			
			// 更新source資料表
			if(count($field_value_setad) != count($field_value_check)){
			  $DB_UPDB = $this->DBLink->prepare( SQL_AdMeta::UPDATE_TABLE_FORMAT());	
			  $DB_UPDB->bindValue(':pattern',join(';',$field_value_setad));
			  $DB_UPDB->bindValue(':dbtable','source_digielement');
			  $DB_UPDB->bindValue(':dbcolumn',$db_field);
			  $DB_UPDB->execute();
			  
			  $DB_UPDB = $this->DBLink->prepare( SQL_AdMeta::SET_DBTABLE_SETFIELD('source_digielement',$db_field,$field_value_module,$field_value_setad));	
			  $DB_UPDB->execute();
			}
		  }
		
		}
		
		
		if(count($source_update)){
			
		  // source 更新
		  $source_update['_userupdate'] = $this->USER->UserID;	
		  
		  $DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($source_update),'source_digielement'));
		  $DB_SAVE->bindValue(':id'        , $element_store_no);
		  foreach($source_update as $uf=>$uv){
		    $DB_SAVE->bindValue(':'.$uf , $uv);
		  }
		  if( !$DB_SAVE->execute()){
		    throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  
		  // meta 標記更新
		  $meta_update['_index']=0;
		  $DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_METADATA_DATA(array('_index')));
		  $DB_SAVE->bindValue(':sid'    , $system_id);
		  $DB_SAVE->bindValue(':_index' , 0);
		  if( !$DB_SAVE->execute()){
		    throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  
		  // 執行mdlogs
		  $DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		  $DB_LOGS->bindValue(':zong' 	   , '');
		  $DB_LOGS->bindValue(':identifier', $element_store_no);
		  $DB_LOGS->bindValue(':sysid' 	   , $system_id);
		  $DB_LOGS->bindValue(':method'    , 'UPDATE');
		  $DB_LOGS->bindValue(':source'    , json_encode($source_origin));
		  $DB_LOGS->bindValue(':update'    , json_encode($source_update));
		  $DB_LOGS->bindValue(':user' 	   , $this->USER->UserID);
		  $DB_LOGS->bindValue(':result'    , 1);
		  $DB_LOGS->execute();
		
		}
		
		// 清空keeper
		$editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		$editor_config_array = (file_exists($editor_config_file)) ? json_decode(file_get_contents($editor_config_file),true) : [];  
	    if(isset($editor_config_array['editkeep'][$volume_id.'@'.$element_store_no])){
		  unset($editor_config_array['editkeep'][$volume_id.'@'.$element_store_no]);
		  file_put_contents($editor_config_file,json_encode($editor_config_array));
		}
		
		// final 
		$result['data']['system_id'] = $system_id;
		$result['data']['source_id'] = $element_store_no;
		$result['data']['updated']   = $source_update;
		
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Built Create A New Element 
	// [input] : VolumeId  :  source.store_no
	// [input] : MetaCreate:  rcdh pass encode string
	
	public function ADBuilt_Newa_Element_Data($VolumeId='',$MetaPass=[] ){
	  
	  $result_key = parent::Initial_Result('newa');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $meta_newa   = is_array($MetaPass) ? $MetaPass : json_decode(base64_decode(str_replace('*','/',rawurldecode($MetaPass))),true); 
	  
	  try{  
		
		// 檢查參數
		if(!preg_match('/^[\w\d\-]+$/',$VolumeId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得卷資料
		$collection = array();
		$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_META('source_digiarchive'));
		$DB_SOURCE->bindParam(':id'   , $VolumeId );	
		if( !$DB_SOURCE->execute() || !$collection = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		if(isset($meta_newa['file_no'])){  	
	      $temp_identifier = $VolumeId.'-'.str_pad(intval($meta_newa['file_no']),3,'0',STR_PAD_LEFT);	
		}else{
		  // 計算新件號
		  $elements    = [];
		  $max_file_no = 0;
		  $DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_COLLECTION_ELEMENTS($db_element_table));
		  $DB_GET->bindParam(':collection' , $meta['collection']);	
		  if( !$DB_GET->execute() ){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		  }
		  while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		    $max_file_no = $max_file_no < $tmp['file_no'] ? $tmp['file_no'] :  $max_file_no;
		  }	
		  $temp_identifier = $VolumeId.'-'.str_pad(($max_file_no+1),3,'0',STR_PAD_LEFT);
		}
		
		// 新增件資料
		$DB_NEW	= $this->DBLink->prepare(SQL_AdMeta::INSERT_ELEMENT_DATA());
		$DB_NEW->bindParam(':collection_id'  , $VolumeId);
		$DB_NEW->bindParam(':user' , $this->USER->UserID);
		if( !$DB_NEW->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');
		}
		
		$new_element_no  = $this->DBLink->lastInsertId('source_digielement');
		
		$source_array = ['collection'=>$collection,'element'=>[]];
		$source_array['element'] = [
		  'collection_id'=>$VolumeId,
		  'sotre_no'=>$temp_identifier
		];
		
		// 新增meta資料
		$DB_NEWMETA = $this->DBLink->prepare(SQL_AdMeta::INSERT_NEW_METADATA());	
	    
		// 註冊meta資料
		$DB_NEWMETA->bindValue(':class'		,$collection['class']);
		$DB_NEWMETA->bindValue(':zong'		, $collection['zong']);
		$DB_NEWMETA->bindValue(':data_type','element');
		$DB_NEWMETA->bindValue(':collection'	,$collection['store_no']);
		$DB_NEWMETA->bindValue(':identifier'	,$temp_identifier);
		$DB_NEWMETA->bindValue(':applyindex'	,'');
		$DB_NEWMETA->bindValue(':source_json'	,json_encode($source_array,JSON_UNESCAPED_UNICODE));
		$DB_NEWMETA->bindValue(':search_json'	,'[]');
		$DB_NEWMETA->bindValue(':dobj_json'		,json_encode(array('dopath'=>$collection['zong'].'/','folder'=>$VolumeId)));
		$DB_NEWMETA->bindValue(':refer_json'	,json_encode(array()));
		$DB_NEWMETA->bindValue(':page_count'	,1);
		$DB_NEWMETA->bindValue(':lockmode'		,'');
		$DB_NEWMETA->bindValue(':auditint'		,0);
		$DB_NEWMETA->bindValue(':checked'		,1);
		$DB_NEWMETA->bindValue(':digited'		,1);
		$DB_NEWMETA->bindValue(':open'			,0);
		$DB_NEWMETA->bindValue(':view'			,'');
		if(!$DB_NEWMETA->execute()){
		  throw new Exception('新增資料失敗'); 	
		}
		$system_id = $this->DBLink->lastInsertId('metadata');
		
		
		// 執行更新
		$element_meta = array('store_no'=>$temp_identifier);
		if(count($meta_newa)){
		  $element_meta = array_merge($element_meta,$meta_newa);
		}
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($element_meta),'source_digielement','seno'));
		$DB_UPD->bindValue(':id'        , $new_element_no);
		foreach($element_meta as $ef=>$ev){
		  $DB_UPD->bindValue(':'.$ef  , $ev);	
		}
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行mdlogs
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $collection['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $system_id);
		$DB_LOGS->bindValue(':identifier' , $temp_identifier);
		$DB_LOGS->bindValue(':method'     , 'CREATE');
		$DB_LOGS->bindValue(':source' 	  , '[]');
		$DB_LOGS->bindValue(':update' 	  , json_encode($element_meta));
		$DB_LOGS->bindValue(':user' 	  , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		
		// final
		$result['data']['system_id']   = $system_id;
		$result['data']['source_id']   = $new_element_no;
		$result['data']['store_no']    = $temp_identifier;
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
		$result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta : Dele Meta Element
	// [input] : VolumeId  :  source.store_no
	// [input] : ElementId :  source_element.store_no
	public function ADBuilt_Dele_Element_Data( $VolumeId='',$ElementId=''){
	  
	  $result_key = parent::Initial_Result('dele');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId) || !preg_match('/^[\w\d\-]+$/',$ElementId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 取得原始資料
		$source_meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_FROM_METADATA('source_digielement','identifier'));
		$DB_GET->bindParam(':id'   , $ElementId );	
		if( !$DB_GET->execute() || !$source_meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 取得meta資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SEARCH_META());
		$DB_GET->bindParam(':system_id'   , $source_meta['system_id'] );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 執行DB record 刪除標記
		$DB_DEL= $this->DBLink->prepare(SQL_AdMeta::MARK_DB_DELETE('source_digielement','identifier'));
		$DB_DEL->bindValue(':id'    , $ElementId);
		$DB_DEL->bindValue(':user'  , $this->USER->UserID);
	    if( !$DB_DEL->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	 
		}
		
		$active = self::ADMeta_Process_Meta_Update($source_meta['system_id']);
		
		if(!$active['action']){
		  // 索引更新不成功
		  $DB_RESTORE= $this->DBLink->prepare(SQL_AdMeta::UNDO_DB_DELETE('source_digielement','identifier'));
		  $DB_DEL->bindValue(':id'    , $ElementId);
		  $DB_DEL->execute();
		  throw new Exception();
		}
		
		// 執行mdlogs
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['identifier']);
		$DB_LOGS->bindValue(':method'     , 'DELETE');
		$DB_LOGS->bindValue(':source' 	  , json_encode($source_meta));
		$DB_LOGS->bindValue(':update' 	  , '[]');
		$DB_LOGS->bindValue(':user' 	  , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		
		// final 
		$result['action'] = true;
    	$result['data']   = $ElementId;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Built : Get Volume Source Metadata
	// [input] : SystemId  		:   // metadata.system_id
	// [input] : SwitchMethod   :   // prev / next
	// [input] : FolderConf     :   // $_SESSION['SNS']['_ADMETA_FOLDERS'] 
	// [input] : UiConfigEncode :   // [domid => config ]
	public function ADBuilt_Editor_Target_Switch($SystemId='',$SwitchMethod='',$FolderConf=[],$UiConfigEncode=''){
		
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!intval($SystemId) || !preg_match('/^\d+$/',$SystemId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		$work_folder = isset($FolderConf['_focus']) ? $FolderConf['_focus'] :'';
		if(!isset($FolderConf[$work_folder])){
		  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');
		}
		
		$work_queue  = $FolderConf[$work_folder];
		if(!is_array($work_queue) || !in_array($SystemId,$work_queue)){
		  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');	
		}
		
		$work_index = array_search($SystemId,$work_queue); 
		$work_next  = strtolower($SwitchMethod)=='next' ? ++$work_index:--$work_index;
		
		if(!isset($work_queue[$work_next])){
		  throw new Exception('_META_EDITOR_SWITCH_OVERFLOW');			
		}
		
		// 取得meta資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_SEARCH_META());
		$DB_GET->bindParam(':system_id'   , $work_queue[$work_next] );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$editor_index = $meta['data_type'] == 'collection' ? $meta['system_id'] : $meta['system_id'].'#'.$meta['identifier'];
		
		
		// 儲存介面設定
		if($UiConfigEncode){
		  $ui_config  = json_decode(base64_decode(str_replace('*','/',rawurldecode($UiConfigEncode))),true); 
	  	  $editor_config_file = _SYSTEM_USER_PATH.$this->USER->UserID.'/editor.conf';
		  $editor_config_array= [];
		  if(file_exists($editor_config_file)){
			$editor_config_array = json_decode(file_get_contents($editor_config_file),true);  
		  }else{
			$editor_config_array['uiconfig'] = [];
		  }
		  $editor_config_array['uiconfig'] = $ui_config;
		  file_put_contents($editor_config_file,json_encode($editor_config_array));   
		}
		
		// final
		$result['action'] = true;
		$result['data']   = $editor_index;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	//-- Admin Built : Renew Metadata Table
	public function ADMeta_Process_Meta_Update($MetaId=0){
	  
	  $result_key = parent::Initial_Result('renew');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		if(intval($MetaId)){
		  $DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_RENEW_TARGET());	
		  $DB_GET->bindParam(':id',$MetaId);
		}else{
		  $DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_RENEW_META());	
		}
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 設定索引
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
		
		
		$meta_reindex_counter = 0;
		$meta_volumn_queue = array();  // 暫存meta檔案
		
		
		while( $meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  
		  $source_array = [];
		  
		  // 取得 volumn source 資料
		  if(!isset($meta_volumn_queue[$meta['collection']])){
			
			$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_META('source_digiarchive'));
			$DB_SOURCE->bindParam(':id'   , $meta['collection'] );	
			if( !$DB_SOURCE->execute() || !$volume = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
			} 
            $meta_volumn_queue[$meta['collection']] = $volume;
		  }
		  
		  $source_array['collection'] = $meta_volumn_queue[$meta['collection']];
		  
		  if($meta['data_type']=='element'){
			$element = [];
			$DB_SOURCE	= $this->DBLink->prepare( SQL_AdMeta::GET_SOURCE_META('source_digielement'));
			$DB_SOURCE->bindParam(':id'   , $meta['identifier'] );	
			if( !$DB_SOURCE->execute() || !$element = $DB_SOURCE->fetch(PDO::FETCH_ASSOC)){
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
			}
		    $source_array['element'] = $element;
		  }
		  
		  $search_conf = [];
		
		  switch($meta['class']){
		  
		    case "relic":
		    
			    $search_conf['class']   = $meta['class'];
				$search_conf['zong']    = $meta['zong'];
				$search_conf['fonds']   = $source_array['collection']['fonds'];
				
			 
				$search_conf['data_type']   = $meta['data_type'];
				$search_conf['collection']  = $meta['collection'];
				$search_conf['identifier']  = '';
				$search_conf['applyindex']  = $meta['applyindex'];
				
				$search_conf['store_id']    = $source_array['collection']['store_id']; 
				
				if($meta['data_type']=='collection'){
					
					// 確認日期
					$search_conf['store_orl']   = $source_array['collection']['store_orl'];
					
					$search_conf['date_string'] = '民國'.$source_array['collection']['store_no1'].'年入館';
					
					$meta_date[] = $source_array['collection']['store_date'];
					$parsedate = self::paser_date_array($meta_date);
					$search_conf['date_start'] = $parsedate['ds'];
					$search_conf['date_end']   = $parsedate['de'];
					
					$search_conf['storeyear']  = ['民國'.$source_array['collection']['store_no1'].'年'];
					$search_conf['savedyear']  = $parsedate['years'];
					
					$search_conf['title']   		= $source_array['collection']['title'];
					$search_conf['categories']  	= $source_array['collection']['categories'];
					$search_conf['ethnic']   		= $source_array['collection']['ethnic'];
					$search_conf['acquire_type']   	= $source_array['collection']['acquire_type'];
					$search_conf['status_code']   	= $source_array['collection']['status_code'];  
					$search_conf['acquire_info']   	= $source_array['collection']['acquire_info'];
					$search_conf['status_descrip']  = $source_array['collection']['status_descrip'];
					$search_conf['store_date']   	= $source_array['collection']['store_date'];
					$search_conf['store_location']  = $source_array['collection']['store_location'];
					$search_conf['store_number']   	= $source_array['collection']['store_number'];
					$search_conf['store_boxid']   	= $source_array['collection']['store_boxid'];
					
					$search_conf['store_information'] = $search_conf['store_location'].' / '.$search_conf['store_number'];
					if($source_array['collection']['store_boxid']){
					  $search_conf['store_information'] .= ' / '.$source_array['collection']['store_boxid'];	
					}
					
					$search_conf['remark']   			= $source_array['collection']['remark'];
					
					$search_conf['count_dofiles']   	= $source_array['collection']['count_dofiles'];
					$search_conf['count_element']   	= $source_array['collection']['count_element'];
					
					$search_conf['logout_flag']  		= $source_array['collection']['logout_flag'];
					
					// 後分類篩選
					$search_conf['list_store_type']		= self::paser_postquery([$source_array['collection']['store_type']]);
					$search_conf['list_ethnic'] 		= self::paser_postquery([$source_array['collection']['ethnic']]);
					$search_conf['list_status_code'] 	= self::paser_postquery([$source_array['collection']['status_code']]);
					$search_conf['list_store_location'] = self::paser_postquery([$source_array['collection']['store_location']]);
					
					
					
					
					// 系統設定
					$search_conf['_flag_secret']  = $source_array['collection']['_flag_secret'];
					$search_conf['_flag_privacy'] = intval($source_array['collection']['_flag_privacy']);
					$search_conf['_flag_open']    = intval($source_array['collection']['_flag_open']);
					$search_conf['_flag_mask']    = 0;
					$search_conf['_flag_update']  = 0;
					$search_conf['_flag_view']    = $source_array['collection']['_view'];
					
					$lockmode   = '普通';
					$auditint   = $source_array['collection']['_flag_privacy'];
					$open       = $source_array['collection']['_flag_open'];
					$view		= $source_array['collection']['_view'];	
					
				}else{  // 單篇meta
				    
					
					$search_conf['identifier']  = $meta['identifier'];
				    $search_conf['applyindex']  = $meta['applyindex'];
				    $search_conf['data_type']   = 'element';
				    
				    
					$search_conf['location']   	= $source_array['element']['location'];
					$search_conf['period']   	= $source_array['element']['period'];
					$search_conf['creator']   	= $source_array['element']['creator'];
					$search_conf['title']   	= $source_array['element']['doname'];
					$search_conf['abstract']    = $source_array['element']['abstract'];
					$search_conf['remark']   	= $source_array['element']['remark'];
					
					
					// 後分類篩選
					$search_conf['list_dotype'] 		= self::paser_postquery([$source_array['element']['dotype']]);
					
					$search_conf['_flag_secret']  = $source_array['element']['_flag_secret'];
					$search_conf['_flag_privacy'] = intval($source_array['element']['_flag_privacy']);
					$search_conf['_flag_open']    = intval($source_array['element']['_flag_open']);
					$search_conf['_flag_mask']    = 0;
					$search_conf['_flag_update']  = 0;
					$search_conf['_flag_view']    = $source_array['element']['_view'];
					
					$lockmode   = '普通';
					$auditint   = $source_array['element']['_flag_privacy'];
					$open       = $source_array['element']['_flag_open'];
					$view		= $source_array['element']['_view'];   
					
				}
				
		        break;
		  }
		  
		  // 更新索引
	      $params = [
			'index' => strtolower(_SYSTEM_NAME_SHORT),
			'type' => 'search',
			'id' => $meta['system_id'],
		  ];
			  
		  try {
			 
			if(intval($meta['_keep'])){
			  $params['body']=$search_conf;
			  $response = $client->index($params);
			  
			  // renew_meta
			  $DB_UPD = $this->DBLink->prepare(SQL_AdMeta::UPDATE_METADATA_DATA(array('source_json','search_json','_lockmode','_auditint','_open','_view','_index')));
			  $DB_UPD->bindValue(':_lockmode'   , $lockmode);
			  $DB_UPD->bindValue(':_auditint'	, $auditint);
			  $DB_UPD->bindValue(':_open'	 	, $open );
			  $DB_UPD->bindValue(':_view'	 	, $view);
			  $DB_UPD->bindValue(':_index'		, 1 );
			  $DB_UPD->bindValue(':source_json',json_encode($source_array,JSON_UNESCAPED_UNICODE));
			  $DB_UPD->bindValue(':search_json',json_encode($search_conf,JSON_UNESCAPED_UNICODE));
			  $DB_UPD->bindValue(':sid', $meta['system_id']);
			
			  if(!$DB_UPD->execute()){
			    throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL'); 	
			  }
			   
			}else{
			  $response = $client->delete($params);
			  
			  // delete meta
			  $DB_DELE= $this->DBLink->prepare(SQL_AdMeta::DELETE_MARK_METADATA());
			  $DB_DELE->bindValue(':sid'    , $meta['system_id'] );
			  if( !$DB_DELE->execute()){
				throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
			  }
			  
			}
			  //var_dump($response);
		  } catch (Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
			$logs_message = date("Y-m-d H:i:s").' [ELASTIC]'.$e->getMessage().'. '.PHP_EOL;
			file_put_contents('logs.txt',$logs_message,FILE_APPEND);
			//echo $e->getMessage().PHP_EOL;
	      }
		  
		  $meta_reindex_counter++;
		} // end of while 
		
		// final
		$result['data'] = $meta_reindex_counter;
    	$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Built : Get Volume Source Metadata
	// [input] : VolumeId  	:    
	// [input] : HtmlEncode :    
	// [input] : Image     	:    
	 
	public function ADBuilt_Print_Volume_Page($VolumeId='',$Image=''){
		
	  $result_key = parent::Initial_Result('print');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$print_page = $Image;
		
		// final
		$result['action'] = true;
		$result['data']['html']   = $print_html;
		$result['data']['page']   = $print_page;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	
	
	
	
	
	
	
	//-- Admin Built : Save Volume Research Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : ResearchId  :   // source_research.srno
    // [input] : PaserString  :   
    public function ADBuilt_Research_Save($VolumeId='',$ResearchId='',$PaserString=''){
		
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$research_meta = json_decode(base64_decode(str_replace('*','/',rawurldecode($PaserString))),true); 
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if($ResearchId!='_addnew' && !intval($ResearchId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		if($ResearchId=='_addnew'){
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::NEWA_RESEARCH_RECORD());	
		}else{
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::SAVE_RESEARCH_RECORD());
		  $DB_SAVE->bindValue(':srno',$ResearchId);
		}
		
		$DB_SAVE->bindValue(':user',$this->USER->UserID); 
		$DB_SAVE->bindValue(':cid',$VolumeId); 
		
		foreach($research_meta as $vrfield=>$vrvalue){
		  $dbfield = preg_replace('/^META-R-/','',$vrfield);	
		  $DB_SAVE->bindValue(':'.$dbfield,$vrvalue);	
		}
		
		if(!$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		
        $srno = $ResearchId=='_addnew' ? $this->DBLink->lastInsertId() : $ResearchId;
		
		// final
		$result['action'] = true;
		$result['data'] = $srno;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	//-- Admin Built : Save Volume Research Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : ResearchId  :   // source_research.srno
    public function ADBuilt_Research_Read($VolumeId='',$ResearchId=''){
		
	  $result_key = parent::Initial_Result('read');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($ResearchId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_READ = $this->DBLink->prepare( SQL_AdMeta::READ_RESEARCH_RECORD());
		$DB_READ->bindValue(':cid',$VolumeId);
		$DB_READ->bindValue(':srno',$ResearchId);
		$research = null;
		if(!$DB_READ->execute() || !$dbgot=$DB_READ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		foreach($dbgot as $rf => $rv ){
		  $research['META-R-'.$rf] = $rv;
		}
		$research['META-R-update'] = $dbgot['_timeupdate'].' @ '.$dbgot['_userupdate'];
		
		// final
		$result['action'] = true;
		$result['data']   = $research;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	//-- Admin Built : DELETE Volume Research Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : ResearchId  :   // source_research.srno
    public function ADBuilt_Research_Delete($VolumeId='',$ResearchId=''){
		
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($ResearchId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_DELE = $this->DBLink->prepare( SQL_AdMeta::DELE_RESEARCH_RECORD());
		$DB_DELE->bindValue(':cid',$VolumeId);
		$DB_DELE->bindValue(':srno',$ResearchId);
		if(!$DB_DELE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	//-- Admin Built : Save Volume Movement Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : MovementId  :   // source_movement.smno
    // [input] : PaserString  :  meta encode array
    public function ADBuilt_Movement_Save($VolumeId='',$MovementId='',$PaserString=''){
		
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$research_meta = json_decode(base64_decode(str_replace('*','/',rawurldecode($PaserString))),true); 
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if($MovementId!='_addnew' && !intval($MovementId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		if($MovementId=='_addnew'){
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::NEWA_MOVEMENT_RECORD());	
		}else{
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::SAVE_MOVEMENT_RECORD());
		  $DB_SAVE->bindValue(':smno',$MovementId);
		}
		
		$DB_SAVE->bindValue(':user',$this->USER->UserID); 
		$DB_SAVE->bindValue(':cid',$VolumeId); 
		
		foreach($research_meta as $vrfield=>$vrvalue){
		  $dbfield = preg_replace('/^META-M-/','',$vrfield);	
		  $DB_SAVE->bindValue(':'.$dbfield,$vrvalue);	
		}
		
		if(!$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		
        $sno = $MovementId=='_addnew' ? $this->DBLink->lastInsertId() : $MovementId;
		
		$DB_COUNT = $this->DBLink->prepare( SQL_AdMeta::COUNT_MOVEMENT_RECORD());	
		$DB_COUNT->execute(['cid'=>$VolumeId]);
		$num = $DB_COUNT->fetchcolumn();
		
		// final
		$result['action'] = true;
		$result['data']['sno'] = $sno;
		$result['data']['num'] = $num;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	//-- Admin Built : Save Volume Movement Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : MovementId  :   // source_research.srno
    public function ADBuilt_Movement_Read($VolumeId='',$MovementId=''){
		
	  $result_key = parent::Initial_Result('read');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($MovementId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_READ = $this->DBLink->prepare( SQL_AdMeta::READ_MOVEMENT_RECORD());
		$DB_READ->bindValue(':cid',$VolumeId);
		$DB_READ->bindValue(':smno',$MovementId);
		$research = null;
		if(!$DB_READ->execute() || !$dbgot=$DB_READ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		foreach($dbgot as $rf => $rv ){
		  $research['META-M-'.$rf] = $rv;
		}
		
		// final
		$result['action'] = true;
		$result['data']   = $research;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	//-- Admin Built : DELETE Volume Movement Record
	// [input] : VolumeId  :   // source_digiarchive.store_no
	// [input] : MovementId  :   // source_movement.smno
    public function ADBuilt_Movement_Delete($VolumeId='',$MovementId=''){
		
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($MovementId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_DELE = $this->DBLink->prepare( SQL_AdMeta::DELE_MOVEMENT_RECORD());
		$DB_DELE->bindValue(':cid',$VolumeId);
		$DB_DELE->bindValue(':smno',$MovementId);
		if(!$DB_DELE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	//-- Admin Built : Save Volume Display Record
	// [input] : VolumeId     :   // source_digiarchive.store_no
	// [input] : DisplayId    :   // source_display.sdno
    // [input] : PaserString  :  meta encode array
    public function ADBuilt_Display_Save($VolumeId='',$DisplayId='',$PaserString=''){
		
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$research_meta = json_decode(base64_decode(str_replace('*','/',rawurldecode($PaserString))),true); 
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if($DisplayId!='_addnew' && !intval($DisplayId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		if($DisplayId=='_addnew'){
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::NEWA_DISPLAY_RECORD());	
		}else{
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::SAVE_DISPLAY_RECORD());
		  $DB_SAVE->bindValue(':sdno',$DisplayId);
		}
		
		$DB_SAVE->bindValue(':user',$this->USER->UserID); 
		$DB_SAVE->bindValue(':cid',$VolumeId); 
		  
		foreach($research_meta as $vrfield=>$vrvalue){
		  $dbfield = preg_replace('/^META-D-/','',$vrfield);	
		  $DB_SAVE->bindValue(':'.$dbfield,$vrvalue);	
		}
		
		if(!$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$sno = $DisplayId=='_addnew' ? $this->DBLink->lastInsertId() : $DisplayId;
		
		// 新增異動紀錄
		if($DisplayId=='_addnew'){
		  $DB_SAVE	= $this->DBLink->prepare( SQL_AdMeta::NEWA_MOVEMENT_RECORD());	
		  $DB_SAVE->bindValue(':user',$this->USER->UserID); 
		  $DB_SAVE->bindValue(':cid',$VolumeId); 
		  
		  $DB_SAVE->bindValue(':move_type','移動');	
		  $DB_SAVE->bindValue(':move_date',date('Y-m-d'));	
		  $DB_SAVE->bindValue(':move_location',$research_meta['META-D-display_place']);	
		  $DB_SAVE->bindValue(':move_reason'  ,$research_meta['META-D-display_organ'].'借展');	
		  $DB_SAVE->bindValue(':move_handler' ,$this->USER->UserID);	
		  if(!$DB_SAVE->execute()){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		  }
		}
		
		
		$DB_COUNT = $this->DBLink->prepare( SQL_AdMeta::COUNT_DISPLAY_RECORD());	
		$DB_COUNT->execute(['cid'=>$VolumeId]);
		$num = $DB_COUNT->fetchcolumn();
		
		// final
		$result['action'] = true;
		$result['data']['sno'] = $sno;
		$result['data']['num'] = $num;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	//-- Admin Built : Save Volume Display Record
	// [input] : VolumeId  :     // source_digiarchive.store_no
	// [input] : DisplayId  :   // source_display.sdno
    public function ADBuilt_Display_Read($VolumeId='',$DisplayId=''){
		
	  $result_key = parent::Initial_Result('read');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($DisplayId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_READ = $this->DBLink->prepare( SQL_AdMeta::READ_DISPLAY_RECORD());
		$DB_READ->bindValue(':cid',$VolumeId);
		$DB_READ->bindValue(':sdno',$DisplayId);
		$research = null;
		if(!$DB_READ->execute() || !$dbgot=$DB_READ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		foreach($dbgot as $rf => $rv ){
		  $research['META-D-'.$rf] = $rv;
		}
		
		// final
		$result['action'] = true;
		$result['data']   = $research;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	//-- Admin Built : DELETE Volume Display Record
	// [input] : VolumeId  :     // source_digiarchive.store_no
	// [input] : DisplayId  :   // source_display.sdno
    public function ADBuilt_Display_Delete($VolumeId='',$DisplayId=''){
		
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$VolumeId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		if(!intval($DisplayId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$DB_DELE = $this->DBLink->prepare( SQL_AdMeta::DELE_DISPLAY_RECORD());
		$DB_DELE->bindValue(':cid',$VolumeId);
		$DB_DELE->bindValue(':sdno',$DisplayId);
		if(!$DB_DELE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	
	
	
	//-- Admin Meta : Batch Rename DOBJ And ReSave profile
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : FilePreHeader : 檔名前墜 
	// [input] : FileStartNum  : 檔名起始編號,含編號長度  001
	// [input] : DOSelectEncode  : digital file name array 
	public function ADMeta_Dobj_Batch_Rename( $DataType='' , $DataFolder='' ,$FilePreHeader='', $FileStartNum='' ,$DOSelectEncode='' ){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		$process_counter = 0;
		$process_list    = array();
		
		// 檢查檔名參數
		if(!$FilePreHeader || !$FileStartNum){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 檢查勾選列表
		$dobj_name_array = json_decode(base64_decode(str_replace('*','/',rawurldecode($DOSelectEncode))),true); 
		if(!$dobj_name_array || !is_array($dobj_name_array) || !count($dobj_name_array)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 設定檔名規則
		// 掃描資料並刪除
		if(!preg_match('/^(\d+)(.*?)/',$FileStartNum,$match)){
		  throw new Exception('_META_DOBJ_RENAME_STARTNUM_PATTERN_FAILE');	 	
		}
		$new_fileheader 	= $FilePreHeader;
		$new_filenum_start  = intval($match[1]);
		$new_filenum_length = strlen($match[1]);
		$new_filenum_footer = isset($match[2]) ? $match[2] : '';
		
		
		// 檢測重排模式為往前或往後
		$rename_mode = '-';
		$first_element_num = intval(str_replace($new_fileheader,'',$dobj_name_array[0]));
		if($new_filenum_start <= $first_element_num){  
          //前排模式 		
		  $dobj_rename_array     = $dobj_name_array;
		  $rename_filenum_start  = $new_filenum_start;	
		  $rename_mode = '+';
		}else{
		  //後排模式	
		  $dobj_rename_array     = array_reverse($dobj_name_array);
		  $rename_filenum_start  = $new_filenum_start + count($dobj_name_array) -1 ;	
		  $rename_mode = '-';
		}
		
		
		
		$rename_counter = 0;
		
		//確認實體檔案
		$dobj_path = [];
		$dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/';
		$dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/';
		$dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/';
		
		
		// 執行重新命名
		foreach($dobj_rename_array as $target_do_file){
          
		  list($orl_filename,$orl_file_extension) = explode('.',$target_do_file);
          
		  if($rename_mode=='-'){  //依據模式命名檔案
			$new_filename = $new_fileheader.str_pad($rename_filenum_start-$rename_counter,$new_filenum_length,'0',STR_PAD_LEFT).$new_filenum_footer.'.'.$orl_file_extension;		    
		  }else{
			$new_filename = $new_fileheader.str_pad($rename_filenum_start+$rename_counter,$new_filenum_length,'0',STR_PAD_LEFT).$new_filenum_footer.'.'.$orl_file_extension;  
		  }
		  
		  $rename_counter++; 
		   
		  // 掃描原始資料設定
		  $dobjlist_save = $dobj_profile['items'];
		  $check_file_name = array(); // ['hasfile'=>false,'collide'=>false]檢查重新命名是否有問題，若有儲存位置
		  foreach($dobjlist_save  as $i => $doset){
            if($doset['file']==$target_do_file) $check_file_name['hasfile']=$i;
			if($doset['file']==$new_filename) $check_file_name['collide']=$i;
		  }
		  
          //確認原始列表內是否有碰撞(預期是沒有碰撞)
		  if(isset($check_file_name['collide'])){
            
			if(isset($check_file_name['hasfile']) && $check_file_name['collide']==$check_file_name['hasfile'] ){
			  // 碰撞號與當前編號相同，不處理
			  continue;
			}
			
			$target_do = $dobj_profile['items'][$check_file_name['collide']];
			$target_do_change_name = preg_replace('/\./','_.',$target_do['file']);
			
			foreach($dobj_path as $active_folder){
		      if(!file_exists($active_folder.$target_do['file'])){ continue; }		
			  if(copy($active_folder.$target_do['file'],$active_folder.$target_do_change_name)){
				unlink($active_folder.$target_do['file']); 
			  }		  
			}
			
			$dobj_profile['items'][$check_file_name['collide']]['file'] = $target_do_change_name;
		    
			// 紀錄
		    //確認檔案已轉移
			if(!file_exists($dobj_profile['store'].$target_do_change_name)){
			  continue; //檔案未處理成功	
			}
			
			$DB_LOG= $this->DBLink->prepare( SQL_AdMeta::LOGS_DOBJ_MODIFY());
		    $DB_LOG->bindParam(':file'   , $target_do['file'] );	
		    $DB_LOG->bindValue(':action' , 'collide' );
		    $DB_LOG->bindParam(':store'  , $target_do_change_name);
		    $DB_LOG->bindValue(':note'   , '' );
		    $DB_LOG->bindParam(':user'   , $this->USER->UserID);
		    $DB_LOG->execute();
			
			unset($check_file_name['collide']);
			
		  }
          
		  //確認是否檔案未碰撞並存在
		  if(isset($check_file_name['collide']) || !isset($check_file_name['hasfile'])){
			continue;  
		  }
			
	      $target_do = $dobj_profile['items'][$check_file_name['hasfile']];  
            			
          // 各資料夾變更檔案
		  foreach($dobj_path as $active_folder){
		    if(!file_exists($active_folder.$target_do['file'])){ continue; }
			if(copy($active_folder.$target_do['file'],$active_folder.$new_filename)){
			  unlink($active_folder.$target_do['file']); 
			}
		  }
		  
		  $dobj_profile['items'][$check_file_name['hasfile']]['file'] = $new_filename;
		  
		  //確認檔案已轉移
		  if(!file_exists($dobj_profile['store'].$new_filename)){
			continue; //檔案未處理成功	
		  }
			
		  $DB_LOG= $this->DBLink->prepare( SQL_AdMeta::LOGS_DOBJ_MODIFY());
		  $DB_LOG->bindParam(':file'   , $target_do['file'] );	
		  $DB_LOG->bindValue(':action' , 'rename' );
		  $DB_LOG->bindParam(':store'  , $new_filename);
		  $DB_LOG->bindValue(':note'   , '' );
		  $DB_LOG->bindParam(':user'   , $this->USER->UserID);
		  $DB_LOG->execute();
		  
		  $process_counter++;
		  $process_list[] = $target_do_file;
		
		}
		
		$dobj_profile['saved'] = date('Y-m-d H:i:s');
		$dobj_config = file_put_contents($profile_path,json_encode($dobj_profile));
		
		// final 
		$result['data']['count'] = $process_counter;
		$result['data']['list']  = $process_list ;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Meta : Batch Reorder DOBJ And ReSave profile
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : DOFilesEncode  : digital file name array : all file
	public function ADMeta_Dobj_Batch_Reorder( $DataType='' , $DataFolder='' ,$DOFilesEncode='' ){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		$process_counter = 0;
		$process_list    = array();
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 檢查檔案順序列表
		$dobj_name_array = json_decode(base64_decode(str_replace('*','/',rawurldecode($DOFilesEncode))),true); 
		if(!$dobj_name_array || !is_array($dobj_name_array) || !count($dobj_name_array)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		$dobjlist_save = $dobj_profile['items'];
		
		//確認檔案數量相符 
		if(count($dobj_name_array) != count($dobjlist_save)){
		  throw new Exception('_META_DOBJ_REORDER_FILE_COUNT_NOT_MATCH');		
		}
		
		//確認實體檔案
		$dobj_path = [];
		$dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/';
		$dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/';
		$dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/';
		
		$reorder_do_profile = array();  //新順序之設定
		
		// 執行重新命名
		foreach($dobj_name_array as $newi => $target_do_file){
		  
		  foreach($dobjlist_save  as $orli => $doset){
            if($doset['file']==$target_do_file){
			  
			  $reorder_do_profile[] = $doset;  	
			   
			  if($orli!==$newi){
			    $DB_LOG= $this->DBLink->prepare( SQL_AdMeta::LOGS_DOBJ_MODIFY());
			    $DB_LOG->bindParam(':file'   , $target_do_file );	
			    $DB_LOG->bindValue(':action' , 'reorder' );
			    $DB_LOG->bindValue(':store'  , '');
			    $DB_LOG->bindValue(':note'   , $orli.'=>'.$newi );
			    $DB_LOG->bindParam(':user'   , $this->USER->UserID);
			    $DB_LOG->execute();
			  
			    $process_counter++;
			    $process_list[] = $target_do_file;
			  
			  }
			  break;
			}
		  }
		}
		
		// 確認資料已變更
		if(md5(json_encode($dobj_profile['items']))!=md5(json_encode($reorder_do_profile))){
		  $dobj_profile['items'] = $reorder_do_profile;
		  $dobj_profile['saved'] = date('Y-m-d H:i:s');
		  $dobj_config = file_put_contents($profile_path,json_encode($dobj_profile));	
		}
		
		// final 
		$result['data']['count'] = $process_counter;
		$result['data']['list']  = $process_list ;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Meta : Batch Delete DOBJ And ReSave profile
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : DOSelectEncode  : digital file name array 
	// [input] : Recapture  : 驗證碼
	// [input] : Var  : digital file name array 
	public function ADMeta_Dobj_Batch_Delete( $DataType='' , $DataFolder='' ,$DOSelectEncode='' ,$Recapture='', $Verification=''){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		$process_counter = 0;
		$process_list    = array();
		
		// 檢查驗證碼
		if(!$Recapture || $Recapture!==$Verification){
		  throw new Exception('_REGISTER_ERROR_CAPTCHA_TEST_FAIL');
		}
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 檢查勾選列表
		$dobj_name_array = json_decode(base64_decode(str_replace('*','/',rawurldecode($DOSelectEncode))),true); 
		if(!$dobj_name_array || !is_array($dobj_name_array) || !count($dobj_name_array)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 掃描資料並刪除
		$dobjlist_save = $dobj_profile['items'];
		
		foreach($dobjlist_save  as $i => $doset){
		    
          if(!in_array($doset['file'],$dobj_name_array)){ //檔案不在刪除清單中
			continue;  
		  }
		  
		  //確認實體檔案
		  $dobj_path = [];
		  $dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/'.$doset['file'];
		  $dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/'.$doset['file'];
		  $dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/'.$doset['file'];
		  
		  $resavename = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/trash/'.$doset['file'].microtime('true');  // 垃圾桶位置
		  
		  foreach($dobj_path as $dotype => $dopath){
            if(!file_exists($dopath)){ continue; }		
            copy($dopath , $resavename);
			unlink($dopath); 
		  }
		  
		  // 移出profile
		  unset($dobj_profile['items'][$i]);
		  
		  // 紀錄
		  $DB_LOG= $this->DBLink->prepare( SQL_AdMeta::LOGS_DOBJ_MODIFY());
		  $DB_LOG->bindParam(':file'   , $doset['file'] );	
		  $DB_LOG->bindValue(':action' , 'delete' );
		  $DB_LOG->bindParam(':store'  , $resavename);
		  $DB_LOG->bindValue(':note'   , '' );
		  $DB_LOG->bindParam(':user'   , $this->USER->UserID);
		  $DB_LOG->execute();
		  
          $process_list[] = $doset['file'];
		  $process_counter++;
		
		}
		
		$dobj_config = file_put_contents($profile_path,json_encode($dobj_profile));
		
		// final 
		$result['data']['count'] = $process_counter;
		$result['data']['list']  = $process_list ;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Meta : Batch Delete DOBJ And ReSave profile
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	public function ADMeta_Dobj_Buffer_Update( $DataType='' , $DataFolder='' ){
		
	  $result_key = parent::Initial_Result('buffer');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// 更新imageBuffer
		$system_buffer_patch    = _SYSTEM_DIGITAL_LIST_BUFFER.$DataFolder.'_list.tmp';
		$system_buffer_contents = [$dobj_profile['store']];
		foreach($dobj_profile['items'] as $item){
	      $system_buffer_contents[] = $item['file'];		
		}
		
		file_put_contents($system_buffer_patch,join("\n", $system_buffer_contents));
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
		
	
	
	
	//-- Admin Meta : DOBJ File Download Prepare
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : DoFileName  : digital file name 
	public function ADMeta_Dobj_Prepare( $DataType='' , $DataFolder='' , $DoFileName=''){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		//確認實體檔案
		$dobj_path = [];
		$dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/'.$DoFileName;
		
		$dobj_download = '';
		foreach($dobj_path as $dotype => $dopath){
          if(!file_exists($dopath)){ continue; }		
          $dobj_download = $dopath;
		  break;
		}
		
		if(!$dobj_download){
		  throw new Exception('_META_DOBJ_DOWNLOAD_FILE_NOT_EXIST');			
		}
		
		$hash_download = md5($DoFileName.microtime(true));
		
		// 紀錄
		$DB_LOG= $this->DBLink->prepare( SQL_AdMeta::LOGS_DOBJ_MODIFY());
		$DB_LOG->bindParam(':file'   , $DoFileName );	
		$DB_LOG->bindValue(':action' , 'download' );
		$DB_LOG->bindParam(':store'  , $dobj_download);
		$DB_LOG->bindValue(':note'   , $hash_download);
		$DB_LOG->bindParam(':user'   , $this->USER->UserID);
		$DB_LOG->execute();
		
		// final 
		$result['data']['hash']  = $hash_download;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Meta :DOBJ File Save
	// [input] : DoDownloadHash  : logs_digital.note	
	public function ADMeta_Dobj_Get_Download( $DoDownloadHash=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
	  
	    if(!$DoDownloadHash || strlen($DoDownloadHash)!='32'){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		} 
	  
		$DB_DOBJ= $this->DBLink->prepare( SQL_AdMeta::DOBJ_DOWNLOAD_RESOUCE());
		$DB_DOBJ->bindValue(':action','download');
		$DB_DOBJ->bindValue(':hash',$DoDownloadHash);
		$DB_DOBJ->bindValue(':user',$this->USER->UserID);
		if( !$DB_DOBJ->execute() || !$source = $DB_DOBJ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// final 
		$result['data']['name']  = $source['doname'];
		$result['data']['size']  = filesize($source['store']);
		$result['data']['location']  = $source['store'];
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta : DOBJ File Download Prepare
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : DoFileName  : digital file name 
	public function ADMeta_Dobj_Set_Cover( $DataType='' , $DataFolder='' , $DoFileName=''){
	  
	  $result_key = parent::Initial_Result('dobjs');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		//確認實體檔案
		$dobj_path = [];
		$dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/'.$DoFileName;
		
		$dobj_download = '';
		foreach($dobj_path as $dotype => $dopath){
          if(!file_exists($dopath)){ continue; }		
          $dobj_download = $dopath;
		  break;
		}
		
		$source_update = [];
		$source_update['cover_page']  = $DoFileName;
		$source_update['_userupdate'] = $this->USER->UserID;
		
		// 執行修改
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($source_update), 'source_digiarchive'));
		$DB_SAVE->bindValue(':id'    , $DataFolder);
		foreach($source_update as $uf=>$uv){
		  $DB_SAVE->bindValue(':'.$uf , $uv);
		}
		$DB_SAVE->execute();
		
		// 執行更新 meta
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_METADATA_DATA(array('_index'), 'collection'));
		$DB_SAVE->bindValue(':sid'    , $DataFolder);
		$DB_SAVE->bindValue(':_index' , 0);
		$DB_SAVE->execute();
		
		$active = self::ADMeta_Process_Meta_Update();
		
		
		// final 
		$result['data']   = $DoFileName;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Meta : DOBJ File Download Prepare
	// [input] : DataType    : ARCHIVE....
	// [input] : DataFolder  : collection id // file folder 
	// [input] : DoFileName  : digital file name 
	// [input] : DoNewType   : digital file type
	
	public function ADMeta_Dobj_Set_Type( $DataType='' , $DataFolder='' , $DoFileName='', $DoNewType=''){
	  
	  $result_key = parent::Initial_Result('dobj');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查DO資料設定
	    $profile_path = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/profile/'.$DataFolder.'.conf';
		if(!file_exists($profile_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 讀取DO設定
		$dobj_config = file_get_contents($profile_path);
		$dobj_profile = json_decode($dobj_config,true);
		if( !$dobj_profile || ( !isset($dobj_profile['items']) || !is_array($dobj_profile['items']))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		//確認實體檔案
		$dobj_path = [];
		$dobj_path['saved']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/saved/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['browse'] = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/browse/'.$DataFolder.'/'.$DoFileName;
		$dobj_path['thumb']  = _SYSTEM_DIGITAL_FILE_PATH.$DataType.'/thumb/'.$DataFolder.'/'.$DoFileName;
		
		$dobj_download = '';
		foreach($dobj_path as $dotype => $dopath){
          if(!file_exists($dopath)){ continue; }		
          $dobj_download = $dopath;
		  break;
		}
		
		foreach($dobj_profile['items'] as $i=>$doconf){  
		  if($doconf['file'] != $DoFileName){
			continue;  
		  }
		  $dobj_profile['items'][$i]['dotype'] = $DoNewType;	
		}
		
		if(!isset($dobj_profile['dotype']) || !count($dobj_profile['dotype'])){
		  $dobj_profile['dotype'] = ['文物卡','整理照','出版照','相片','底片','翻拍','其他'];
		}
		
		if(!in_array($DoNewType,$dobj_profile['dotype'])){
		   array_unshift($dobj_profile['dotype'],$DoNewType);	
		}
		
		file_put_contents($profile_path,json_encode($dobj_profile));
		
		/*
		// 執行修改
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_SOURCE_META(array_keys($source_update), 'source_digiarchive'));
		$DB_SAVE->bindValue(':id'    , $DataFolder);
		foreach($source_update as $uf=>$uv){
		  $DB_SAVE->bindValue(':'.$uf , $uv);
		}
		$DB_SAVE->execute();
		
		// 執行更新 meta
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_METADATA_DATA(array('_index'), 'collection'));
		$DB_SAVE->bindValue(':sid'    , $DataFolder);
		$DB_SAVE->bindValue(':_index' , 0);
		$DB_SAVE->execute();
		
		$active = self::ADMeta_Process_Meta_Update();
		*/
		
		// final 
		$result['data']['name']   = $DoFileName;
		$result['data']['type']   = $DoNewType;
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	//-- Admin Meta : Read Meta edit logs
	// [input] : DataNo  :  \w\d+;  system_id
	public function ADMeta_Read_Item_Logs( $DataNo='' ){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$DataNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 取得編輯資料
		$logs = [];
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_LOGS());
		$DB_GET->bindParam(':storeno'   , $DataNo );	
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $logs[] = [
		    'time'=>$tmp['mdtime'],
			'id'=>$tmp['identifier'],
			'editor'=>$tmp['uploader'],
			'fields'=>json_decode($tmp['updated'],true),
		  ];
		}
		
		// final 
		$result['action'] = true;
    	$result['data'] = $logs;
    	
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	//-- Admin Built : Save Task Element
	// [input] : taskid  :  \w\d+;
	// [input] : itemid  :  \w\d+;
	public function ADBuilt_Done_Item_Data( $TaskId='',$ItemId=''){
	  
	  $result_key = parent::Initial_Result('done');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^TSK\d+$/',$TaskId)  ||  !preg_match('/^[\w\d\-]+$/',$ItemId)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		$task_meta['_estatus'] = '_finish';
		
		// 執行更新
		$DB_SAVE= $this->DBLink->prepare(SQL_AdMeta::UPDATE_ELEMENT_DATA(array('_estatus')));
		$DB_SAVE->bindValue(':taskid' , $TaskId);
		$DB_SAVE->bindValue(':itemid' , $ItemId);
		$DB_SAVE->bindValue(':_estatus', '_finish');
		
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 更新任務資料
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_TASK_STATUS());
		$DB_UPD->bindValue(':taskid' , $TaskId);
		$DB_UPD->bindValue(':status' , '_EDITING');
		
		
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行logs
		/*
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['identifier']);
		$DB_LOGS->bindValue(':method'    , 'CREATE/UPDATE/DELETE/BATCH');
		$DB_LOGS->bindValue(':source' , json_encode($source));
		$DB_LOGS->bindValue(':update' , json_encode($meta_update));
		$DB_LOGS->bindValue(':user' , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		*/
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	//-- Admin Meta - DigitalObject View Switch
	// [input] : DataNo    :  \w\d+  = DB.metadata.system_id;
	// [input] : DObjFileName       :  digital file name
	// [input] : DobjConfigString   :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADMeta_DObj_Conf_Save($DataNo='',$DObjFileName='',$DobjConfigString='' ){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
	    
		// 檢查資料序號
	    if(!preg_match('/^[\w\d\-]+$/',$DataNo) || !$DObjFileName || !$DobjConfigString ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$dobj_objects = json_decode(base64_decode(str_replace('*','/',rawurldecode($DobjConfigString))),true);  
		
		if(!count($dobj_objects)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$DataNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 取得編輯資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_DATA());
		$DB_GET->bindParam(':id'   , $DataNo );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$meta_doconf = json_decode($meta['dobj_json'],true);  // from convas objects
		
		
		$canvas_store  = [];
		$config_setting= array();
		$canvas_object = $dobj_objects['objects'][0];
		
		if(isset($canvas_object['objects'])){
		  foreach($canvas_object['objects'] as $object){
			  switch($object['type']){
				case 'image':  
				  $config_setting['base_sx'] = $object['scaleX'] ? $object['scaleX'] : 1;
				  $config_setting['base_sy'] = $object['scaleY'] ? $object['scaleY'] : 1;
				  $config_setting['base_h'] = intval($object['width']*$config_setting['base_sx']);
				  $config_setting['base_w'] = intval($object['height']*$config_setting['base_sy']);
				  $config_setting['base_l'] = $object['left'];
				  $config_setting['base_t'] = $object['top'];
				  break;
				  
				default:
				  $canvas_store[] = [
					'type'	=> 'mask',
					'shap'  => $object['type'],
					'width'	=> round(intval($object['width']*$object['scaleX'])/$config_setting['base_sx'], 2),
					'height'=> round(intval($object['height']*$object['scaleY'])/$config_setting['base_sy'], 2),			
					'left' 	=> round(($object['left']- $config_setting['base_l'])/$config_setting['base_sx'], 2),
					'top'  	=> round(($object['top'] - $config_setting['base_t'])/$config_setting['base_sx'], 2),
				  ];
				   break;  
			  }
		  }	
		}
		
		
		if(!isset($meta_doconf['domask'])){
		  $meta_doconf['domask'] = array();	
		}
		if(!isset($meta_doconf['domask'][$DObjFileName])){
		  $meta_doconf['domask'][$DObjFileName] = [];
		}
		
		if(count($canvas_store)){
		  $meta_doconf['domask'][$DObjFileName]['mode'] = 'edit';
		  $meta_doconf['domask'][$DObjFileName]['conf'] = $canvas_store;
		  $meta_doconf['domask'][$DObjFileName]['creater'] = $this->USER->UserID;
		  $meta_doconf['domask'][$DObjFileName]['time'] = date('Y-m-d H:i:s');
		}else{
		  if(isset($meta_doconf['domask'][$DObjFileName]['mode']) 
		     && $meta_doconf['domask'][$DObjFileName]['mode']=='edit'){
			   unset($meta_doconf['domask'][$DObjFileName]);    
		  }
		}
		
		$meta_doconf['logs'][date('Y-m-d H:i:s')] = " edited by ".$this->USER->UserID;
		
		$DB_UPD	= $this->DBLink->prepare( SQL_AdMeta::UPDATE_METADATA_DATA(array('dobj_json')));
		$DB_UPD->bindParam(':sid'   , $meta['system_id'] , PDO::PARAM_INT);	
		$DB_UPD->bindValue(':dobj_json' , json_encode($meta_doconf));
	    if( !$DB_UPD->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// final 
		$result['data']   = 1;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	  
	}
	
	
	
	
	
	
	
	/*== [ File Upload Module ] ==*/
	//name:數位檔案上傳模組
	
	
	//-- Initial Dobj Upload Initial 
	// 上傳檔案初始化，建立暫存空間，並確認資料是否重複
	// [input] : UploadData : urlencode(json_pass())  = array(folder , creater , classlv , list=>array(name, size, type, lastmdf=timestemp));
	// [input] : FILES : [array] - System _FILES Array;
	public function ADMeta_Upload_Task_Initial( $UploadData=''){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $upload_data = json_decode(rawurldecode($UploadData),true);   
	  
	  try{
		  
		if(!$upload_data || !count($upload_data)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		$upload_time_flag = date('YmdHis');  // 用來識別task_upload file
		
		//create upload temp space 
		$upload_temp_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/'.$upload_data['folder'].'/';
		if(!is_dir($upload_temp_folder)){
		  mkdir($upload_temp_folder,0777,true);	
		}
		
		// check exist file 確認檔案室否曾經上傳
		$checked  = array();
		$DB_Check  = $this->DBLink->prepare(SQL_AdMeta::CHECK_FILE_UPLOAD_LIST()); 
		
		if(is_array($upload_data['list']) && count($upload_data['list'])){  
		  foreach($upload_data['list'] as $i=>$file){
			$checked[$i] = array();
			$hashkey = md5($file['name'].$file['size'].$file['lastmdf']);
			
			$DB_Check->bindValue(':hash',$hashkey);
			$DB_Check->execute();
			
			$chk = $DB_Check->fetchAll(PDO::FETCH_ASSOC);
			
			$checked[$i]['check']  = count($chk) ? 'double' : 'accept';
		  }  
		}
		
		// return folder 
		$result['data']['folder'] = $upload_data['folder'];
		$result['data']['tmflag'] = $upload_time_flag;
		$result['data']['check']  = $checked;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	//-- Upload Photo 
	// [input] : ZongCode     : [str] zong folder : RECORD ARCHIVE...    
	// [input] : FolderCode   : [str] collection_id;
	// [input] : TimeFlag     : [str] timeflag  date(YmdHis);
	// [input] : UploadMeta : accnum:urlencode(base64encode(json_pass()))  = array(F=>V);
	// [input] : FILES : [array] - System _FILES Array;
	public function ADMeta_Uploading_Dobj( $ZongCode='', $FolderCode='' ,$TimeFlag='' , $UploadMeta='' , $FILES = array()){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  // Allowed extentions.
      $allowedExts = array("jpg","tiff","png","gif","cr2","dng","tif","raf","mp3","mp4");
      
      // Get filename.
      $temp = explode(".", $FILES["file"]["name"]);

      // Get extension.
      $extension = end($temp);
      
	  // Validate uploaded files.
	  // Do not use $_FILES["file"]["type"] as it can be easily forged.
	  $finfo = finfo_open(FILEINFO_MIME_TYPE);
	  $mime  = finfo_file($finfo, $FILES["file"]["tmp_name"]);
	  $upload_data = json_decode(base64_decode(str_replace('*','/',$UploadMeta)),true);   
	  
	  try{
		
		// 檢查參數
		if(!preg_match('/^[\w\d\_\-]+$/',$FolderCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(!preg_match('/^\d{14}$/',$TimeFlag)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
     	
		if (!in_array(strtolower($extension), $allowedExts)) {
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }	
		
        if( $FILES["file"]["error"] ){
          throw new Exception('_SYSTEM_UPLOAD_ERROR:'.$FILES["file"]["error"]);  
        }
        
		
		//紀錄上傳檔案
		$hashkey = md5($FILES["file"]['name'].$FILES["file"]['size'].$FILES["file"]['lastmdf']);
		$filetmp  = microtime(true);
		$filepath = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/'.$FolderCode.'/'.$filetmp;
		
		$DB_Regist = $this->DBLink->prepare(SQL_AdMeta::REGIST_FILE_UPLOAD_RECORD()); 
		$DB_Regist->bindValue(':utkid',	0);
		$DB_Regist->bindValue(':folder',$FolderCode);
		$DB_Regist->bindValue(':flag',	$TimeFlag);
		$DB_Regist->bindValue(':user',	$this->USER->UserID);
		$DB_Regist->bindValue(':hash',	$hashkey);
		$DB_Regist->bindValue(':store',	$filepath);
		$DB_Regist->bindValue(':saveto',_SYSTEM_DIGITAL_FILE_PATH.$ZongCode.'/');
		$DB_Regist->bindValue(':name',	$FILES["file"]['name']);
		$DB_Regist->bindValue(':size',	$FILES["file"]['size']);
		$DB_Regist->bindValue(':mime',	strtolower($FILES["file"]['type']));
		$DB_Regist->bindValue(':type',	strtolower($extension));
		$DB_Regist->bindValue(':dotag',	$upload_data['dotype']);
		$DB_Regist->bindValue(':last',	$FILES["file"]['lastmdf']);
		
		
		
		if(!$DB_Regist->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		$urno = $this->DBLink->lastInsertId();
		
		// 取得文件資料
		if(!move_uploaded_file($FILES["file"]["tmp_name"], $filepath )){
		  throw new Exception('_META_DOBJ_UPLOAD_MOVE_FAIL');		
		}
		
		// 更新上傳紀錄
		$DB_Update = $this->DBLink->prepare(SQL_AdMeta::UPDATE_FILE_UPLOAD_UPLOADED()); 
		$DB_Update->bindValue(':urno',$urno );
		$DB_Update->execute();
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	//-- Finish Digital Object Upload Task 
	// [input] : ZongCode     : [str] zong folder : RECORD ARCHIVE...     
	// [input] : FolderId     : [str] metadata.collection
	// [input] : TimeFlag     : [int] fuploadtimeflag; \d{14}
	public function ADMeta_Upload_Task_Finish($ZongCode='', $FolderId='' , $TimeFlag='' ){
	  
	  $result_key = parent::Initial_Result('queue');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		  
		// 檢查參數
		if(!preg_match('/^[\w\d\-\_]+$/',$FolderId)  ||  !preg_match('/^\d{14}$/',$TimeFlag)   ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		
		// 確認資料夾狀態
		$dobj_exist   = array();
		$folder_conf = _SYSTEM_DIGITAL_FILE_PATH.$ZongCode.'/profile/'.$FolderId.'.conf';
		if(file_exists($folder_conf)){
		  $dobj_array = json_decode(file_get_contents($folder_conf),true);	
		  if(isset($dobj_array['items'])){
		    foreach($dobj_array['items'] as $dobj ){
		  	  $dobj_exist[] = $dobj['file'];
		    }
		  }
		}
		
		
		$folder_id = $FolderId;
		$upload_flag = $TimeFlag;
		
		// 查詢新上傳檔案
		$dobj_upload = array();
		$DB_DOJ = $this->DBLink->prepare(SQL_AdMeta::SELECT_UPLOAD_OBJECT_LIST());
		$DB_DOJ->bindValue(':folder', $FolderId); 
		$DB_DOJ->bindValue(':flag'	, $upload_flag); 
		$DB_DOJ->bindValue(':user'	, $this->USER->UserID); 
		if(!$DB_DOJ->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		while( $tmp = $DB_DOJ->fetch(PDO::FETCH_ASSOC)){
		  $dobj = $tmp;
		  if( in_array($tmp['name'],$dobj_exist) ){
			$dobj['@check'] = 'duplicate'; 
		  }else{
			$dobj['@check'] = '';   
		  }
		  $dobj_upload[] = $dobj;
		}
		
		if(!count($dobj_upload)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$result['data']   = $dobj_upload;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Active Upload Object Import  讓上傳成功之檔案執行匯入
	// [input] : UploadListPaser     : [str] encode string(system_upload.urno.array)
	public function ADMeta_Process_Upload_Import( $UploadListPaser=''){
	 
	  $result_key = parent::Initial_Result('uplact');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
		
		// 處理勾選檔案
		$process_counter = 0;
		$process_list    = array();
        $uplfile_list = json_decode(base64_decode(str_replace('*','/',rawurldecode($UploadListPaser))),true);  
		
		
		//註冊匯入工作
		$DB_Task = $this->DBLink->prepare(SQL_AdMeta::REGIST_SYSTEM_TASK()); 
		$DB_Task->bindValue(':user',$this->USER->UserNO);
		$DB_Task->bindValue(':task_name',"數位檔案上傳");
		$DB_Task->bindValue(':task_type',"DOIMPORT");
		$DB_Task->bindValue(':task_num',count($uplfile_list));
		$DB_Task->bindValue(':task_done',0);
		$DB_Task->bindValue(':time_initial',date('Y-m-d H:i:s'));
		
		if(!$DB_Task->execute()){
		  throw new Exception('_TASK_INITIAL_FAIL'); 	
		}
		
		$task_id = $this->DBLink->lastInsertId(); 
		
		// 處理資料
		$DB_UPL = $this->DBLink->prepare(SQL_AdMeta::SELECT_TARGET_UPLOAD_FILE());  //查詢上傳檔案
		$DB_DEL = $this->DBLink->prepare(SQL_AdMeta::DELETE_TARGET_UPLOAD_FILE());  //標示檔案刪除 
		
		$DB_Bind = $this->DBLink->prepare(SQL_AdMeta::BIND_UPLOAD_TO_TASK());  // 將上傳資料綁定工作
		$DB_Bind->bindValue(':utkid',$task_id);
		
		foreach($uplfile_list as $urno){
			
		  $DB_UPL->bindValue(':urno',$urno);	
		  if(!$DB_UPL->execute()) continue;
          
		  $tmp = $DB_UPL->fetch(PDO::FETCH_ASSOC);	
		  $active_time = date('Y-m-d H:i:s');
		  $logs = $tmp['_logs'] ? json_decode($tmp['_logs'],true) : array();
		  
		  if(!file_exists($tmp['store'])){
			
			//檔案若不存在則標示檔案刪除
		    $logs[$active_time] = $this->USER->UserID.' upload file unfound.';
		    $DB_DEL->bindValue(':process',$active_time);
		    $DB_DEL->bindValue(':logs',json_encode($logs));
		    $DB_DEL->bindValue(':urno',$urno);	
		    if(!$DB_DEL->execute()) continue;
			 
		  }
		  
		  $logs[$active_time] = $this->USER->UserID.' regist import task:'.$task_id.'.';
		  
		  
		  $DB_Bind->bindValue(':urno',$urno); 
		  $DB_Bind->bindValue(':logs',json_encode($logs));		  
		  
		  if(!$DB_Bind->execute()) continue;
		 
		  $process_counter++;
		  $process_list[] = $urno;
		  
		}
		
		// 開啟匯入程序
		//exec(_SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJob/Job_Import_Upload_Files.php');  // 做完才結束
		pclose(popen("start /b "._SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemTasks/Task_Import_Upload_Files.php '.$task_id,"r"));  // 可以放著不管
		
		$result['data']['count'] = $process_counter;
		$result['data']['task']  = $task_id ;
		$result['data']['list']  = $process_list ;
		
		$result['action'] = true;
	
	 } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Active Upload Object Delete  刪除上傳成功之檔案
	// [input] : UploadListPaser     : [str] encode string(system_upload.urno.array)
	public function ADMeta_Process_Upload_Delete( $UploadListPaser='' ){
	 
	  $result_key = parent::Initial_Result('uplact');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
        $uplfile_list = json_decode(base64_decode(str_replace('*','/',rawurldecode($UploadListPaser))),true);  
		
		if(!count($uplfile_list)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
        // 處理勾選檔案
		$process_counter = 0;
		$process_list    = array();
		
		$DB_UPL = $this->DBLink->prepare(SQL_AdMeta::SELECT_TARGET_UPLOAD_FILE());  //查詢上傳檔案
		$DB_DEL = $this->DBLink->prepare(SQL_AdMeta::DELETE_TARGET_UPLOAD_FILE());  //標示檔案刪除 
		
		foreach($uplfile_list as $urno){
		  
		  $DB_UPL->bindValue(':urno',$urno);	
		  if(!$DB_UPL->execute()) continue;
          
		  $tmp = $DB_UPL->fetch(PDO::FETCH_ASSOC);
		  
		  //刪除暫存檔
		  if(file_exists($tmp['store'])){
			unlink($tmp['store']);
		  }
		  
		  //資料庫更新
		  $active_time = date('Y-m-d H:i:s');
		  $logs = $tmp['_logs'] ? json_decode($tmp['_logs'],true) : array();
		  $logs[$active_time] = $this->USER->UserID.' delete upload file.';
		  
		  $DB_DEL->bindValue(':process',$active_time);
		  $DB_DEL->bindValue(':logs',json_encode($logs));
		  $DB_DEL->bindValue(':urno',$urno);	
		  if(!$DB_DEL->execute()) continue;
		  
		  $process_counter++;
		  $process_list[] = $urno;
		
		}
		
		$result['data']['count'] = $process_counter;
		$result['data']['list']  = $process_list ;
		$result['action'] = true;
	     
	 } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/* SAMPLE */
	/*******************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	********************************************************************************************************
	*******************************************************************************************************/
	
	
	
	/***== [ 建檔管理模組函數 ] ==***/
	//-- Admin Built : Finish Task Work
	// [input] : taskid  :  \w\d+;
	public function ADBuilt_Finish_Work_Task( $TaskId=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^TSK\d+$/',$TaskId) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 查詢任務
		$task = array();
		$DB_TASK= $this->DBLink->prepare(SQL_AdMeta::GET_TARGET_META_DATA());
		$DB_TASK->bindValue(':id' , $TaskId);
		if( !$DB_TASK->execute() || !$task=$DB_TASK->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		if( $task['handler'] != $this->USER->UserID){
		  throw new Exception('_BUILT_TASK_HANDLER_FAIL');	
		}
		
		// 更新所有任務案件
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::FINISH_TASK_ELEMENTS());
		$DB_UPD->bindValue(':taskid' , $TaskId);
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 更新任務資料
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_TASK_STATUS());
		$DB_UPD->bindValue(':taskid' , $TaskId);
		$DB_UPD->bindValue(':status' , '_FINISH');
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行logs
		/*
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['identifier']);
		$DB_LOGS->bindValue(':method'    , 'CREATE/UPDATE/DELETE/BATCH');
		$DB_LOGS->bindValue(':source' , json_encode($source));
		$DB_LOGS->bindValue(':update' , json_encode($meta_update));
		$DB_LOGS->bindValue(':user' , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		*/
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Built : Return Task Work
	// [input] : taskid  :  \w\d+;
	public function ADBuilt_Return_Work_Task( $TaskId=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^TSK\d+$/',$TaskId) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 確定腳色權限
		if( !isset($this->USER->PermissionNow['group_roles']['R00']) && 
		   (!isset($this->USER->PermissionNow['group_roles']['R02']) || $this->USER->PermissionNow['group_roles']['R02'] <= 1 )){
		   throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		}
		
		// 查詢任務
		$task = array();
		$DB_TASK= $this->DBLink->prepare(SQL_AdMeta::GET_TARGET_META_DATA());
		$DB_TASK->bindValue(':id' , $TaskId);
		if( !$DB_TASK->execute() || !$task=$DB_TASK->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 更新任務資料
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_TASK_STATUS());
		$DB_UPD->bindValue(':taskid' , $TaskId);
		$DB_UPD->bindValue(':status' , '_EDITING');
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行logs
		/*
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['identifier']);
		$DB_LOGS->bindValue(':method'    , 'CREATE/UPDATE/DELETE/BATCH');
		$DB_LOGS->bindValue(':source' , json_encode($source));
		$DB_LOGS->bindValue(':update' , json_encode($meta_update));
		$DB_LOGS->bindValue(':user' , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		*/
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Built : Checked Task Work
	// [input] : taskid  :  \w\d+;
	public function ADBuilt_Checked_Work_Task( $TaskId=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^TSK\d+$/',$TaskId) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 確定腳色權限
		if( !isset($this->USER->PermissionNow['group_roles']['R00']) && 
		   (!isset($this->USER->PermissionNow['group_roles']['R02']) || $this->USER->PermissionNow['group_roles']['R02'] <= 1 )){
		   throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		}
		
		// 查詢任務
		$task = array();
		$DB_TASK= $this->DBLink->prepare(SQL_AdMeta::GET_TARGET_META_DATA());
		$DB_TASK->bindValue(':id' , $TaskId);
		if( !$DB_TASK->execute() || !$task=$DB_TASK->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 更新任務資料
		$DB_UPD= $this->DBLink->prepare(SQL_AdMeta::UPDATE_TASK_STATUS());
		$DB_UPD->bindValue(':taskid' , $TaskId);
		$DB_UPD->bindValue(':status' , '_CHECKED');
		if( !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// 執行logs
		/*
		$DB_LOGS	= $this->DBLink->prepare(SQL_AdMeta::LOGS_META_MODIFY());
		$DB_LOGS->bindValue(':zong' 	  , $meta['zong']);
		$DB_LOGS->bindValue(':sysid' 	  , $meta['system_id']);
		$DB_LOGS->bindValue(':identifier' , $meta['identifier']);
		$DB_LOGS->bindValue(':method'    , 'CREATE/UPDATE/DELETE/BATCH');
		$DB_LOGS->bindValue(':source' , json_encode($source));
		$DB_LOGS->bindValue(':update' , json_encode($meta_update));
		$DB_LOGS->bindValue(':user' , $this->USER->UserID);
		$DB_LOGS->bindValue(':result' , 1);
		$DB_LOGS->execute();
		*/
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Built : Download Select Tasks Elements
	// [input] : taskidstring  :  taskid;taskid;.... ;
	public function ADBuilt_Export_Work_Task($TaskIdString=''){
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d;]+$/',$TaskIdString)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		
		// 取得任務資料
		
		$targets  = explode(';',$TaskIdString);
		$exports  = array();
		$collection = array();
		
		foreach($targets as $data_id){
	      
		  $task = NULL;
		  $DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_DATA());
		  $DB_GET->bindParam(':id'   , $data_id );	
		  if( !$DB_GET->execute() || !$task = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		  }
		
		  // 確定腳色權限
		  if( $task['handler']==$this->USER->UserID ){
			
		  }elseif( isset($this->USER->PermissionNow['group_roles']['R00']) || 
		     (isset($this->USER->PermissionNow['group_roles']['R02']) && $this->USER->PermissionNow['group_roles']['R02'] > 1 )){
		  }else{
		    throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');		
		  }  
		  
		  $exports[] = $task['task_no'];
		  $collection[$task['task_no']] = array('id'=>$task['collection_id'],'name'=>$task['collection_name']);
		}
		
		if(!count($exports)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL'); 	
		}
		
		
		// 取得任務資料
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TASKS_ELEMENTS_EXPORT($exports));
		if( !$DB_GET->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$excel_records = array();
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
          
		  $meta = json_decode($tmp['meta_json'],true);
		  		  
          $record = array();
		  $record[] = $collection[$tmp['taskid']]['id'];
		  $record[] = $tmp['itemid'];
		  $record[] = $tmp['page_file_start'];
		  $record[] = $tmp['page_file_end'];
		  $record[] = $collection[$tmp['taskid']]['name'];
		  $record[] = isset($meta['description']) ? $meta['description'] : '';
		  $record[] = isset($meta['from_date']) ? $meta['from_date'] : '';
		  $record[] = isset($meta['to_date']) ? $meta['to_date'] : '';
		  $record[] = isset($meta['per_name']) ? $meta['per_name'] : '';
		  $record[] = isset($meta['place_info']) ? $meta['place_info'] : '';
		  $record[] = isset($meta['key_word']) ? $meta['key_word'] : '';
		  $record[] = isset($meta['edit_note']) ? $meta['edit_note'] : '';
		  $record[] = $tmp['_editor'];
		  $record[] = $tmp['_update'];
		  $record[] = $tmp['_estatus'];
		  $excel_records[] = $record;  	
		}
		
		// final
		$result['action'] = true;
		$result['data']['excel'][] = $excel_records;
		$result['data']['fname'] = count($exports)==1 ? 'AHAS_MetaEditor_Export_'.$task['collection_id'].'_'.date('Ymd') : 'AHAS_MetaEditor_Export_'.date('Ymd');
		$result['data']['title'] = count($exports)==1 ? $task['collection_id'] : '匯出'.count($exports).'個任務';
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result; 	 
	}
	
	
	
	
	
	//-- Finish Photo Upload Task 
	// [input] : FolderId     : [str] metadata.identifter
	// [input] : TimeFlag     : [int] fuploadtimeflag; \d{14}
	public function ADMeta_Upload_Task_Finish_Restore( $FolderId='' , $TimeFlag=''){
	  
	  $result_key = parent::Initial_Result('task');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		  
		// 檢查參數
		if(!preg_match('/^[\w\d\-\_]+$/',$FolderId)  ||  !preg_match('/^\d{14}$/',$TimeFlag)   ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$folder_id = $FolderId;
		$upload_flag = $TimeFlag;
		
		
		// 查詢新上傳檔案
		$DB_PHO = $this->DBLink->prepare(SQL_AdMeta::SELECT_UPLOAD_OBJECT_LIST());
		$DB_PHO->bindValue(':folder', $folder_id); 
		$DB_PHO->bindValue(':flag'	, $upload_flag); 
		$DB_PHO->bindValue(':user'	, $this->USER->UserID); 
		if(!$DB_PHO->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		
		// 設定變數
		$meta  = $this->Metadata;
		if(!count($meta)){
		  throw new Exception('_SYSTEM_ERROR_ACCESS_PROCESS_FAIL');	
		}
		
		$dobject_config = json_decode($meta['dobj_json'],true);
		
		
		
		$objs = $DB_PHO->fetchAll(PDO::FETCH_ASSOC);
		if(!count($objs)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$new_do_conf = array();
		
		 /* [ 處理上傳程序 ] */
		foreach($objs as $obj){
		  
		  // 1. 重新命名
		  $meta_folder =  _SYSTEM_FILE_PATH.$obj['store'];
		  
		  // 依據原始資料夾檔案數量命名
		  if(!is_dir($meta_folder)){
		    throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL'); 
		  }
		  $elements = count( array_filter(array_slice(scandir($meta_folder),2) , function($file) use($meta_folder){ return is_file(  $meta_folder.$file  ); }));  // 原檔案數量
		  
		  
		  do{
            $elements++;
			$new_file_name =  _SYSTEM_NAME_SHORT.'_'.$folder_id.'_'.str_pad($elements,4,'0',STR_PAD_LEFT).'.'.$obj['type'];
		  }while( file_exists($meta_folder.$new_file_name) );
		  
		  
		  // 2. 歸檔
		  $DB_UPLOAD = $this->DBLink->prepare(SQL_AdMeta::UPDATE_FILE_UPLOAD_PROCESSED()); 
		  
		  if(!copy( $meta_folder.'upload/'.str_pad($obj['urno'],8,'0',STR_PAD_LEFT).$obj['hash'] , $meta_folder.$new_file_name )){
            
			// 註記歸檔失敗
			$DB_UPLOAD->bindValue(':logs',"FAIL : copy upload file fail");
		    $DB_UPLOAD->bindValue(':urno',$obj['urno']);
			$DB_UPLOAD->bindValue(':archive','');
			$DB_UPLOAD->execute();
			
		    continue;
		  }
		  
		  unlink($meta_folder.'upload/'.str_pad($obj['urno'],8,'0',STR_PAD_LEFT).$obj['hash']);
		  
		  // 3. 更新狀態
		  $DB_UPLOAD->bindValue(':logs',"SUCCESS : save as ".$new_file_name);
		  $DB_UPLOAD->bindValue(':urno',$obj['urno']);
		  $DB_UPLOAD->bindValue(':archive',date('Y-m-d H:i:s'));
		  $DB_UPLOAD->execute();
		  
		  // 4. 加入原始meta do config
		  //{"thcc-hp-dng00961-0001-i.jpg":{"name":"thcc-hp-dng00961-0001-i.jpg","addr":"photo\/dng00961\/thcc-hp-dng00961-0001-i.jpg","hash":"f72ecf28","view":1,"order":0,"index":0,"exist":1,"logs":["20160911_10:34:51 inserted."]}}
		  $dobj_conf[$new_file_name] = array(
		   'name'=>$new_file_name,
		   'addr'=>$obj['store'].$new_file_name,
		   'hash'=>substr(md5($meta_folder.$new_file_name.time()),(rand(0,3)*8),8),
		   'view'=>1,
		   'order'=>99,
		   'index'=>0,
		   'exist'=>1,
		   'logs'=>array(date('Ymd_H:i:s').' '.$this->USER->UserID.' uploaded.')
		  );
		  
		  $new_do_conf[$new_file_name] = $dobj_conf[$new_file_name];
		  
		}
		
		// 5. 更新原始meta
		$DB_UPD	= $this->DBLink->prepare( SQL_AdMeta::ADMIN_META_UPDATE_META_DATA(array('dobj_json')));
		$DB_UPD->bindParam(':sid'   , $meta['system_id'] , PDO::PARAM_INT);	
		$DB_UPD->bindValue(':dobj_json' , json_encode($dobj_conf));
		$DB_UPD->execute();
		
		// 放回全域變數
		$this->Metadata['dobj_json'] = json_encode($dobj_conf);
		
		
		// 開啟匯入程序 - 外部處理sample 
		//exec(_SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJob/Job_Import_Upload_Files.php');  // 做完才結束
		//pclose(popen("start /b "._SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJob/Job_Import_Upload_Files.php '.$task_id,"r"));  // 可以放著不管
		$result['data']    = $new_do_conf;
		
		$result['action'] = true;
		
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	//-- Upload Batch File  // 上傳批次處理檔案
	// [input] : MetaClass : 001/002
	// [input] : FILES : [array] - System _FILES Array;
	public function ADMeta_Upload_Batch_File( $MetaClass='' , $FILES = array()){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  // Allowed extentions.
      $allowedExts = array("xls","xlsx");
       
	  try{
		
		// Get filename.
        $temp = explode(".", $FILES["file"]["name"]);
        // Get extension.
        $extension = end($temp);
		
		// 檢查上傳檔案資訊
		if (!in_array(strtolower($extension), $allowedExts)) {
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }	
		
        if( $FILES["file"]["error"] ){
          throw new Exception('_SYSTEM_UPLOAD_ERROR:'.$FILES["file"]["error"]);  
        }
		
	    // Validate uploaded files.
	    // Do not use $_FILES["file"]["type"] as it can be easily forged.
	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
	    $mime  = finfo_file($finfo, $FILES["file"]["tmp_name"]);
		
		$upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
		$upload_batch_key  = $MetaClass.'-'.date('YmdHis');
		$upload_batch_file = $upload_batch_key.'.'.$extension;
		
		
		// 轉存位置
		if(!is_dir($upload_folder)) mkdir($upload_folder,0777,true);
		move_uploaded_file($FILES["file"]["tmp_name"],$upload_folder.$upload_batch_file);
		
		// 檢查檔案
		if(!$new_batch_size = filesize($upload_folder.$upload_batch_file)){
		  throw new Exception('_META_BATCH_MOVE_FAIL');	
		}
		
		$result['data']['file'] = $FILES["file"]["name"];
		$result['data']['save'] = $upload_batch_file;
		$result['data']['size'] = $new_batch_size;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Check Upload Excel File  // 檢查批次處理檔案
	// [input] : UploadFile : PUBLICATION-date()
	public function ADMeta_Check_Batch_File( $UploadFile='' ){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  function colnum2code($colnumber){ //COL欄位轉換
		/*range A - ZZ */
		$col_range = range('A','Z');
		$col_index = intval($colnumber);
		$col_code  = '';
		if(intval($col_index/26)){
		  $col_code = $col_range[intval($col_index/26)-1];
		  $col_code .= ($col_index%26) ? $col_range[($col_index%26-1)]:'A'; 
		}else{
		  $col_code	= $col_range[$col_index];
		}
		return $col_code;
	  }
	  
	  try{
	    
		$upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
		
		if(!file_exists($upload_folder.$UploadFile)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$objReader = PHPExcel_IOFactory::createReaderForFile($upload_folder.$UploadFile);
	    $objPHPExcel = $objReader->load($upload_folder.$UploadFile);
		$main_sheet = $objPHPExcel->setActiveSheetIndex(0);
	    
		
		//取得處理參數
		$meta_class   = $main_sheet->getCellByColumnAndRow(1,1)->getValue();
		$meta_version = $main_sheet->getCellByColumnAndRow(1,2)->getValue();
		$meta_key     = $main_sheet->getCellByColumnAndRow(1,3)->getValue(); 
	    
		$frow_start   = 5;
		
		// 設定資料集相關
		$fformat = [0=>[],1=>[]];
		$freference[0]['level'] = [];
		$zclass = [];
		
		// 取得全宗分類資料
		$DB_CLASS = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_CLASS());
		$DB_CLASS->bindParam(':zclass', $meta_class );	
		if( !$DB_CLASS->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$zclass =  $DB_CLASS->fetch(PDO::FETCH_ASSOC);
			
		// 建構全宗分類
		$DB_ZLV = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_LEVEL());
		function builtzongclass($dblink,$bindno,$znamepre,$zlevel){
			$dblink->bindParam(':mcno', $bindno );	
			$dblink->execute();          
			$lvs = $dblink->fetchAll(PDO::FETCH_ASSOC);
			  
			if(count($lvs)){
			  foreach($lvs as $lv){
				$zlevel[$lv['class_code']] = $znamepre.'/'.$lv['class_name'];	
				$zlevel = $zlevel+builtzongclass($dblink,$lv['mcno'],$znamepre.'/'.$lv['class_name'],$zlevel);    
			  }  
			}
			return  $zlevel; 
		}
			
		if(isset($zclass['mcno'])){
		  $class_level = builtzongclass($DB_ZLV,$zclass['mcno'],$zclass['class_name'],[]);
		  if(is_array($class_level)&&count($class_level)){
			$freference[0]['level'] = array_keys($class_level);	
			$freference[0]['series'] = array_values($class_level);	
		  }
		}
		
		
		switch($meta_class){
		    case 'relic':		
			  // 取得卷欄位檢測參考表  meta_format
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digiarchive');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat[0][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference[0][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  
			  // 取得件欄位檢測參考表  meta_format 
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digielement');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat[1][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference[1][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  break;
		      
			default:
			  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
			  break;
		}
		
		//取得當前資料版本 - by匯出key
		$meta_currency = [];
		
		$DB_MTNOW = $this->DBLink->prepare( SQL_AdMeta::GET_EXPORT_CURRENT_META());
	    $DB_MTNOW->bindValue(':key',$meta_key);
	    $DB_MTNOW->execute();
	    while($tmp = $DB_MTNOW->fetch(PDO::FETCH_ASSOC)){
		  if(!isset($meta_currency[$tmp['class']])) $meta_currency[$tmp['class']]=['volume'=>[],'element'=>[]];
		  $source = json_decode($tmp['source_json'],true);
	      if($tmp['data_type']=='collection'){
			$meta_currency[$tmp['class']]['volume'][$tmp['applyindex']] = $source['collection'];
		  }else{
			$meta_currency[$tmp['class']]['element'][$tmp['applyindex']] = $source['element'];  
		  }
		}
		
		
		//取得匯出當時的資料狀態
		$meta_whenexport = [];
		$export_store = _SYSTEM_DIGITAL_FILE_PATH.'METADATA/export/'.$meta_key.'.json';
		if(file_exists($export_store)){
		  $meta_whenexport = json_decode(file_get_contents($export_store),true);	
		}
		
		
		//掃描excel檔案
		$sheet_count = $objPHPExcel->getSheetCount();
		$sheet_check = [
		  'insert'=>[],
		  'delete'=>[],
		  'conflict'=>[],
		  'modify'=>[],
		  'fail'=>[],
		  'total'=>0
		];
		
		$sheet_type_map = [0=>'volume',1=>'element']; 
		
		for($shindex=0;$shindex<2;$shindex++){
		  
		  $meta_sheet = $objPHPExcel->setActiveSheetIndex($shindex);
		  $meta_sheet_name = $objPHPExcel->getActiveSheet()->getTitle();  
		  
		  // get meta field
		  $col = 0;
		  $meta_fileds   = [];
		  $meta_col2field = [];
		  
		  $fcounter = 0;
		  while( $f = trim($meta_sheet->getCellByColumnAndRow($col,$frow_start)->getValue())){
            
			$meta_fileds[$f]=[
		      'index'	=> $col,
			  'field'	=> $f,
			  'name'	=> trim($meta_sheet->getCellByColumnAndRow($col,($frow_start-1))->getValue()),
		      'format'	=> (isset($fformat[$shindex][$f]) ? $fformat[$shindex][$f] : []),
			  'refer' 	=> (isset($freference[$shindex][$f]) ? $freference[$shindex][$f] : [])
		    ];
			$meta_col2field[$col] = $f;
			$col++;
		    if($fcounter++ > 100 ) break; // 防止迴圈
		  }
		  
		  // get meta sheet
		  $mcol_start   = 0;
		  $mrow_start   = 6;
		  $mcol_finish  = count($meta_fileds);
		  
		  $frow = $mrow_start; 
		  $read_exit_fleg = 0;
		  $meta_records = [];
		  
		  do{
			
			$meta_xlsread = [];  //從excel中讀取之資料
			$meta_linkfld = '';  //跨資料版本之索引號碼 
			
			for($fcol=$mcol_start ; $fcol<$mcol_finish ; $fcol++ ){
			  $cell = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getValue());  
		      if(substr($cell,0,1)=='='){
				$cell = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getFormattedValue());
			  }
			  $meta_xlsread[$meta_col2field[$fcol]] = $cell;
			  if($meta_col2field[$fcol] == $this->SourceTableIndexFild){
				$meta_linkfld = $cell; 
			  }
			} 
		    
			if(!count(array_filter($meta_xlsread))){  // 若為空值
			  $read_exit_fleg++;
			
			}else{
			  
			  // 取得其他參照版本
			  $meta_db_record = isset($meta_currency[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld]) ? $meta_currency[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld] :[];
			  $meta_ep_record = isset($meta_whenexport[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld]) ? $meta_whenexport[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld] :[];
				
				
		      //檢測資料
			  foreach($meta_xlsread as $mf => $mv){  
				  
				  if(!isset($meta_fileds[$mf])) continue;
				  
				  $format_pass = true;  
				  $notconflict = true;  
				  
				  $cellid  = [colnum2code($meta_fileds[$mf]['index']),$frow];
				  $checker = isset($meta_fileds[$mf]['format']) ? $meta_fileds[$mf]['format'] : [];
				  	
				  //check if auto new
				  if($checker['autonew']){
					if($mv=='+'){
					  $sheet_check['insert'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => '第 '.$frow.' 行',  //colnum2code($meta_fileds[$mf]['index']).':'.$frow
						'descrip' => join(', ',array_values($meta_xlsread)),
					  ];  
					}else if($mv=='-'){
					  $sheet_check['delete'][] = [
					    'cellid'  => '第 '.$frow.' 行',  //colnum2code($meta_fileds[$mf]['index']).':'.$frow
					  ];    
					}
			      }
				  
				  //check if empty
				  if($checker['nessary'] && $mv=='' ){
					$sheet_check['fail'][] = [
					  'sheet'   => $meta_sheet_name,
					  'cellid'  => join(':',$cellid),
					  'column'  => $meta_fileds[$mf]['name'],
					  'content' => '',
					  'descrip' => '此欄位不可為空',
					];
					$format_pass = false;
				  
				  }else if($checker['fromsys'] && isset($freference[$shindex][$mf])){
					//check from system
					$valrefer = $freference[$shindex][$mf];  
					if(!in_array($mv,$valrefer)){
					  $sheet_check['fail'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $mv,
					    'descrip' => '資料不合法，請使用系統設定值',
					  ];
					  $format_pass = false;
					}  
			      }else{
					
					switch($checker['module']){
						case 'R': if(!preg_match($checker['pattern'],$mv)){ $format_pass=false; } break;
						case 'V': if( $checker['pattern']!=$mv){ $format_pass=false;} break;
						case 'S':	if( $mv!='' && !in_array($mv,explode(';',$checker['pattern']))){ $format_pass=false; } break;				  
						default: break;	
					}
					if(!$format_pass){
					  $sheet_check['fail'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $mv,
					    'descrip' => '格式錯誤'.($checker['descrip'] ? ' : '.$checker['descrip'] : ''),
					  ];
					}
				  }
				  
				  
				  // 檢視是否修改
				  if(isset($meta_ep_record[$mf]) && $meta_ep_record[$mf] != $mv){
					$sheet_check['modify'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $mv,
					    'descrip' => '資料修改 : '.$mv,
					];
				  }
				  
				  // 檢視是否碰撞  目前資料版本已與匯出時不同
				  if(isset($meta_db_record[$mf]) && isset($meta_ep_record[$mf]) && $meta_db_record[$mf] != $meta_ep_record[$mf]){
					$sheet_check['conflict'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $meta_db_record[$mf],
					    'descrip' => '資料已變更 : '.$meta_db_record['_timeupdate'].'由'.$meta_db_record['_userupdate'].'修改',
					];
					$notconflict = false;
				  }
				  
				  //錯誤標示於excel
			      $cell_style['fill'] = array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'ff4500')
				  );
			      if(!$format_pass){
					$meta_sheet->getStyle(join('',$cellid).':'.join('',$cellid))->applyFromArray($cell_style);	  
				  }
				  
				  //衝突標示excel
			      $cell_style['fill'] = array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => '888888')
				  );
			      if(!$notconflict){
					$meta_sheet->getStyle(join('',$cellid).':'.join('',$cellid))->applyFromArray($cell_style);	  
				  }
				  
				  
				  
			  }
			  $sheet_check['total']++;
			}
			$frow++;
		  }while($read_exit_fleg < 10);
		  
		}
		
		$excel_file_name = preg_replace('/(\.xls)/','-C\\1',$UploadFile);
		$objPHPExcel->setActiveSheetIndex(0);
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($upload_folder.$excel_file_name); 
		unset($objPHPExcel);
		
		// final
		$result['action'] = true;
		$result['data']['license'] = count($meta_currency) ? 1 : 0;
		$result['data']['check']   = $sheet_check;
		$result['data']['fname']   = $excel_file_name;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Prepare Revise File to User  // 準備檢驗檔案提供使用者下載
	// [input] : ReviseFile :
	public function ADMeta_Prepare_Batch_Revise( $ReviseFile='' ){
	  
	  $result_key = parent::Initial_Result('revise');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	     
        $upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
         
		if(!file_exists($upload_folder.$ReviseFile)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		$file_path   = _SYSTEM_USER_PATH.$this->USER->UserID.'/';
		$file_revise = _SYSTEM_NAME_SHORT.'_alert_'.date('Ymd').'.xlsx';
		
		copy($upload_folder.$ReviseFile,$file_path.$file_revise);
		
		// final
		$result['action'] = true;
		$result['data']['fname']   = pathinfo($file_path.$file_revise,PATHINFO_FILENAME );
		$result['data']['date']    = date('Y-m-d H:i:s');
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
    // !!!***尚缺功能部分***!!!
	// 更新修正  ( 屏除錯誤紀錄 )
	// 下載  1.錯誤資料 2.下載全部範圍(原檢測資料範圍)
	// 快速取代工具
	// 檢查 process
	// 更新 process
	
	
	//-- Check Upload Excel File  // 檢查批次處理檔案
	// [input] : UploadFile : PUBLICATION-date()-C
	public function ADMeta_Update_Batch_Revise( $UploadFile='' ){
	  
	  function colnum2code($colnumber){ //COL欄位轉換
		/*range A - ZZ */
		$col_range = range('A','Z');
		$col_index = intval($colnumber);
		$col_code  = '';
		if(intval($col_index/26)){
		  $col_code = $col_range[intval($col_index/26)-1];
		  $col_code .= ($col_index%26) ? $col_range[($col_index%26-1)]:'A'; 
		}else{
		  $col_code	= $col_range[$col_index];
		}
		return $col_code;
	  }
	  
	  $result_key = parent::Initial_Result('batch');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		$upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
		
		if(!file_exists($upload_folder.$UploadFile)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$objReader = PHPExcel_IOFactory::createReaderForFile($upload_folder.$UploadFile);
	    $objPHPExcel = $objReader->load($upload_folder.$UploadFile);
		$main_sheet = $objPHPExcel->setActiveSheetIndex(0);
	    
		
		//取得處理參數
		$meta_class   = $main_sheet->getCellByColumnAndRow(1,1)->getValue();
		$meta_version = $main_sheet->getCellByColumnAndRow(1,2)->getValue();
		$meta_key     = $main_sheet->getCellByColumnAndRow(1,3)->getValue(); 
	    
		$frow_start   = 5;
		$update_counter = 0;
		
		
		//掃描excel檔案
		$sheet_count = $objPHPExcel->getSheetCount();
		
		
		$sheet_type_map = [0=>'volume',1=>'element']; 
		
		for($shindex=0;$shindex<2;$shindex++){
		  
		  $meta_sheet = $objPHPExcel->setActiveSheetIndex($shindex);
		  $meta_sheet_name = $objPHPExcel->getActiveSheet()->getTitle();  
		  
		  // get meta field
		  $col = 0;
		  $meta_col2field = [];
		  
		  $fcounter = 0;
		  while( $f = trim($meta_sheet->getCellByColumnAndRow($col,$frow_start)->getValue())){
            $meta_col2field[$col] = $f;
			$col++;
		    if($fcounter++ > 100 ) break; // 防止迴圈
		  }
		  
		  // get meta sheet
		  $mcol_start   = 0;
		  $mrow_start   = 6;
		  $mcol_finish  = count($meta_col2field);
		  
		  $frow = $mrow_start; 
		  $read_exit_fleg = 0;
		  $meta_records = [];
		  
		  do{
			
			$meta_xlsread = [];  //從excel中讀取之資料
			$meta_linkfld = '';  //跨資料版本之索引號碼 
			
			$meta_hasfail = false;
			$meta_update_field_header = 'META-'.strtoupper(substr($sheet_type_map[$shindex],0,1)).'-';
			for($fcol=$mcol_start ; $fcol<$mcol_finish ; $fcol++ ){
			  
			  $cellid  = [colnum2code($fcol),$frow];
			  $cell    = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getValue());  
		      if(substr($cell,0,1)=='='){
				$cell = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getFormattedValue());
			  }
			  if($meta_col2field[$fcol] == $this->SourceTableIndexFild){
				$meta_linkfld = $cell; 
			  }
			  $cell_color = strtolower($meta_sheet->getStyle(join('',$cellid).':'.join('',$cellid))->getFill()->getStartColor()->getRGB());
			  
			  if(!$meta_hasfail && ($cell_color=='ff4500' || $cell_color=='888888') ){  //ff4500 格式有誤  //888888版本不同
				$meta_hasfail = true;  
			  }
			  
			  if($cell_color=='7f7f7f' || $cell_color=='eeee8aa' ){  continue; }  // 系統資料不可修改
			  
			  // 準備更新格式  'META-V-{field}'
			  $update_field = $meta_update_field_header.$meta_col2field[$fcol];
			  $meta_xlsread[$update_field] = $cell;
			  
			} 
			
			if(!count(array_filter($meta_xlsread))){  // 若為空值
			  $read_exit_fleg++;
			}else if(!$meta_hasfail && $meta_linkfld){
			  
			  //更新資料
			  $data_modify = rawurlencode(str_replace('/','*',base64_encode(json_encode($meta_xlsread)))); 
			  
			  if($sheet_type_map[$shindex]=='volume'){
				$update = self::ADBuilt_Save_Volume_Meta( $meta_linkfld , $data_modify);   
			  }else{
				$update = self::ADBuilt_Save_Element_Data( '', $meta_linkfld , $data_modify);    
			  }
			  if($update['action']) $update_counter++; 
			  unset($this->ModelResult['save']); 
			}
			
			$frow++;
		  }while($read_exit_fleg < 10);  
		}
		unset($objPHPExcel);
		
		// final
		$result['action'] = true;
	    $result['data']['update'] = $update_counter;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Admin Meta Export Select Renew Version
	// [input] : UploadFile ;
	public function ADMeta_Renew_Batch_Select($UploadFile){
	  
	  function colnum2code($colnumber){ //COL欄位轉換
		/*range A - ZZ */
		$col_range = range('A','Z');
		$col_index = intval($colnumber);
		$col_code  = '';
		if(intval($col_index/26)){
		  $col_code = $col_range[intval($col_index/26)-1];
		  $col_code .= ($col_index%26) ? $col_range[($col_index%26-1)]:'A'; 
		}else{
		  $col_code	= $col_range[$col_index];
		}
		return $col_code;
	  }
		
	  $result_key = parent::Initial_Result('batch');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		$upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
		
		if(!file_exists($upload_folder.$UploadFile)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$objReader = PHPExcel_IOFactory::createReaderForFile($upload_folder.$UploadFile);
	    $objPHPExcel = $objReader->load($upload_folder.$UploadFile);
		$main_sheet = $objPHPExcel->setActiveSheetIndex(0);
	    
		//取得處理參數
		$meta_class   = $main_sheet->getCellByColumnAndRow(1,1)->getValue();
		$meta_version = $main_sheet->getCellByColumnAndRow(1,2)->getValue();
		$meta_key     = $main_sheet->getCellByColumnAndRow(1,3)->getValue(); 
	    
		//取得當前資料版本 - by匯出key
		$meta_currency = [];
		$data_batch_counter = 0;
		
		//== 註冊匯出序號
		$export_key = md5($this->USER->UserID.':'.microtime().':'.$UploadFile);
		$DB_LOGS = $this->DBLink->prepare( SQL_AdMeta::LOGS_META_EXPORT());
		$DB_MTNOW = $this->DBLink->prepare( SQL_AdMeta::GET_EXPORT_CURRENT_META());
	    $DB_MTNOW->bindValue(':key',$meta_key);
	    $DB_MTNOW->execute();
	    while($tmp = $DB_MTNOW->fetch(PDO::FETCH_ASSOC)){
		  if(!isset($meta_currency[$tmp['class']])) $meta_currency[$tmp['class']]=['volume'=>[],'element'=>[]];
		  $source = json_decode($tmp['source_json'],true);
	      if($tmp['data_type']=='collection'){
			$meta_currency[$tmp['class']]['volume'][$tmp['applyindex']] = $source['collection'];
		    $meta_version = $source['collection']['_timeupdate'];
		  }else{
			$meta_currency[$tmp['class']]['element'][$tmp['applyindex']] = $source['element']; 
            $meta_version = $source['element']['_timeupdate'];			
		  }
		
		  $data_batch_counter++;
		  // 註冊匯出紀錄
		  $DB_LOGS->bindValue(':exportkey',$export_key);
		  $DB_LOGS->bindValue(':system_id',$tmp['system_id']);
		  $DB_LOGS->bindValue(':meta_version',$meta_version);
		  $DB_LOGS->bindValue(':user',$this->USER->UserID);
		  $DB_LOGS->execute();
		  
		}
		
		//儲存資料匯出版本控制檔
		$export_store = _SYSTEM_DIGITAL_FILE_PATH.'METADATA/export/'.$export_key.'.json';
		file_put_contents($export_store,json_encode($meta_currency,JSON_UNESCAPED_UNICODE));
		
		
		//== 設定匯出資料格式 ==
		$freference = ['volume'=>[],'element'=>[]];  // 資料匯出參考
		$fformat = [];  // 欄位檢測 
		
		foreach($meta_currency as $sheet=>$data_export ){	
		  
		  // 取得 CLASS ZONGS
		  $freference['volume']['zong'] = [];
		  $DB_ZONG = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_INFO());
		  $DB_ZONG->bindParam(':zclass', $sheet );	
		  if( !$DB_ZONG->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  while($tmp = $DB_ZONG->fetch(PDO::FETCH_ASSOC)){
			$freference['volume']['zong'][$tmp['zid']] = $tmp['zname'];	
		  }
			
		  // 取得全宗分類資料
		  $freference['volume']['level'] = [];
		  $zclass = [];
		  $DB_CLASS = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_CLASS());
		  $DB_CLASS->bindParam(':zclass', $sheet );	
		  if( !$DB_CLASS->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  $zclass =  $DB_CLASS->fetch(PDO::FETCH_ASSOC);
			
		  // 建構全宗分類
		  $DB_ZLV = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_LEVEL());
		  function builtzongclass($dblink,$bindno,$znamepre,$zlevel){
			$dblink->bindParam(':mcno', $bindno );	
			$dblink->execute();          
			$lvs = $dblink->fetchAll(PDO::FETCH_ASSOC);
			  
			if(count($lvs)){
			  foreach($lvs as $lv){
				$zlevel[$lv['class_code']] = $znamepre.'/'.$lv['class_name'];	
				$zlevel = $zlevel+builtzongclass($dblink,$lv['mcno'],$znamepre.'/'.$lv['class_name'],$zlevel);    
			  }  
			}
			return  $zlevel; 
		  }
			
		  if(isset($zclass['mcno'])){
			$freference['volume']['level'] = builtzongclass($DB_ZLV,$zclass['mcno'],$zclass['class_name'],[]);	
		  }
		  
		  // 設定資料集相關
		  switch($sheet){
		    case 'relic':		
			  
			  $excel_template = 'template_btm_source_metadata.xlsx'; 
			  
			  // 取得卷欄位檢測參考表  meta_format
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digiarchvie');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat['volume'][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference['volume'][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  
			  // 取得件欄位檢測參考表  meta_format 
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digielement');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat['element'][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference['element'][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  
			  break;
		    
			default:
			  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
			  break;
		  }
		  
		  
		  //php excel initial
		  $objReader = PHPExcel_IOFactory::createReader('Excel2007');
		  $objPHPExcel = $objReader->load(_SYSTEM_ROOT_PATH.'mvc/templates/'.$excel_template);
		    
          $ref_col = 0;
		  $refer_colindex = [];
		    		   
		   
		  //填入入資料
		  foreach($data_export as $d_type => $data_list){
			
			// 設定 excel sheet  
			$col = 0 ;
			$row_start = 6 ;
			$active_sheet = null;
			
			if($d_type == 'volume' ){  
			  $active_sheet = $objPHPExcel->setActiveSheetIndex(0);
		      $row_max   = $row_start+count($data_list);
			}else{
			  $active_sheet = $objPHPExcel->setActiveSheetIndex(1);	
			}
		    
			//取得excel與DB欄位位置對應
			$col = 0; $row=5;    // 系統預設excel格式，第4行為欄位ID
			$dbf2colindex=['d2x'=>[],'x2d'=>[]];    // db欄位對應xls row 位置  
			while($field = trim($active_sheet->getCellByColumnAndRow($col,$row))){
			  $col_now = $col;
			  $col++;
			  if($field=='' || $col > 100 ) break;
			  if(!isset($fformat[$d_type][$field])) continue;  
			  $dbf2colindex['d2x'][$field] = $col_now;
			  $dbf2colindex['x2d'][$col_now] = $field;
			}
			
			// 填入內容
			$row = $row_start;  
			foreach( $data_list as $data){
				$col = 0;
				foreach($data as $f=>$v){
				  if( $f=='class') continue;
				  if(preg_match('/^_/',$f)) continue; 
				  if(is_array($v)) $v = join(';',$v);
				  if(!trim($active_sheet->getCellByColumnAndRow($col,5))) break;
				  $active_sheet->getCellByColumnAndRow($dbf2colindex['d2x'][$f], $row)->setValueExplicit($v, PHPExcel_Cell_DataType::TYPE_STRING);  	
				  $col++; 
				}
				$row++;
			}
			
			// 設定顯示樣式
		    foreach($dbf2colindex['d2x'] as $dbf => $xlscol){
				$col_code  = colnum2code($xlscol);  
				
			    $cell_style = array();
			    
				// index
				if(isset($fformat[$d_type][$dbf]['autonew']) && $fformat[$d_type][$dbf]['autonew']) continue;
				
				
				// 必填
				if(isset($fformat[$d_type][$dbf]['nessary']) && $fformat[$d_type][$dbf]['nessary'] ){
					$cell_style['borders'] = array(
								'allborders' => array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
									'color' => array('rgb' => '4682b4')
								)
							);		
				}
			    
				// 系統生成
				if(isset($fformat[$d_type][$dbf]['fromsys']) && $fformat[$d_type][$dbf]['fromsys'] ){
					$cell_style['fill'] = array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'eee8aa')
							    );
				}
				
				if(count($cell_style)){
				  $active_sheet->getStyle($col_code.$row_start.':'.$col_code.($row_max+100))->applyFromArray($cell_style);	
				}
		    }// endof style set
			
			
		    // 放入參考資料
		    $reference_sheet = $objPHPExcel->setActiveSheetIndexByName('REFERENCE'); 
			$refer = $freference[$d_type];
			
			foreach($refer as $rf => $rset){
				$row = 3;
				if($rf=='zong'){        // 特殊欄位參考
				  $refer_colindex['zong'] = ['index'=>$ref_col,'set'=>array_keys($refer[$rf])];
				  $refer_colindex['fonds'] = ['index'=>$ref_col+1,'set'=>array_values($refer[$rf])];
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['zong']['index'], 2)->setValueExplicit('zong', PHPExcel_Cell_DataType::TYPE_STRING);  
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['fonds']['index'], 2)->setValueExplicit('fonds', PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($refer_colindex['zong']['index'], $row)->setValueExplicit($rid, PHPExcel_Cell_DataType::TYPE_STRING);  
					$reference_sheet->getCellByColumnAndRow($refer_colindex['fonds']['index'], $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				  $ref_col+=1;
				}else if($rf=='level'){
				  $refer_colindex['level'] = ['index'=>$ref_col,'set'=>array_keys($refer[$rf])];
				  $refer_colindex['series'] = ['index'=>$ref_col+1,'set'=>array_values($refer[$rf])];
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['level']['index'], 2)->setValueExplicit('level', PHPExcel_Cell_DataType::TYPE_STRING);  
				  $reference_sheet->getCellByColumnAndRow($refer_colindex['series']['index'], 2)->setValueExplicit('series', PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($refer_colindex['level']['index'], $row)->setValueExplicit($rid, PHPExcel_Cell_DataType::TYPE_STRING);  
					$reference_sheet->getCellByColumnAndRow($refer_colindex['series']['index'], $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				  $ref_col+=1;
				}else{  				// 普通欄位參考  			
				  $refer_colindex[$rf] = ['index'=>$ref_col,'set'=>$rset];
				  $reference_sheet->getCellByColumnAndRow($ref_col, 2)->setValueExplicit($rf, PHPExcel_Cell_DataType::TYPE_STRING);  
				  foreach($rset as $rid => $rvalue){
					$reference_sheet->getCellByColumnAndRow($ref_col, $row)->setValueExplicit($rvalue, PHPExcel_Cell_DataType::TYPE_STRING);  
					$row++;
				  }
				}
                $ref_col++;
				
			}// end of data insert
			
			
			// 設定參考
			// 設訂欄位參考選單與對應
			foreach($dbf2colindex['d2x'] as $field => $data_col){
				  
				if(!isset($refer_colindex[$field])) continue;
				  
				$refer_col_code  = colnum2code($refer_colindex[$field]['index']);  // 參考欄位COL代號
				  
				$objValidation01 = $active_sheet->getCellByColumnAndRow($data_col,$row_start)->getDataValidation();
				$objValidation01->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
				$objValidation01->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
				$objValidation01->setAllowBlank(false);
				$objValidation01->setShowDropDown(true);
				$objValidation01->setFormula1('REFERENCE!$'.$refer_col_code.'$3:$'.$refer_col_code.'$'.(3+count($refer_colindex[$field]['set'])));
				
				for($i=($row_start) ; $i<$row_max ; $i++){
					$active_sheet->getCellByColumnAndRow($data_col,$i)->setDataValidation(clone $objValidation01);
					if($field == 'fonds' || $field == 'series'){
					  
					  $col_main = colnum2code(($dbf2colindex['d2x'][$field]));	 		// 設定連動之欄位COL代號
					  $col_rele = colnum2code(($dbf2colindex['d2x'][$field]-1));	 		// 設定連動之欄位COL代號
					  $col_search_s = colnum2code(($refer_colindex[$field]['index']-1)); // 連動參考起始欄位 
					  $col_search_e = colnum2code($refer_colindex[$field]['index']);     // 連動參考結束欄位
					  
					  $row_refer_s = 3;
					  $row_refer_e = ($row_refer_s+count($refer_colindex[$field]['set']));
					  
					  // 正向連動 查A回B
					  //$active_sheet->setCellValue($col_rele.$i,'=VLOOKUP('.$col_main.$i.',REFERENCE!'.$col_search_s.'3:'.$col_search_e.(3+count($refer_colindex[$field]['set'])).',1,FALSE)');
					
					  // 反向連動 查B回A
					  $active_sheet->setCellValue($col_rele.$i,'=INDEX(REFERENCE!$'.$col_search_s.'$'.$row_refer_s.':$'.$col_search_s.'$'.$row_refer_e .',MATCH('.$col_main.$i.',REFERENCE!$'.$col_search_e.'$'.$row_refer_s.':$'.$col_search_e.'$'.$row_refer_e.',0))');
					
					}
				}
			}//end of active refernece  
		  }
		}
		
		// 設定序號
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 2)->setValueExplicit(date('Y-m-d H:i:s'), PHPExcel_Cell_DataType::TYPE_STRING);  	
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 3)->setValueExplicit($export_key, PHPExcel_Cell_DataType::TYPE_STRING);  	
		
        $excel_file_name =  _SYSTEM_NAME_SHORT.'_renew_'.date('Ymd');
		$objPHPExcel->setActiveSheetIndex(0);
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$excel_file_name.'.xlsx'); 
		unset($objPHPExcel);
		
		// final
		$result['data']['fname']   = $excel_file_name;
		$result['data']['count']   = $data_batch_counter;
		
		$result['action'] = true;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Meta Export Upload Meta Fail Part // 取得上傳資料有錯誤部分
	// [input] : UploadFile ;
	public function ADMeta_Select_Batch_Detect($UploadFile){
	  
	  $result_key = parent::Initial_Result('batch');
	  $result  = &$this->ModelResult[$result_key];
	  
	  function colnum2code($colnumber){ //COL欄位轉換
		/*range A - ZZ */
		$col_range = range('A','Z');
		$col_index = intval($colnumber);
		$col_code  = '';
		if(intval($col_index/26)){
		  $col_code = $col_range[intval($col_index/26)-1];
		  $col_code .= ($col_index%26) ? $col_range[($col_index%26-1)]:'A'; 
		}else{
		  $col_code	= $col_range[$col_index];
		}
		return $col_code;
	  }
	  
	  try{
	    
		$upload_folder = _SYSTEM_DIGITAL_FILE_PATH.'UPLOAD/'.$this->USER->UserID.'/';
		
		if(!file_exists($upload_folder.$UploadFile)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$objReader = PHPExcel_IOFactory::createReaderForFile($upload_folder.$UploadFile);
	    $objPHPExcel = $objReader->load($upload_folder.$UploadFile);
		$main_sheet = $objPHPExcel->setActiveSheetIndex(0);
	    
		
		//取得處理參數
		$meta_class   = $main_sheet->getCellByColumnAndRow(1,1)->getValue();
		$meta_version = $main_sheet->getCellByColumnAndRow(1,2)->getValue();
		$meta_key     = $main_sheet->getCellByColumnAndRow(1,3)->getValue(); 
	    
		$frow_start   = 5;
		
		// 設定資料集相關
		$fformat = [0=>[],1=>[]];
		switch($meta_class){
		    case 'relic':		
			  // 取得卷欄位檢測參考表  meta_format
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digiarchive');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat[0][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference[0][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  
			  // 取得件欄位檢測參考表  meta_format 
			  $DB_MTINFO = $this->DBLink->prepare( SQL_AdMeta::GET_DB_TABL_FORMAT());
			  $DB_MTINFO->bindValue(':dbtable','source_digielement');
			  $DB_MTINFO->execute();
			  while($field = $DB_MTINFO->fetch(PDO::FETCH_ASSOC)){
				$fformat[1][$field['dbcolumn']] = $field;
				if($field['module']=='S' && $field['pattern']){
                  $freference[1][$field['dbcolumn']] = explode(';',$field['pattern']);   					
				}
			  }
			  
			  break;
		    
			 
			
			default:
			  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
			  break;
		}
		
		// 取得全宗分類資料
		$freference[0]['level'] = [];
		$zclass = [];
		$DB_CLASS = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_CLASS());
		$DB_CLASS->bindParam(':zclass', $meta_class );	
		if( !$DB_CLASS->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$zclass =  $DB_CLASS->fetch(PDO::FETCH_ASSOC);
			
		// 建構全宗分類
		$DB_ZLV = $this->DBLink->prepare( SQL_AdMeta::GET_ZONG_LEVEL());
		function builtzongclass($dblink,$bindno,$znamepre,$zlevel){
			$dblink->bindParam(':mcno', $bindno );	
			$dblink->execute();          
			$lvs = $dblink->fetchAll(PDO::FETCH_ASSOC);
			  
			if(count($lvs)){
			  foreach($lvs as $lv){
				$zlevel[$lv['class_code']] = $znamepre.'/'.$lv['class_name'];	
				$zlevel = $zlevel+builtzongclass($dblink,$lv['mcno'],$znamepre.'/'.$lv['class_name'],$zlevel);    
			  }  
			}
			return  $zlevel; 
		}
			
		if(isset($zclass['mcno'])){
			$class_level = builtzongclass($DB_ZLV,$zclass['mcno'],$zclass['class_name'],[]);
			if(is_array($class_level)&&count($class_level)){
			  $freference[0]['level'] = array_keys($class_level);	
			  $freference[0]['series'] = array_values($class_level);	
			}
		}
		  
		
		
		//取得當前資料版本 - by匯出key
		$meta_currency = [];
		
		$DB_MTNOW = $this->DBLink->prepare( SQL_AdMeta::GET_EXPORT_CURRENT_META());
	    $DB_MTNOW->bindValue(':key',$meta_key);
	    $DB_MTNOW->execute();
	    while($tmp = $DB_MTNOW->fetch(PDO::FETCH_ASSOC)){
		  if(!isset($meta_currency[$tmp['class']])) $meta_currency[$tmp['class']]=['volume'=>[],'element'=>[]];
		  $source = json_decode($tmp['source_json'],true);
	      if($tmp['data_type']=='collection'){
			$meta_currency[$tmp['class']]['volume'][$tmp['applyindex']] = $source['collection'];
		  }else{
			$meta_currency[$tmp['class']]['element'][$tmp['applyindex']] = $source['element'];  
		  }
		}
		
		
		//取得匯出當時的資料狀態
		$meta_whenexport = [];
		$export_store = _SYSTEM_DIGITAL_FILE_PATH.'METADATA/export/'.$meta_key.'.json';
		if(file_exists($export_store)){
		  $meta_whenexport = json_decode(file_get_contents($export_store),true);	
		}
		
		
		//掃描excel檔案
		$sheet_count = $objPHPExcel->getSheetCount();
		$sheet_check = [
		  'insert'=>[],
		  'delete'=>[],
		  'conflict'=>[],
		  'modify'=>[],
		  'fail'=>[],
		  'total'=>0
		];
		
		$sheet_type_map = [0=>'volume',1=>'element']; 
		
		for($shindex=0;$shindex<2;$shindex++){
		  
		  $meta_sheet = $objPHPExcel->setActiveSheetIndex($shindex);
		  $meta_sheet_name = $objPHPExcel->getActiveSheet()->getTitle();  
		  
		  // get meta field
		  $col = 0;
		  $meta_fileds   = [];
		  $meta_col2field = [];
		  
		  $fcounter = 0;
		  while( $f = trim($meta_sheet->getCellByColumnAndRow($col,$frow_start)->getValue())){
            
			$meta_fileds[$f]=[
		      'index'	=> $col,
			  'field'	=> $f,
			  'name'	=> trim($meta_sheet->getCellByColumnAndRow($col,($frow_start-1))->getValue()),
		      'format'	=> (isset($fformat[$shindex][$f]) ? $fformat[$shindex][$f] : []),
			  'refer' 	=> (isset($freference[$shindex][$f]) ? $freference[$shindex][$f] : [])
		    ];
			$meta_col2field[$col] = $f;
			$col++;
		    if($fcounter++ > 100 ) break; // 防止迴圈
		  }
		  
		  // get meta sheet
		  $mcol_start   = 0;
		  $mrow_start   = 6;
		  $mcol_finish  = count($meta_fileds);
		  
		  $frow = $mrow_start; 
		  $read_exit_fleg = 0;
		  $meta_records = [];
		  $meta_rremove = [];  //存放不須保存的資料行
		  
		  do{
			
			$meta_xlsread = [];  //從excel中讀取之資料
			$meta_linkfld = '';  //跨資料版本之索引號碼 
			
			for($fcol=$mcol_start ; $fcol<$mcol_finish ; $fcol++ ){
			  $cell = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getValue());  
		      if(substr($cell,0,1)=='='){
				$cell = trim($meta_sheet->getCellByColumnAndRow($fcol,$frow)->getFormattedValue());
			  }
			  $meta_xlsread[$meta_col2field[$fcol]] = $cell;
			  if($meta_col2field[$fcol] == $this->SourceTableIndexFild){
				$meta_linkfld = $cell; 
			  }
			} 
		    
			if(!count(array_filter($meta_xlsread))){  // 若為空值
				$read_exit_fleg++;
			}else{
				
				
				// 取得其他參照版本
				$meta_db_record = isset($meta_currency[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld]) ? $meta_currency[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld] :[];
				$meta_ep_record = isset($meta_whenexport[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld]) ? $meta_whenexport[$meta_class][$sheet_type_map[$shindex]][$meta_linkfld] :[];
				
				
				//檢測資料
				$error_detect = 0;
				
				foreach($meta_xlsread as $mf => $mv){  
				  
				  if(!isset($meta_fileds[$mf])) continue;
				  
				  $format_pass = true;  
				  $notconflict = true;  
				  
				  $cellid  = [colnum2code($meta_fileds[$mf]['index']),$frow];
				  $checker = isset($meta_fileds[$mf]['format']) ? $meta_fileds[$mf]['format'] : [];
				  	
				  //check if auto new
				  if($checker['autonew']){
					if($mv=='+'){
					  $sheet_check['insert'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => '第 '.$frow.' 行',  //colnum2code($meta_fileds[$mf]['index']).':'.$frow
						'descrip' => join(', ',array_values($meta_xlsread)),
					  ];  
					}else if($mv=='-'){
					  $sheet_check['delete'][] = [
					    'cellid'  => '第 '.$frow.' 行',  //colnum2code($meta_fileds[$mf]['index']).':'.$frow
					  ];    
					}
			      }
				  
				  //check if empty
				  if($checker['nessary'] && $mv=='' ){
					$sheet_check['fail'][] = [
					  'sheet'   => $meta_sheet_name,
					  'cellid'  => join(':',$cellid),
					  'column'  => $meta_fileds[$mf]['name'],
					  'content' => '',
					  'descrip' => '此欄位不可為空',
					];
					$format_pass = false;
					$error_detect++;
				  
				  }else if($checker['fromsys'] && isset($freference[$shindex][$mf])){
					//check from system
					$valrefer = $freference[$shindex][$mf];  
					if(!in_array($mv,$valrefer)){
					  $sheet_check['fail'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $mv,
					    'descrip' => '資料不合法，請使用系統設定值',
					  ];
					  $format_pass = false;
					  $error_detect++;
					}  
			      }else{
					
					switch($checker['module']){
						case 'R': if(!preg_match( $checker['pattern'] ,$mv)){ $format_pass=false; } break;
						case 'V': if( $checker['pattern']!=$mv){ $format_pass=false;} break;
						case 'S':	if( $mv!='' && !in_array($mv,explode(';',$checker['pattern']))){ $format_pass=false; } break;				  
						default: break;	
					}
					if(!$format_pass){
					  $sheet_check['fail'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $mv,
					    'descrip' => '格式錯誤'.($checker['descrip'] ? ' : '.$checker['descrip'] : ''),
					  ];
					  $error_detect++;
					}
				  }
				  
				  // 檢視是否碰撞  目前資料版本已與匯出時不同
				  if(isset($meta_db_record[$mf]) && isset($meta_ep_record[$mf]) && $meta_db_record[$mf] != $meta_ep_record[$mf]){
					$sheet_check['conflict'][] = [
					    'sheet'   => $meta_sheet_name,
						'cellid'  => join(':',$cellid),
					    'column'  => $meta_fileds[$mf]['name'],
					    'content' => $meta_db_record[$mf],
					    'descrip' => '資料已變更 : '.$meta_db_record['_timeupdate'].'由'.$meta_db_record['_userupdate'].'修改',
					];
					$notconflict = false;
				    $error_detect++;
				  }
				  
				  
				  //錯誤標示於excel
			      $cell_style['fill'] = array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'ff4500')
				  );
			      if(!$format_pass){
					$meta_sheet->getStyle(join('',$cellid).':'.join('',$cellid))->applyFromArray($cell_style);	  
				  }
				  
				  //衝突標示excel
			      $cell_style['fill'] = array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => '888888')
				  );
			      if(!$notconflict){
					$meta_sheet->getStyle(join('',$cellid).':'.join('',$cellid))->applyFromArray($cell_style);	  
				  }
				}
				
				if($error_detect===0){
				  $meta_rremove[] = $frow;
				}
				
				$sheet_check['total']++;
			}
			$frow++;
			
			
			
		  }while($read_exit_fleg < 10);
		  
		  // 移除excel 列
		  if(count($meta_rremove)){
			while($del_row = array_pop($meta_rremove)){
			  $meta_sheet->removeRow($del_row,1);  	
			}			
		  }
		  
		}
		
		$excel_file_name = preg_replace('/(\.xls)/','-E\\1',$UploadFile);
		$objPHPExcel->setActiveSheetIndex(0);
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($upload_folder.$excel_file_name); 
		unset($objPHPExcel);
		
		// final
		$result['action'] = true;
		$result['data']   = $excel_file_name;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//-- Admin Meta Hide/Show Image
	// [input] : DataNo  :  \d+;
	// [input] : Switch => 0/1
	public function ADMeta_DObj_Display_Switch($DataNo , $PageName , $HideFlag){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d\-]+$/',$DataNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		$do_display = intval($HideFlag) ? 1 : 0;
		
		
		// 取得編輯資料
		$meta = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMeta::GET_TARGET_META_DATA());
		$DB_GET->bindParam(':id'   , $DataNo );	
		if( !$DB_GET->execute() || !$meta = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$meta_doconf = json_decode($meta['dobj_json'],true);  // from convas objects
		
		if(!isset($meta_doconf['domask'][$PageName])){
		  $meta_doconf['domask'][$PageName] = [];	
		}
		
		$meta_doconf['domask'][$PageName]['display'] = $do_display;
		$meta_doconf['logs'][date('Y-m-d H:i:s')] = "display ".$do_display." by ".$this->USER->UserID;
		
		$DB_UPD	= $this->DBLink->prepare( SQL_AdMeta::UPDATE_METADATA_DATA(array('dobj_json')));
		$DB_UPD->bindParam(':sid'   , $meta['system_id'] , PDO::PARAM_INT);	
		$DB_UPD->bindValue(':dobj_json' , json_encode($meta_doconf));
	    if( !$DB_UPD->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// final 
		$result['data']   = $PageName;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	// 處理時間
	static public function paser_date_array($DateArray){
		  
	  $paser_return = [
		'ds'=>NULL,
		'de'=>NULL,
		'years'=>['none'],
	  ];
	  
	  $date_queue = array();
	  
	  if(!is_array($DateArray)){
		return $paser_return;  
	  }
	  
	  
	  
	  foreach($DateArray as $dstr){
	  
		$ynum = intval(substr($dstr,0,4));		  
		$dset = preg_split('/(\/|\-|\.)/',$dstr);
		 
		if(!strtotime(strtr($dstr,'.','-')) || ( $ynum < 1911 || $ynum > date('Y'))){
		  continue;
		}
		
		if(isset($dset[1]) && !intval($dset[1])){
		  $dset[1]='01';		
		}
		
		if(isset($dset[2]) && !intval($dset[2])){
		  $dset[2]='01';		
		}
		
		$date_queue[] = strtotime(join('-',$dset));	
	  }
	  
	  if(!count($date_queue)){
		return $paser_return;    
	  }
	  
	  $paser_return['ds'] = date('Y-m-d',min($date_queue));
	  $paser_return['de'] = date('Y-m-d',max($date_queue));
	  $paser_return['years'] = [];
	  
	  for($i=intval(substr($paser_return['ds'],0,4)); $i<= intval(substr($paser_return['de'],0,4)) ; $i++){
		$yearnum = $i; //str_pad($i,4,'0',STR_PAD_LEFT);
		if( ($yearnum-1911) > 0 ){
		  $paser_return['years'][] = str_pad($yearnum,4,'0',STR_PAD_LEFT).' 民國'.($yearnum-1911).'年';   
		}else if(($yearnum-1911) < 0){
		  $paser_return['years'][] = str_pad($yearnum,4,'0',STR_PAD_LEFT).' 民國前'.($yearnum-1911).'年';   
		}else{
		  $paser_return['years'][] = str_pad($yearnum,4,'0',STR_PAD_LEFT).' 民國元年';  
		}
	  }
	  //var_dump($paser_return);
	  return $paser_return; 
		  
	}
	
	// 處理人名	  
	static public function paser_person($MemberArray){
	  $paser_return = [];
	   	
	  if(!is_array($MemberArray)){
		return $paser_return;  
	  }
	  
	  $data_queue = array();
	  
	  foreach($MemberArray as $mbr_string){
		$data_queue += preg_split('/(，|、|；|;|,)/u',$mbr_string);
	  }
	  
	  $data_queue = array_unique(array_filter($data_queue));
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  
	  return array_values($data_queue); 
	}	  
	

	// 處理單位  
	static public function  paser_organ($OrganArray){
	  $paser_return = [];
	   	
	  if(!is_array($OrganArray)){
		return $paser_return;  
	  }
	  
	  $data_queue = array();
	  
	  foreach($OrganArray as $org_string){
		$data_queue += preg_split('/(，|、|；|;|,|\s+)/u',$org_string);
	  }
	  
	  $data_queue = array_unique(array_filter($data_queue));
	  
	  foreach($data_queue as $key=>$ditem){
		if(preg_match('/:|：/',$ditem)){
		  $ditemsplit = preg_split('/:|：/',$ditem);
		  $data_queue[$key] = array_shift($ditemsplit);	
		}
	  }
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  
	  return array_values($data_queue); 
	} 
		 
	// 處理多重欄位	  
	static public function  paser_postquery($FieldArray){
	  $paser_return = [];
	   	
	  if(!is_array($FieldArray)){
		return $paser_return;  
	  }
	  
	  $data_queue = array();
	  
	  foreach($FieldArray as $field_string){
		$data_queue += preg_split('/(，|、|；|;|,)/u',$field_string);
	  }
	  
	  $data_queue = array_unique(array_filter($data_queue));
	  
	  if(!count($data_queue)){
		return $paser_return;   
	  }
	  return array_values($data_queue); 
	}	 
	
		// 轉國字數字
	static public function getChineseNumber($num){
		  $conver = array(
		   0 => '-',
		   1 => '一',
		   2 => '二',
		   3 => '三',
		   4 => '四',
		   5 => '五',
		   6 => '六',
		   7 => '七',
		   8 => '八',
		   9 => '九',
		   10 => '十',
		   11 => '十一',
		   12 => '十二',
		   13 => '十三',
		   14 => '十四',
		   15 => '十五',
		   16 => '十六',
		   17 => '十七',
		   18 => '十八',
		   19 => '十九',
		   20 => '二十',
		  );
		  
	    return isset($conver[$num]) ? $conver[$num] : '-';
	}
	
	
  }
?>