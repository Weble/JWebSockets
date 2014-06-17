<div class="progress hidden">
  <div class="bar" style="width: 0%;"></div>
</div>

<script src="http://js.pusher.com/2.2/pusher.min.js" type="text/javascript"></script>

<script type="text/javascript">
	jQuery(document).ready(function($){

		// Enable pusher logging - don't include this in production
		    Pusher.log = function(message) {
		      if (window.console && window.console.log) {
		        window.console.log(message);
		      }
		    };


		var pusher = new Pusher('b663e0d9ded0302f369a');
		var channel = pusher.subscribe('backup');
		channel.bind('pusher:subscription_succeeded', function() {
			try {
				channel.bind('step', function(data) {
					if (!data.HasRun == 1) {
						$('.progress').removeClass("hidden");
						$('.bar').css('width', data.Progress + '%');
					} else {
						$('.progress').addClass("hidden");
						$('.bar').css('width', '0%');
					}
				});

				
			} catch(e) {
				console.log(JSON.stringify(e));
			}
		});
	})
</script>