<?php
$viewTemplate = $this->getRenderedForm();
//echo $viewTemplate; 
?>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var conn = new WebSocket('ws://localhost:8080');
		conn.onopen = function(e) {
		    console.log("Connection established!");
		    conn.send(JSON.stringify({
		    	option: 'com_ws', 
		    	view: 'items', 
		    	task: 'ws',
		    	ajax: 'start',
		    	tag: 'backend'
		    }));
		};

		conn.onmessage = function(e) {
		    console.log(e.data);
		}
		window.conn = conn;
	})
</script>