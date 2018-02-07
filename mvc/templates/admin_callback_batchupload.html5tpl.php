<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<!-- PHP DATA -->
	<?php
	$result_data = isset($this->vars['server']) ? $this->vars['server'] : []; 
	?>
	<script type="text/javascript" >
	  parent.process_batch_upload('<?php echo json_encode($result_data);?>');
	</script>
  </head>
  <body></body>
</html>