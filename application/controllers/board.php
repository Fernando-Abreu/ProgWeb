<?php

class Board extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    } 
          
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	
    		
    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
    
    
    function index() {
		$user = $_SESSION['user'];
    		    	
	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');
	    	
	    	$user = $this->user_model->get($user->login);

	    	$invite = $this->invite_model->get($user->invite_id);
	    	
	    	if ($user->user_status_id == User::WAITING) {
	    		$invite = $this->invite_model->get($user->invite_id);
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    	}
	    	else if ($user->user_status_id == User::PLAYING) {
	    		$match = $this->match_model->get($user->match_id);
	    		if ($match->user1_id == $user->id)
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    		else
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    	}
	    	
	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;
	    	
	    	switch($user->user_status_id) {
	    		case User::PLAYING:	
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}
	    	
		$this->load->view('match/board',$data);
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');
 		
 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];
 			 
 			$user = $this->user_model->getExclusive($user->login);
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			
 			$msg = $this->input->post('msg');
 			
 			if ($match->user1_id == $user->id)  {
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}
 				
 			echo json_encode(array('status'=>'success'));
 			 
 			return;
 		}
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();
 			
 		$match = $this->match_model->getExclusive($user->match_id);			
 			
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		echo json_encode(array('status'=>'success','message'=>$msg));
		return;
		
		transactionerror:
		$this->db->trans_rollback();
		
		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 	/*
 	 * returns in json:
 	 *   board - an array of arrays
 	 *   victory - Boolean, true if someone won, false if still in game
 	 *   user - the id of the user who won, or the user that should play
 	 *   columns - an array of valid columns to put a disc (not full)
 	 */

 	function check_for_updates() {
 		
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 		$this->load->model('game');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
		
 		$match = $this->match_model->get($user->match_id);
		$game = unserialize($match->board_state);
		$board = $game->getBoard();
		$columns = $game->getValidColumns();
		$win = $game->checkWin();
		if ($win==0) {
			$user = $game->getTurn();
			$victory = false;
		} else {
			$user = $win;
			$victory = true;
		}
 		echo json_encode(array('board'=>$board,
 								'victory'=>$victory,
 								'user'=>$user,
 								'columns'=>$columns));
 		return;
 		
 		error:
 		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 	function play($column) {
 		//updates the board object in the db
 		
 		echo '<script type="text/javascript">alert("' . $msg . '"); </script>';
 		
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 		$this->load->model('game');
 		
 		$user = $_SESSION['user'];
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}

 		// start transactional mode
 		$this->db->trans_begin();
 		
 		$match = $this->match_model->getExclusive($user->match_id);

 		$board_state = $match->board_state;
 		$game = unserialize($board_state);
 		if (!($game->isValidMove($column, $user->id))) {
 			$errormsg="Invalid move";
 			goto transactionerror;
 		}
 		$game->insertToColumn($column, $user->id);
 		$win = $game->checkWin();
 		if ($win != 0) {
 			$win = ($win==$match->user1_id)? (Match::U1WON):(Match::U2WON);
 			$match = $this->match_model->updateStatus($win);
 		}
 
 		$board_state = serialize($board_state);
 		$this->match_model->updateBoard($match->id,$board_state);
 	
 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 			
 		// if all went well commit changes
 		$this->db->trans_commit();
 			
 		echo json_encode(array('status'=>'success'));
 		return;
 		
 		transactionerror:
 		$this->db->trans_rollback();
 		
 		error:
 		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 }

