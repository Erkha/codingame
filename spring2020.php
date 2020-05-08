<?php
 class Pac 
 {
    public $pacId;
    public $mine;
    public $x;
    public $y;
    public $typeId;
    public $speedTurnsLeft;
    public $abilityCooldown;
    
    public function __construct($pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown)
    {
        $this->pacId = $pacId;
        $this->mine = $mine;
        $this->x = $x;
        $this->y = $y;
        $this->typeId = $typeId;
        $this->speedTurnsLeft = $speedTurnsLeft;
        $this->abilityCooldown = $abilityCooldown;
    }
 }

 class Map
 {
     public $coords;

     public function __construct()
     {
         $this->coords = array();
     }

     public function updateCoord($x, $y, $status ="U"){
         $this->coords[$x . '-' . $y] = $status;
     }
 }
 
 
/**
 * Grab the pellets as fast as you can!
 **/

$pacs = array();
$map = new Map();
fscanf(STDIN, "%d %d", $width, $height); // $width: size of the grid
for ($i = 0; $i < $height; $i++) { // $height: top left corner is (x=0, y=0)
    $row = str_split(stream_get_line(STDIN, $width + 1, "\n")); // one line of the grid: space " " is floor, pound "#" is wall
    foreach ($row as $index => $cell) {
        if ($cell == " ") {
            $map->updateCoord($index, $i);
        }
    }
}

// game loop
while (TRUE) {
    $collides=array();
    fscanf(STDIN, "%d %d", $myScore, $opponentScore);

    fscanf(STDIN, "%d", $visiblePacCount);
    
    for ($i = 0; $i < $visiblePacCount; $i++) {
        fscanf(STDIN, "%d %d %d %d %s %d %d", $pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown);
        $player = $mine ? '1' : '2';
        $pacs[$pacId.'-'.$player] = new Pac($pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown);
    }

    fscanf(STDIN, "%d", $visiblePelletCount);
    $superPellets = array();
    for ($i = 0; $i < $visiblePelletCount; $i++) {
        fscanf(STDIN, "%d %d %d", $x, $y, $value);
        $status= $value == 1 ? "p" : "P";
        $map->updateCoord($x,$y,$status);
        if ($value == 10) {
            $superPellets[] = [$x,$y];
        }
    }

    $moves = "";
    foreach ($pacs as $pac) {
        $pacCoords = $pac->x.'-'.$pac->y;
        $map->updateCoord($pac->x, $pac->y,"V");
        if ($pac->mine == true) {
            $potentialPellet = array_search('p',$map->coords);
            if (!empty($superPellets)) {
                $selectedSuperPellet = array_shift($superPellets);
                $xDest = $selectedSuperPellet[0];
                $yDest = $selectedSuperPellet[1];
                error_log('pac'.$pac->pacId.":"."super".$xDest."-".$yDest);
            }elseif($potentialPellet){
              $coords = explode('-',$potentialPellet);
              $xDest = $coords[0];
              $yDest = $coords[1];
              error_log('pac'.$pac->pacId.":"."pot".$xDest."-".$yDest);
            }else{
                $potentialPellet = array_rand($map->coords);
                $coords = explode('-',$potentialPellet);
                $xDest = $coords[0];
                $yDest = $coords[1];
                error_log('pac'.$pac->pacId.":"."rand".$xDest."-".$yDest);
            }
            $pacCoords = $xDest.'-'.$yDest;
            $collides[$pacCoords] = 'mine';
            $moves .= "MOVE " . $pac->pacId . " " . $xDest . " " . $yDest . " | "; // MOVE <pacId> <x> <y>
        }
    }
    echo substr($moves, 0, -3) . "\n";
}
