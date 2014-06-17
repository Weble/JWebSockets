<input id="message" type="text" placeholder="Message..." required />
<button id="send" type="button">Send</button>

<ul id="conversation">

</ul>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var conn = new WebSocket('ws://localhost:8080');
		conn.onopen = function(e) {
		    console.log("Connection established!");
		};

		$('#send').click(function(){
			conn.send(JSON.stringify({
				option: 'com_ws', 
				view: 'items', 
				task: 'send',
				message: $('#message').val(),
				name: '<?php echo JFactory::getUser()->name; ?>',
			}));
		})

		conn.onmessage = function(e) {
		    if (e && e.data) {
		    	var data = JSON.parse(e.data);
		    	var li = $('<li>');
		    	li.html('<strong>' + data.user + ': ' +data.message);
		    	$('#conversation').prepend(li);
		    }
		}
		window.conn = conn;
	})
</script>