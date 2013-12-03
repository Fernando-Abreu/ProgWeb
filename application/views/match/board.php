
<!DOCTYPE html>

<html>
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<link href="<?= base_url() ?>/css/template.css" rel="stylesheet">
	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var user_id = "<?= $user->id ?>";
		var status = "<?= $status ?>";
		var possible_columns = [];
		var turn = false;
		
		$(function(){
			// set updates handler - called every 2000 ms
			$('body').everyTime(2000,function(){
				// if waiting for invitation answer - check for answer from other user 
				if (status == 'waiting') {
					$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
							if (data && data.status=='rejected') {
								alert("Sorry, your invitation to play was declined!");
								window.location.href = '<?= base_url() ?>arcade/index';
							}
							if (data && data.status=='accepted') {
								status = 'playing';
								$('#status').html('Playing ' + otherUser);
							}
					});
				}
				// check for messages from other player
				var url = "<?= base_url() ?>board/getMsg";
				$.getJSON(url, function (data,text,jqXHR){
					if (data && data.status=='success') {
						var conversation = $('[name=conversation]').val();
						var msg = data.message;
						if (msg.length > 0)
							$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
					}
				});
				// if waiting for other person to play - check if played 
				if (status == 'playing' && turn == false) {
					GetGameUpdate();
				}
			});

			// set form handler - sends message
			$('form').submit(function(){
				var arguments = $(this).serialize();
				var url = "<?= base_url() ?>board/postMsg";
				$.post(url,arguments, function (data,textStatus,jqXHR){
						var conversation = $('[name=conversation]').val();
						var msg = $('[name=msg]').val();
						$('[name=msg]').val('');
						$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
						});
				return false;
				});
			// if ready to start playing - get game information from server 
			if (status == 'playing') {
		    	GetGameUpdate();
			}
		});

	// gets game information updates: win, turn, board state
	// and informs user by updating board display and short message
	function GetGameUpdate(){
		var url = "<?= base_url() ?>board/check_for_updates";
		$.getJSON(url, function (data,text,jqXHR){
			if (data) {
				if (data.status=='failure') {
					$('#move_desc').html(data.message);
				} else {
					if (data.victory==true) {
						status = 'gameover';
						turn = false;
						if (data.user==user_id) {
							$('#move_desc').html('You won!   <a href="<?= base_url() ?>index.php/arcade/endMatch">(Back)</a>' );
						} else {
							$('#move_desc').html('You lost..   <a href="<?= base_url() ?>index.php/arcade/endMatch">(Back)</a>');
						} 
					} else {
						if (data.user==user_id) {
							turn = true;
							possible_columns = data.columns;
							$('#move_desc').html("Your turn");
						} else {
							turn = false;
							possible_columns = [];
							$('#move_desc').html("Waiting for opponent");
						}
					}
					updateBoardDisplay(data.board);
				}
			}
		});
	}

	</script>
	<script> // board display functions
	
	var discs = [];
	var canvas;
	var context;
	  
	window.onload = function() {
	    canvas = document.getElementById("canvas");
	    canvas.onmousedown = canvasClick;
	    context = canvas.getContext("2d");
	};
	
	// disc object - has location, size, color and draw function
	function Disc(canvas,x, y, color) {
		this.context = canvas.getContext("2d");
	    this.x = x;
	    this.y = y;
	    this.radius = 20;
	    this.color = color;
	}
	Disc.prototype.draw = function() {
	    this.context.beginPath();
	    this.context.arc(this.x, this.y, this.radius, 0, Math.PI*2);
	    this.context.fillStyle = this.color;
	    this.context.strokeStyle = "black"; 
	    this.context.fill();
	    this.context.stroke();
	};
	
	// draws the board acoording to the array discs
	function drawDiscs() {
	    // Clear the canvas
	    context.clearRect(0,0, canvas.width, canvas.height);
	    // Draw all the discs in the grid
	    for(var i=0; i<discs.length;i++) { 
	        var disc = discs[i];
	        disc.draw();
	    }      
	}
	
	// displays the given board array as a board picture using the canvas
	function updateBoardDisplay(board_array) {  
		discs = [];
	    for (var column = 0; column<7; column++) {
	        for (var row =0; row<6; row++) {
	            var x = 25 + 50*column;
	            var y = 275 - 50*row;
	            var color_id = board_array[column][row];
				var color = "";
	            switch(color_id) {
	            	case(1): color = "red";break;
	            	case(2): color = "green"; break;
	            	case(-1): color = "pink";break;
	            	case(-2): color = "lightgreen";break;
	            	default: color = "white";
		        }
	            var disc = new Disc(canvas, x, y, color);
	            discs.push(disc);                    
	        }
	    }
	    drawDiscs();
	}
	
	// performes the player column selection for his turn
	function canvasClick(e) {
		// ignore if not currently playing
		if (status != 'playing') {
			return;
		}
	    // Get the canvas click coordinates
	    var clickX = e.pageX - canvas.offsetLeft;
	    var column = Math.floor(clickX/50);
	    // Check if it is a legal move
	    if (turn == false) {
	    	$('#move_desc').html("Please wait patiently for your turn");
	    } else if (possible_columns.indexOf(column)==-1) {
	    	$('#move_desc').html("Invalid column selection - Try again");
	    // if it is a legal move, do it
	    } else { 
	        url = "<?= base_url() ?>board/play";
	        args = "json= "+JSON.stringify(column);
	        $.ajax({
	            url: url,
	            data: args,
	            type: 'POST'
	        }).done(function(data,textStatus,jqXHR){
	        	GetGameUpdate();
	        	if (data && data.status=='failure') {
					$('#move_desc').html(data.message);
				}
	        });
	    }
	    return;
	}
	  
	</script>
	
	</head>
	
	
<body>  

	<h3>
		Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</h3>
	
	<h1 id='status'> 
		<?php 
		if ($status == "playing")
			echo "Playing " . $otherUser->login;
		else
			echo "Waiting on " . $otherUser->login;
		?>
	</h1>

	<p id='move_desc'></p>
	<canvas id="canvas" width=350 height=300></canvas>
	
	<div id='message_box'>
		<?php 
		echo form_textarea('conversation');
		echo form_open();
		echo form_input('msg');
		echo form_submit('Send','Send');
		echo form_close();
		?>
	</div>
		
</body>

</html>
