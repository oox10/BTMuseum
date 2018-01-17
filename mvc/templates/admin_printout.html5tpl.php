<!DOCTYPE html>
<html lang="zh-Hant-TW">
	<head>
		<title></title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<style>
		  *{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}			
		  html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{border:0;font-size:100%;font:inherit;vertical-align:baseline;margin:0;padding:0}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:none}table{border-collapse:collapse;border-spacing:0}
		</style>	
		<style>
			html,body{font-family:Arial,'Microsoft Jhenghei','微軟正黑體', PMingLiu, sans-serif;font-size:20px;line-height:1.1}html{overflow-x:hidden}
			table{text-align:center;border-collapse:collapse;border-spacing:0;width:100%;display:table;}
			th{font-weight:bold;border:solid #000000 1px;padding:5px 0;}
			td{border:#000000 0px;padding:5px 5px;border-style:solid;text-align:left;}
			
			.page-container{
			  display:flex;
              flex-direction:column;			  
			}
			
			.page-container > header{ flex:0 0 50px; display:flex; justify-content:space-between; align-items:center; padding:5px;}
			  .page_rander{font-size:0.8em;}
			.page-container > article{ 
			  flex:1;
			}
			  .print_record{
				display:flex; 
				padding-top:5px;
				margin-top:5px;
				font-size:0.8em; 
				break-inside:avoid; 
				border-top:1px #cdcdcd dotted;
			  
			  }
			    .prno{flex:0 0 40px; text-align:right;}
				.prthumb{
				  flex:0 0 150px; 
				  display:flex;
				  align-items:flex-start;
				  overflow:hidden;
				  padding:0 5px;
				  
				}
				.prthumb > img{width:100%; height:auto;}
		     
		        .prfields{flex:1;}
				.prfields > table{width:100%; }
				.prfields > table td.pfield{ width:20%; font-weight:bold;}
				.prfields > table td.pvalue{   }
			
			.page-content-row{padding:5px 5px;}
			
			
			.page-footer{
			  text-align:right;
			  padding:5px 0;
			  font-size:0.85em;
			}
			
			
			@media print {
				.page-container{
					page-break-after:always;
					page-break-inside : avoid;/*在列印中，page-container中不會換頁?*/
				}
				.table-unbreak-container td{ page-break-inside:avoid; }/*在列印中，table.table-unbreak-container中 td 不會換頁*/
				.page-container div.print_record{ page-break-inside:avoid;break-inside:avoid;}
			}
			@media screen{
				body{
					width:21cm;/*限制為A4寬*/
					margin:0 auto;
				}
				.page-container{
					width:90%;
					margin:20px auto;
					border:1px #CDCDCD solid;
					min-height: 277mm;
				}
			}
			@page{
				size:portrait;/*ㄧ頁大小，以紙張短邊為寬，沒有用。*/
				margin:5%;
			}
		</style>
	    <script type="text/javascript" src="tool/jquery-3.2.1.min.js"></script>
		
	    <?php 
		  $print_thumb   = isset($this->vars['server']['data']['print']['hasthumb']) ? $this->vars['server']['data']['print']['hasthumb'] : 1;
		  $print_roweach = isset($this->vars['server']['data']['print']['rowseach']) ? $this->vars['server']['data']['print']['rowseach'] : 10;
		  $print_records = isset($this->vars['server']['data']['print']['records']) ? $this->vars['server']['data']['print']['records']   : [];	  
		  $page = 0;
		?>
	
	</head>
	<body>
	    <?php  for($i=0 ; $i<count($print_records) ; $i+=$print_roweach ): ?>
		<?php  
		  $page++;
		  $records = array_slice($print_records,$i,$print_roweach);
		?>
		<div class = "page-container">
		    <header>	
		        <div class='page_title'>
			        <span><?php echo _SYSTEM_HTML_TITLE;?> - 資料列印</span>
				    <span>P.<?php echo ($page); ?> </span>
			    </div>
			    <div   class='page_rander'>
			        <span > 匯出時間:<?php echo date('Y-m-d H:i:s');?> </span> 
			    </div>
			</header>	
			<article>
			<?php foreach( $records as $key=>$r): ?> 
			  <div class='print_record'>
			    <div class='prno'><?php echo ($i+1+$key).'. '; ?></div>
			    <div class='prthumb'><img src='<?php echo $r['thumb'];?>'/> </div>
				<div class='prfields'>
				  <table class='table-unbreak-container'>
				    <tr><td colspan=2><?php echo $r['header']; ?></td></tr>
                    <?php foreach( $r['fields'] as $f): ?> 				    
                    <tr><td class='pfield'><?php echo $f['f']; ?>:</td><td class='pvalue'><?php echo $f['v']; ?></td></tr>
					<?php endforeach; ?>
				  </table>
				</div>
			  </div>	 
			<?php endforeach; ?>
			</article>
		</div>
		<?php  endfor; ?>
		
		<script type="text/javascript">
		$(document).ready(function () {
		  window.print();		  
		});
		(function() {
			var beforePrint = function() {
			  //console.log('Functionality to run before printing.');
			};
			var afterPrint = function() {
			  //window.close();
			  //console.log('Functionality to run after printing');
			};

			if (window.matchMedia) {
				var mediaQueryList = window.matchMedia('print');
				mediaQueryList.addListener(function(mql) {
					if ( mql.matches ) {
						beforePrint();
					} else {
						afterPrint();
					}
				});
			}
			window.onbeforeprint = beforePrint;
			window.onafterprint = afterPrint;
		}());
		</script>
	</body>
	
</html>