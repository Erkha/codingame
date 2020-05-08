<?php

/**
 * Grab the pellets as fast as you can!
 **/

/* initialize map */
$map = array();
fscanf(STDIN, "%d %d", $width, $height); // $width: size of the grid
for ($i = 0; $i < $height; $i++) { // $height: top left corner is (x=0, y=0)
    $row = str_split(stream_get_line(STDIN, $width + 1, "\n")); // one line of the grid: space " " is floor, pound "#" is wall
    foreach ($row as $index => $cell) {
        if ($cell == " ") {
            $coord = $index . '-' . $i;
            $map[$coord] = "U";
        }
    }
}

// game loop
while (TRUE) {
    $collides=array();
    fscanf(STDIN, "%d %d", $myScore, $opponentScore);

    fscanf(STDIN, "%d", $visiblePacCount);
    $pacs = array();
    for ($i = 0; $i < $visiblePacCount; $i++) {
        fscanf(STDIN, "%d %d %d %d %s %d %d", $pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown);
        $pacs[] = array(
            'pacId'             => $pacId,          // pac number (unique within a team)
            'mine'              => $mine,           // true if this pac is yours
            'x'                 => $x,              // position in the grid
            'y'                 => $y,              // position in the grid
            'typeId'            => $typeId,         // unused in wood leagues
            'speedTurnsLeft'    => $speedTurnsLeft, //unused in wood leagues
            'abilityCooldown'   => $abilityCooldown //unused in wood leagues
        );
        $pacCoords = $pac['x'].'-'.$pac['y'];
        $collides[$pacCoords]= $mine ? 'mine' : $typeId;
    }

    fscanf(STDIN, "%d", $visiblePelletCount);
    $superPellets = array();
    for ($i = 0; $i < $visiblePelletCount; $i++) {
        fscanf(STDIN, "%d %d %d", $x, $y, $value);
        $pelletCoord = $x . '-' . $y;
        $map[$pelletCoord] = $value == 1 ? "p" : "P";
        if ($value == 10) {
            $superPellets[] = [$x,$y];
        }
    }

    $moves = "";
    shuffle($pacs);
    foreach ($pacs as $pac) {
        $pacCoords = $pac['x'].'-'.$pac['y'];
        $map[$pacCoords] = "V";
        $collides[] = [$pac['x'], $pac['y']];
        if ($pac['mine'] == true) {
            $potentialPellet = array_search('p',$map);
            if (!empty($superPellets)) {
                $selectedSuperPellet = array_shift($superPellets);
                $xDest = $selectedSuperPellet[0];
                $yDest = $selectedSuperPellet[1];
                error_log('pac'.$pac['pacId'].":"."super".$xDest."-".$yDest);
            }elseif($potentialPellet){
              $coords = explode('-',$potentialPellet);
              $xDest = $coords[0];
              $yDest = $coords[1];
              error_log('pac'.$pac['pacId'].":"."pot".$xDest."-".$yDest);
            }else{
                $potentialPellet = array_rand($map);
                $coords = explode('-',$potentialPellet);
                $xDest = $coords[0];
                $yDest = $coords[1];
                error_log('pac'.$pac['pacId'].":"."rand".$xDest."-".$yDest);
            }
            $pacCoords = $xDest.'-'.$yDest;
            $collides[$pacCoords] = 'mine';
            $moves .= "MOVE " . $pac['pacId'] . " " . $xDest . " " . $yDest . " | "; // MOVE <pacId> <x> <y>
        }
    }
    echo substr($moves, 0, -3) . "\n";
}
