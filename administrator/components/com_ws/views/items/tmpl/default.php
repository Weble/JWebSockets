<button id="startbackupws" type="button">
	Start Backup
</button>
<div class="progress hidden">
  <div class="bar" style="width: 0%;"></div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var start = new Date().getTime();
		var conn = new WebSocket('ws://localhost:8080', {secure: true});
		conn.onopen = function(e) {
		    console.log("Connection established!");
		};

		$('#startbackupws').click(function(){
			conn.send(JSON.stringify({
				option: 'com_ws', 
				view: 'items', 
				task: 'ws',
				ajax: 'start',
				tag: 'backend'
			}));

			$('.progress').removeClass("hidden");
		})

		conn.onmessage = function(e) {
		    if (e && e.data) {
		    	var data = JSON.parse(e.data);
		    	if (!data.HasRun == 1) {
		    		$('.progress').removeClass("hidden");
		    		$('.bar').css('width', data.Progress + '%');
		    	} else {
		    		$('.progress').addClass("hidden");
		    		$('.bar').css('width', '0%');
		    	}
		    }
		}
		window.conn = conn;
	})
</script>