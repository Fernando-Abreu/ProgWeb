
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
		var turn = false;
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
					var url = "<?= base_url() ?>board/check_for_updates";
					$.getJSON(url, function (data,text,jqXHR){
						if (data) {
							if (data.victory==true) {
								turn = false;
								if (data.user==user_id) {
									$('#move_desc').html("You won! :)");
								} else {
									$('#move_desc').html("You lost :(");
								} 
							} else {
								if (data.user==user_id) {
									turn = true;
									possible_columns = data.columns;
									$('#move_desc').html("Your turn");
								} else {
									turn = false;
									$('#move_desc').html("Waiting for opponent");
								}
								board_array = data.board;
								$('#board').html(board_array.toString());
							}
						}
					});
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
		});

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
	
	<div id='board'></div>
	
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

// Game area
var discs = [];
var canvas;
var context;
  
window.onload = function() {
    canvas = document.getElementById("canvas");
    canvas.onmousedown = canvasClick;
    context = canvas.getContext("2d");
    getDiscs();
      
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
    //alert(this.color + " " +this.x + " "+this.y );
};
  

  
function drawGrid(canvas) {
     //canvas = document.getElementById("canvas");

    var gridOptions = {
        majorLines: {
            separation: 100,
            color: '#FF0000'
        }
    };

   // drawGridLines(cnv, gridOptions.minorLines);
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
    // Redraw the canvas 
    //drawDiscs();
      
}
  
function canvasClick(e)
{
      
    // Get the canvas click coordinates.
    var clickX = e.pageX - canvas.offsetLeft;
    var clickY = e.pageY - canvas.offsetTop;
    // Check if is the user turn 
    // if not send a alert is not your turn and return
      
      
    // Gets the column clicked
    var column = Math.floor(clickX/100);

    var arguments = $(this).serialize();
    var url = "<?= base_url() ?>board/play";
	$.getJSON(url, function (data,text,jqXHR){
		$.post(url,arguments, function (data,textStatus,jqXHR){
			var conversation = 2;
			var msg = 3;
		});	
	});
    // Call method to insert in that column for the current user
    // it update the table
      
      
    //alert(column);
    getDiscs();
      
      
    //addDisc(clickX,clickY, "blue");
    return;
}
  
  
function clearCanvas() {
    // Remove all the circles.
    discs = [];

    // Update the display.
    drawDiscs();
}
  
function getDiscs()
{  
    clearCanvas();
	
    
    var board_array = new Array();
    board_array[0] = new Array();
      
    board_array[0].push("green");
    board_array[0].push("blue");
    board_array[1] = new Array();
    board_array[1].push("yellow");
    board_array[2] = new Array();
    board_array[3] = new Array();
    board_array[4] = new Array();
    board_array[5] = new Array();
    board_array[6] = new Array();
     
    for (var column = 0; column<board_array.length; column++)
    {
        for (var row =0; row<board_array[column].length; row++)
        {
              
            var x = 50 + 100*column;
            var y = 550 - 100*row;
            var color = board_array[column][row];
              
              
            addDisc(x, y, color );  
                          
        }
    }
    drawDiscs();
}
  
</script>

</html>

