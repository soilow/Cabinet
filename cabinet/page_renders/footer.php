<?php

class Footer {
	private static $output = '';

	public static function Render($validator_flag = 0) {
		self::$output .= self::Support();
		self::$output .= self::Jquery();

		if ($validator_flag)
			self::$output .= self::RealTimeValidator();

		self::$output .= self::End();

		return self::$output;
	}

	private static function Support() {
		return '
		<div class="support">
			<p>Поддержка:</p>
		</div>
		';
	}

	private static function Jquery() {
		return'
		<script src="frontend/script/dashboard.js?id='.filectime("frontend/script/dashboard.js").'"></script>';
	}

	private static function RealTimeValidator() {
		return "
		<script>
			function realtime_validate(evt, mode) {
			  var theEvent = evt || window.event;
			  var key = theEvent.keyCode || theEvent.which;
			  key = String.fromCharCode( key );

			  if (mode == 'num') {
			  	var regex = /[0-9]/;
			  } 
			  else if (mode == 'alnum') {
			  	var regex = /[a-zA-Z\d]/;
			  }
			  else if (mode == 'rusalnum') {
			  	var regex = /[a-zA-Zа-яА-Я \d]/;
			  }
			  else if (mode == 'money') {
			  	var regex = /[-\d]/;
			  }

			  if( !regex.test(key) ) {
			    theEvent.returnValue = false;
			    if(theEvent.preventDefault) theEvent.preventDefault();
			  }
			}
		</script>";
	}

	private static function End() {
		return'
		</body>
		</html>';
	}
}

?>