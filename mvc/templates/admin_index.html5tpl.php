<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE : 'RCDH System'; ?></title>
	
	<!-- CSS -->
	<link type="text/css" href="tool/jquery-ui-1.12.1.custom/jquery-ui.structure.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/jquery-ui-1.12.1.custom/jquery-ui.theme.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
    <script type="text/javascript" src="tool/html2canvas.js"></script>
	<script type="text/javascript" src="tool/Highcharts-4.2.3/js/highcharts.js"></script>
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_ad10.css" />
	
	<link rel="stylesheet" type="text/css" href="theme/css/css_index_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_index_admin.js"></script>
	
	
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	$chart_data 	= isset($this->vars['server']['data']['space']['chart']) ? $this->vars['server']['data']['space']['chart']:array();
	$space_data     = isset($this->vars['server']['data']['space']['space']) ? $this->vars['server']['data']['space']['space']:array('total'=>0,'used'=>0,'rate');
	
	$count_data     = isset($this->vars['server']['data']['space']['count']) ? $this->vars['server']['data']['space']['count']:array('photo'=>0);
	 
	$post_list = isset($this->vars['server']['data']['post']) ? $this->vars['server']['data']['post'] : ''; 
	 
	 
	?>
  
    <data id='chart_data'><?php echo json_encode($chart_data,JSON_NUMERIC_CHECK);?></data>
  
  </head>
  <body>
	<div class='system_main_area'>
	  <div class='system_manual_area'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area'>
        <div class='tool_banner' >
		  <span>
		    北投文物館典藏管理系統 
		  </span>
		  <span class='account_option tool_right'>
		    <div class='account_info'>
			  <span id='acc_mark'><i class='m_head'></i><i class='m_body'></i></span>
			  <span id='acc_string'> 
			    <i class='acc_name'><?php echo $user_info['user']['user_name']; ?></i>
			    <i class='acc_group'><?php echo $user_info['user']['user_group']; ?></i>
			  </span>
			  <span id='acc_option'><a class='mark16 pic_more'></a> </span>
			</div>
		    <div class='account_control arrow_box'>
			  <ul class='acc_option_list'>
			    <li >
				  <label title='目前群組'> <i class="fa fa-university" aria-hidden="true"></i> 群組 </label>
				  <select id='acc_group_select'>
				    <?php foreach($user_info['group'] as $gset): ?>  
				    <option value='<?php echo $gset['id']?>' <?php echo $gset['now']?'selected':'' ?> > <?php echo $gset['name']; ?></option>
				    <?php endforeach; ?>
				  </select>
				</li>
				<li> 
				  <label> <i class="fa fa-user-secret" aria-hidden="true"></i> 角色 </label>
				  <span>
				    <?php foreach($user_info['group'] as $gid => $gset): ?>  
				    <?php if($gset['now']) echo join(',',$gset['roles']); ?>
				    <?php endforeach; ?>
				  </span> 
				</li>
				<li>
				  <label> <i class="fa fa-clock-o" aria-hidden="true"></i> 登入</label>
				  <span> <?php echo $user_info['login']; ?></span>
				</li>
			  </ul>
			  <div class='acc_option_final'>
			    <span id='acc_logout'> 登出 </span>
			  </div>
		    </div>
		  </span>
		</div>
		
		 
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='' >
		    <div class='record_header'>
			  <span class='record_name'>資料統計</span>
			  <span class='record_option'>
			  </span>
			</div> 
			<div class='record_body' id='system_dashboard'>
			  <div id='chart_container'></div>
		      <div class='space_container'>
			    <div id='space_used'>
			      <h1>空間用量</h1>
				  <div class='descrip'>
				    <span><?php echo $space_data['used'];?></span> / <span><?php echo $space_data['total'];?></span> ( <span><?php echo $space_data['rate'];?>%</span> )
				  </div >
			    </div>
				<div id='data_count'>
			      <h1>文物數量</h1>
				  <div class='descrip'>
				    <span> <?php echo number_format($count_data['photo']);?> 筆文物 </span>
				  </div>
			    </div>
			    <div id='photo_rate'></div>
			  </div>
			</div>
		  </div>
		  
		  
		  <div class='data_record_block' id='information_block' >
			  
			  
			  <div class='information-block' id='announcement'>
				<div class='record_header'>
				  <span class='record_name'>最新消息</span>
				  <?php if(count($post_list )>5): ?>
				  <span class='more option' id='act_switch_post_mode' ><i class="fa fa-angle-double-right" aria-hidden="true"></i> <i class='more' title='顯示所有公告'>MORE</i><i class='hide' title='顯示前列公告'>HIDE</i> </span>	
				  <?php endif; ?>
				</div> 
				
				<div class='billboard' more='0' >
				  <div class='news_block' >
					<?php foreach($post_list as $post): ?>   
					<div class='post' no='<?php echo intval($post['pno']);?>' top='<?php echo $post['post_level'] > 2 ? 1 : 0; ?>' mark="<?php echo $post['post_type']; ?>" popout='<?php echo $post['post_type']=='緊急通告' ? 1 : 0; ?>'  >
					  <h2>
						<span class='post_date' > <?php echo substr($post['post_time_start'],0,10); ?></span>
						<span class='post_type' > <?php echo $post['post_type']; ?> </span>
						<span class='post_summary' > <?php echo $post['post_title']; ?> </span>
						<span class='post_organ'>  <?php echo $post['post_from']; ?> </span>
						<span class='post_rate' style='width:<?php echo ($post['post_level']-1)*22; ?>px'>  </span>
					  </h2>
					  <div class='post_content'>
						<?php echo $post['post_title']; ?>
					  </div>
					</div>
					<?php endforeach; ?>
				  </div>
				</div> 
			  </div>
			  
			  <div>	
				<div class='record_header'>
				  <span class='record_name'>文件與指南</span>
				  <span class='record_option'>
				  </span>
				</div> 
				<div class='record_body' id='system_document'>
				  
				  <div class='document_block'>
					<h2>系統操作指南 <a href='docs/document_system_20160316.pdf' target=_blank> 下載 </a></h2>
					<div>
					  系統介面說明與操作指引，包含圖庫資料庫操作、分類管理、帳號管理、紀錄管理與回報管理五部分。
					</div>
					
				  </div>
				  
				  <div class='document_block'>
					<h2>系統管理手冊 <a href='docs/document_admin_20160316.pdf' target=_blank> 下載 </a></h2>
					<div>
					  系統管理指南，包含系統架構、開關機步驟與復原程序。
					</div>
				  </div>
				  
				</div>
			  </div>
			  
			 
		  </div>
		  		  
		</div>
	  </div>
	</div>
	
	
	<!-- 框架外結構  -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'></div>
		  <div class='msg_info'><?php echo $page_info;?></div>
		</div>
		<div id='area_close'></div>
      </div>
	</div> 
	
	<!-- 公告訊息 -->
	<div class='system_announcement_area'>
	    <div class='container'>
		  <div id='announcement_block'>
		    <h1>
			  <div class='ann_header'>
			    <span class='ann_type'></span> 
				<span class='ann_title'></span> 
			  </div>
			  <span class='ann_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</h1>
			<div class='ann_contents'></div>
			<div class='ann_footer'>
			  <div>
			    <span class='ann_time'>  </span>
				From
				<span class='ann_from'>  </span>
			  </div>
			  <div>
			    <span class='ann_counter'>  </span>
			  </div>
			</div>
		  </div>
        </div>
	</div>
	
	<!-- 系統report -->
      <div class='system_feedback_area'>
        <div class='feedback_block'>
        <div class='feedback_header tr_like' >
          <span class='fbh_title'> 系統回報 </span>
          <a class='fbh_option' id='act_feedback_close' title='關閉' ><i class='mark16 pic_close'></i></a>
        </div>
        <div class='feedback_body' >
          <div class='fb_imgload'> 建立預覽中..</div>
          <div class='fb_preview'></div>
          <div class='fb_areasel'>
            <span>回報頁面:</span>
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_body_block'>全頁面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_content_area'>中版面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_edit_area'>右版面
            <input type='radio' class='feedback_upload_sel' name='feedback_area' value='user_upload'><input type='file'  id='feedback_img_upload' >
          </div>
          <div class='fb_descrip'>
            <div class=''>
              <span class='fbd_title'>回報類型:</span>
              <input type='checkbox' class='feedback_type' name='fbd_type' value='資料問題' ><span >資料問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='系統問題' ><span >系統問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='使用問題' ><span >使用問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='建議回饋' ><span >建議回饋</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='其他' >其他:<input type='text' class='fbd_type_other' name='fbd_type_other' value='' >
            </div>
            <div class='fbd_title'>回報描述:</div>
            <textarea  class='feedback_content'  name='fbd_content'></textarea>
          </div>
        </div>
        <div class='feedback_bottom tr_like' >
          <a class='sysbtn btn_feedback' id='act_feedback_cancel' > <i class='mark16 pic_account_off'></i> 取 消 </a>
          <a class='sysbtn btn_feedback' id='act_feedback_submit' > <i class='mark16 pic_account_on'></i> 送 出 </a>		
        </div>
        </div>
      </div>      
	
	<!-- 系統Loading -->
    <div class='system_loading_area'>
	  <div class='loading_block' >
	    <div class='loading_string'> 系統處理中 </div>
		<div class='loading_image' id='sysloader'></div>
	    <div class='loading_info'>
		  <span >如果系統過久無回應，請按[ Esc ] 關閉 loading 版面，並重新操作剛才的動作.</span>
	    </div>
	  </div>
	</div>
  
  </body>
</html>