<?php
  
class Game {
  
    private $board; // an array of columns, each column is an array that holds
    				// 1 or 2 for the playes discs, 0 for empty, -1 or -2 for winning discs
    private $players; // an array of thw two users ids
    private $turn; // holds the number of the user that chould play next
    private $win; //holds the id of the user who won, or 0 if no one won yet
  
    public function __construct() {
        $this->board = array(  array(0,0,0,0,0,0),
                array(0,0,0,0,0,0),
                array(0,0,0,0,0,0),
                array(0,0,0,0,0,0),
                array(0,0,0,0,0,0),
                array(0,0,0,0,0,0),
                array(0,0,0,0,0,0)  );
        $this->win = 0; // 0 or player id
        $this->turn = 2;
    }
    
    public function defineUsers($u1,$u2) {
    	$this->players = array(1=>$u1, 2=>$u2);
    }
  
    public function getBoard(){
        return $this->board;
    }
  
    public function getTurn(){
    	if ($this->win != 0) {
    		return 0;
    	}
        return $this->players[$this->turn];
    }
  
    public function checkWin(){
        return $this->win;
    }
    
    public function getValidColumns(){
    	$c = array();
    	foreach (array(0,1,2,3,4,5,6) as $column){
    		if ($this->board[$column][5]==0){
    			$c[] = $column;
    		}
    	}
    	return $c;
    }
    
    public function isValidMove($column, $userid){
    	$userValid = ($this->win==0 && $this->players[$this->turn]==$userid);
    	$columnValid = ($column>=0 && $column<=6 && $this->board[$column][5]==0);
    	return $userValid && $columnValid;
    }
  
    // should be called only after isValidMove
    public function insertToColumn($column, $userid){
    	
    	//calculate users number
    	$usernum = ($userid==$this->players[1] ? 1 : 2);

        // calculate row number
        foreach (array(0,1,2,3,4,5) as $row){
            if ($this->board[$column][$row]==0){
                break;
            }
        }
        // update board and turn
        $this->board[$column][$row] = $usernum;
        $this->turn = ($usernum==1? 2 : 1);
  
        // update win
        $this->updateWin($column, $row, $usernum);
    }
    
    // given the details of the last inserted disc, updates win
    public function updateWin($column, $row, $usernum){
    	$victory = false;
    	// check row
    	$counter = 0;
    	foreach (array(0,1,2,3,4,5,6) as $i){
    		if ($this->board[$i][$row]==$usernum){
    			$counter++;
    			if ($counter>=4){
    				$victory = true;
    				foreach (array(0,1,2,3) as $j){
    					$this->board[$i-$j][$row]=$usernum*(-1);
    				}
    			}
    		} else {
    			$counter = 0;
    		}
    	}
    	// check column
    	$counter = 0;
    	foreach (array(0,1,2,3,4,5) as $i){
    		if ($this->board[$column][$i]==$usernum){
    			$counter++;
    			if ($counter>=4){
    				$victory = true;
    				foreach (array(0,1,2,3) as $j){
    					$this->board[$column][$i-$j]=$usernum*(-1);
    				}
    			}
    		} else {
    			$counter = 0;
    		}
    	}
    	// check / diagonal
    	$counter = 0;
    	foreach (array(0,1,2,3,4,5) as $r){
    		$c=$r+$column-$row;
    		if ($c>=0 && $c<=6){
    			if ($this->board[$c][$r]==$usernum){
    				$counter++;
    				if ($counter>=4){
    					$victory = true;
    					foreach (array(0,1,2,3) as $j){
    						$this->board[$c-$j][$r-$j]=$usernum*(-1);
    					}
    				}
    			} else {
    				$counter = 0;
    			}
    		}
    	}
    	// check \ diagonal
    	$counter = 0;
    	foreach (array(0,1,2,3,4,5) as $r){
    		$c=$column+$row-$r;
    		if ($c>=0 && $c<=6){
    			if ($this->board[$c][$r]==$usernum){
    				$counter++;
    				if ($counter>=4){
    					$victory = true;
    					foreach (array(0,1,2,3) as $j){
    						$this->board[$c+$j][$r-$j]=$usernum*(-1);
    					}
    				}
    			} else {
    				$counter = 0;
    			}
    		}
    	}
    	// save result
    	if ($victory){
    		$this->win = $this->players[$usernum];
    		$this->turn = 0;
    	}
    }
  
    //for debug
    public function printAll(){
        echo 'board: ';
        $b = $this->getBoard();
        foreach (array(0,1,2,3,4,5,6) as $c){
            foreach (array(0,1,2,3,4,5) as $r){
                echo $b[$c][$r];
            }
            echo ' ';
        }
        echo '</br>players: '.strval($this->players[1]).', '.strval($this->players[2]);
        echo '</br>turn: '. strval($this->getTurn());
        echo '</br>victory: '.strval($this->checkWin());
        echo '</br>';
    }
  
} 