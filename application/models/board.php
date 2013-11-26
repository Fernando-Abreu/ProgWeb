<?php

class Board {

	const NOWIN = 0;
	const U1WON = 1; //the usernum of the winning user is the constant value!
	const U2WON = 2;

	private $board = array(array(0,0,0,0,0,0),
			array(0,0,0,0,0,0),
			array(0,0,0,0,0,0),
			array(0,0,0,0,0,0),
			array(0,0,0,0,0,0),
			array(0,0,0,0,0,0),
			array(0,0,0,0,0,0));

	private $win = self::NOWIN;

	public function getBoard(){
		return $board;
	}

	public function checkWin(){
		return $win;
	}

	// column should be between 0 and 6
	// usernum should be 1 or 2
	public function insertToColumn($column, $usernum){

		// validate user number
		if ($usernum!=1 && $usernum!=2){
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
			$win = $usernum;
		}
	}

}