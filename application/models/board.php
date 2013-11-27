<?php

class Board {

	private $board;
	private $players;
	private $turn;
	private $win;

	public function __construct($u1, $u2) {
		$this->board = array(  array(0,0,0,0,0,0),
								array(0,0,0,0,0,0),
								array(0,0,0,0,0,0),
								array(0,0,0,0,0,0),
								array(0,0,0,0,0,0),
								array(0,0,0,0,0,0),
								array(0,0,0,0,0,0)  );
		$this->players = array($u1,$u2);
		$this->turn = $u1;
		$this->win = 0;
	}
	
	public function getBoard(){
		return $this->board;
	}
	
	public function getTurn(){
		return $this->turn;
	}

	public function checkWin(){
		return $this->win;
	}

	// column should be between 0 and 6
	// usernum should be active player
	public function insertToColumn($column, $usernum){

		// validate user number
		if ($usernum!=$this->turn){
			return 'error';
		}
		// validate column number
		if ($column<0 || $column>6){
			return 'error';
		}
		if ($this->board[$column][5]!=0){
			return 'error';
		}
		// calculate row number
		foreach (array(0,1,2,3,4,5) as $row){
			if ($this->board[$column][$row]==0){
				break;
			}
		}
		// update board
		$this->board[$column][$row] = $usernum;

		// update win
		$victory = false;
		// check row
		$counter = 0;
		foreach (array(0,1,2,3,4,5,6) as $i){
			if ($this->board[$i][$row]==$usernum){
				$counter++;
				if ($counter>=4){
					$victory = true;
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
					}
				} else {
					$counter = 0;
				}
			}
		}
		// save result
		if ($victory){
			$this->win = $usernum;
			$this->turn = 0;
		} else {
			// update turn
			$this->turn = ($usernum==$this->players[0]?$this->players[1]:$this->players[0]);
		}
	}

}
