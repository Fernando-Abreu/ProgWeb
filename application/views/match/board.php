

<!DOCTYPE html>

<html>
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var user_id = "<?= $user->id ?>";
		var status = "<?= $status ?>";
		var possible_columns = [];
		
		$(function(){
			$('body').everyTime(2000,function(){
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
					var url = "<?= base_url() ?>board/getMsg";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							var conversation = $('[name=conversation]').val();
							var msg = data.message;
							if (msg.length > 0)
								$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
						}
					});
					if (turn==false) {
						GetGameUpdate();
					}
			});

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

		    GetGameUpdate();
		});

	function GetGameUpdate(){
		var url = "<?= base_url() ?>board/check_for_updates";
		$.getJSON(url, function (data,text,jqXHR){
			if (data) {
				if (data.victory==true) {
					turn = false;
					if (data.user==user_id) {
						$('#move_desc').html("You won!");
					} else {
						$('#move_desc').html("You lost");
					} 
				} else {
					if (data.user==user_id) {
						possible_columns = data.columns;
						$('#move_desc').html("Your turn");
					} else {
						possible_columns = [];
						$('#move_desc').html("Waiting for opponent");
					}
					updateBoardDisplay(data.board);
				}
			}
		});
	}

	</script>
	</head>
	
	
<body>  
	<h1>Game Area</h1>

	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</div>
	
	<div id='status'> 
	<?php 
		if ($status == "playing")
			echo "Playing " . $otherUser->login;
		else
			echo "Wating on " . $otherUser->login;
	?>
	</div>
	
	<div id='move_desc'>
	</div>
	
	<canvas id="canvas" width="700" height="600" style="border:1px solid #FF0000;"></canvas>
	
	
<?php 
	
	echo form_textarea('conversation');
	
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();
	
?>
		
</body>

<script>
// board display functions

var discs = [];
var canvas;
var context;
  
window.onload = function() {
    canvas = document.getElementById("canvas");
    canvas.onmousedown = canvasClick;
    context = canvas.getContext("2d");
};
   
function Disc(canvas,x, y, color) 
{
    if(canvas)
    {
        this.context = canvas.getContext("2d");
        this.x = x;
        this.y = y;
        this.radius = 40;
        this.color = color;
    }
}
  
Disc.prototype.draw = function()
{
    // Draw the dics
    this.context.globalAlpha = 0.85;
    this.context.beginPath();
    this.context.arc(this.x, this.y, this.radius, 0, Math.PI*2);
    this.context.fillStyle = this.color;
    this.context.strokeStyle = "black"; 
    this.context.fill();
    this.context.stroke();
};
  
  
function drawGrid(canvas) {
    var gridOptions = {
        majorLines: {
            separation: 100,
            color: '#FF0000'
        }
    };
    drawGridLines(canvas, gridOptions.majorLines);
    return;
}

function drawGridLines(canvas, lineOptions) {

    var iWidth = canvas.width;
    var iHeight = canvas.height;

    context = canvas.getContext('2d');

    context.strokeStyle = lineOptions.color;
    context.strokeWidth = 1;

    context.beginPath();

    var iCount = null;
    var i = null;
    var x = null;
    var y = null;

    iCount = Math.floor(iWidth / lineOptions.separation);

    for (i = 1; i <= iCount; i++) {
        x = (i * lineOptions.separation);
        context.moveTo(x, 0);
        context.lineTo(x, iHeight);
        context.stroke();
    }

    iCount = Math.floor(iHeight / lineOptions.separation);

    for (i = 1; i <= iCount; i++) {
        y = (i * lineOptions.separation);
        context.moveTo(0, y);
        context.lineTo(iWidth, y);
        context.stroke();
    }

    context.closePath();
    return;
}
  
function drawDiscs()
{
    // Clear the canvas
    context.clearRect(0,0, canvas.width, canvas.height);
      
    // Draw all the discs in the grid
    drawGrid(canvas);
    for(var i=0; i<discs.length;i++)
    { 
        var disc = discs[i];
        disc.draw();
    }
              
}
  
function addDisc(x, y, color)
{
    // Creates new disc
    var disc = new Disc(canvas, x, y, color);
    // Store disc in the array
    discs.push(disc);
}
  
function canvasClick(e)
{
    // Get the canvas click coordinates
    var clickX = e.pageX - canvas.offsetLeft;
    var column = Math.floor(clickX/100);
    // Check if it is a legal move
    if (possible_columns.indexOf(column)!=-1) {
		// play column
        $.ajax({
            url: "<?= base_url() ?>board/play",
            type: 'POST',
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({ 'column': column }),
            success: function (result) {
                alert("success");
                GetGameUpdate();
                //if (data && data.status=='success') {
				//	alert ('real success');
				//	}
            }
        });
    }
    return;
}

function updateBoardDisplay(board_array)
{  
	discs = [];
    for (var column = 0; column<7; column++)
    {
        for (var row =0; row<6; row++)
        {
            if (board_array[column][row]!=0){
	            var x = 50 + 100*column;
	            var y = 550 - 100*row;
	            var color_id = board_array[column][row];
				var color = "";
	            switch(color_id)
	            {
	            	case(1): color = "blue";break;
	            	case(2): color = "green"; break;
	            	case(-1): color = "lightblue";break;
	            	case(-2): color = "lightgreen";break;
		        }
	            addDisc(x, y, color);  
            }                      
        }
    }
    drawDiscs();
}
  
</script>

</html>

