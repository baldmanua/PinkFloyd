<?php

class File{
	private $content = array('File not set.');
	private $valid = false;
	private $name = '';
	private static $_instance = null;

	/**
	* @return File
	*/
	public static function getInstance(){
		if (null === self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {}

	protected function __clone() {}

	/**
	 *
	 * @param string $fileName
	 *
	 * @return $this
	 */
	public function import($fileName) {
		if(file_exists($fileName)){
			$this->content = file($fileName, FILE_IGNORE_NEW_LINES);
			$this->name = $fileName;
			$this->valid = true;
		}else{
			$this->content = array("File $this->name not found.");
			$this->valid = false;
		}
		return $this;
	}

	/**
	 * @param Wall $wall
	 * @param array $bricks
	 *
	 * @return bool
	 */
	public function parse(&$wall, &$bricks){
		if(!$this->valid){
			return false;
		}
		$tempContent = $this->content;
		$counter = 0;
		
		$wallSize = explode(" ", $tempContent[0]);
		if(count($wallSize) != 2 || !is_numeric($wallSize[0]) || !is_numeric($wallSize[1])){
			$this->valid = false;
			$this->content = array("File $this->name contents error(s) on line 1");
			return false;
		}
		unset($tempContent[0]);

		$wallContent = "";
		for($i = 1; $i <= $wallSize[1]; $i++){
			if(!isset($tempContent[$i]) || strlen($tempContent[$i]) != $wallSize[0]){
				$this->valid = false;
				$this->content = array("File $this->name contents error(s) on line " . ($i + 1));
				return false;
			}
			$wallContent .= $tempContent[$i];
			unset($tempContent[$i]);
			$counter = $i + 1;
		}
		$wall = new Wall($wallSize[0], $wallSize[1], $wallContent);

		if(!isset($tempContent[$counter]) || !is_numeric($tempContent[$counter])){
			$this->valid = false;
			$this->content = array("File $this->name contents error(s) on line " . ($counter + 1));
			return false;
		}
		$bricksQuantity = $tempContent[$counter];
		unset($tempContent[$counter]);
		++$counter;

		$bricks = array();
		for($i = 0; $i < $bricksQuantity; $i++){
			if(!isset($tempContent[$i + $counter])){
				$this->valid = false;
				$this->content = array("File $this->name contents error(s) on line " . ($i + $counter + 1));
				return false;
			}
			$brick = explode(" ", $tempContent[$i + $counter]);
			if(count($brick) != 3 || !is_numeric($brick[0]) || !is_numeric($brick[1]) || !is_numeric($brick[2])){
				$this->valid = false;
				$this->content = array("File $this->name contents error(s) on line " . ($i + $counter + 1));
				return false;
			}
			$bricks[] = new Brick($brick[0], $brick[1], $brick[2]);
		}
		return true;
	}
}

class Brick{
	public $width;
	public $height;
	public $quantity;

	/**
	 * Brick constructor.
	 *
	 * @param $width
	 * @param $height
	 * @param $quantity
	 */
	public function __construct($width, $height, $quantity){
		$this->width = (int)$width;
		$this->height = (int)$height;
		$this->quantity = (int)$quantity;
	}

	/**
	 * @return int
	 */
	public function spend(){
		$this->quantity -= 1;
		return $this->quantity;
	}

	/**
	 * @return float|int
	 */
	public function getSquare(){
		return ($this->width * $this->height * $this->quantity);
	}

	/**
	 * @return float|int
	 */
	public function getTotalSquare(){
		return ($this->getSquare() * $this->quantity);
	}
}

class Wall{
	public $width;
	public $height;
	public $map;
	public $square;

	/**
	 * Wall constructor.
	 *
	 * @param $width
	 * @param $height
	 * @param $map
	 */
	public function __construct($width, $height, $map){
		$this->width = (int)$width;
		$this->height = (int)$height;
		$cnt = 0;
		for($i = 0; $i < $height; $i++){
			for($j = 0; $j < $width; $j++){
				$this->map[$i][$j] = (int)$map[$cnt];
				$this->square+= $map[$cnt];
				++$cnt;
			}
		}
	}

	/**
	 *
	 */
	public function draw(){
		foreach ($this->map as $str) {
			foreach ($str as $col) {
				echo $col;
			}
			echo "\n";
		}
	}

	/**
	 * @param $bricks
	 *
	 * @return bool
	 */
	public function checkSize($bricks){
		$square = 0;
		foreach ($bricks as $brick) {
			$square+= $brick->getTotalSquare();
		}
		return $square >= $this->square? true: false;
	}


	/**
	 * @param array $bricks
	 * @param int $brick
	 * @param array $place
	 *
	 * @return mixed
	 */
	public function placeBrick($bricks, $brick, $place){
		if($place['reverse']){
			$brickWidth = $bricks[$brick]->height;
			$brickHeight = $bricks[$brick]->width;
		}else{
			$brickWidth = $bricks[$brick]->width;
			$brickHeight = $bricks[$brick]->height;
		}
		for($i=0; $i < $brickHeight; $i++){
			for($j = 0; $j < $brickWidth; $j++){
				$this->map[$i+$place['top']][$j+$place['left']] = 0;
				$this->square -= 1;
			}
		}
		if(!$bricks[$brick]->spend()){
			//echo
			unset($bricks[$brick]);
		}
		return $bricks;
	}
}


/**
 * @param Wall $wall
 * @param Brick $brick
 * @param int $top
 * @param int $left
 * @param bool $reverse
 *
 * @return bool
 */
function checkPlace(Wall $wall, Brick $brick, $top, $left, $reverse){
	if($reverse){
		$brickWidth = $brick->height;
		$brickHeight = $brick->width;
	}else{
		$brickWidth = $brick->width;
		$brickHeight = $brick->height;
	}
	for($i=0; $i < $brickHeight; $i++){
		for($j = 0; $j < $brickWidth; $j++){
			if(!$wall->map[$i+$top][$j+$left]){
				return false;
			}
		}
	}
	return true;
}

/**
 * @param Wall $wall
 * @param Brick $brick
 *
 * @return array
 */
function findPlaces(Wall $wall, Brick $brick){
		$brickWidth = $brick->width;
		$brickHeight = $brick->height;
	$places = array();
	for($i=0; $i <= ($wall->height - $brickHeight); $i++){
		for($j=0; $j <= ($wall->width - $brickWidth); $j++){
			if(checkPlace($wall, $brick, $i, $j, false)){
				$places[] = array(
					'reverse' => false,
					'top' => $i,
					'left' => $j
				);
			}elseif($brickWidth!=$brickHeight && checkPlace($wall, $brick, $i, $j, true)){
				$places[] = array(
					'reverse' => true,
					'top' => $i,
					'left' => $j
				);
			}
		}
	}
	return $places;
}

function recursiveAdd(Wall $wall, array $bricks){
	foreach ($bricks as $key => $brick) {
		for($i=0;$i<$brick->quantity;$i++){
			$places = findPlaces($wall, $brick);
			foreach ($places as $place){
				$bricks = $wall->placeBrick($bricks, $key, $place);

				if(empty($bricks) && $wall->square != 0){
					continue;
				}elseif ($wall->square == 0){
					return true;
				}else{
					return recursiveAdd($wall, $bricks);
				}
			}
		}
	}
	return false;
}


$data = File::getInstance()->import($argv[1]);

$wall;
$bricks;
if(!$data->parse($wall, $bricks) || !$wall->checkSize($bricks)){
	$result = false;
	exit();
}else{
	$result = recursiveAdd($wall,$bricks);
}

	echo $result? "yes": "no";
	echo "\n";