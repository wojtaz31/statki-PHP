<?php

enum TileState
{
    const FREE = 0b00;
    const EXCLUDED = 0b01;
    const OCCUPIED = 0b10;
}

class Board
{
    public int $width;
    public int $height;
    public int $pxlTileSize;
    public array $tiles;

    function __construct(int $width = 10, int $height = 10, int $pxlTileSize = 80)
    {
        $this->width = $width;
        $this->height = $height;
        $this->pxlTileSize = $pxlTileSize;
        for ($i = 0; $i < $width; $i++) $this->tiles[$i] = array_fill(0, $height, TileState::FREE);
    }

    private function getUnavailableTiles()
    {
        $unavailableTiles = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->tiles[$x][$y] === TileState::EXCLUDED || $this->tiles[$x][$y] === TileState::OCCUPIED) {
                    array_push($unavailableTiles, [$x, $y]);
                }
            }
        }

        return $unavailableTiles;
    }


    function inject_board_styles()
    {
        echo '<link rel="stylesheet" href="styles.css">';
        echo "<style>";
        echo "#board-container {";
        echo "    display: grid;";
        echo "    grid-template-columns: repeat($this->width, $this->pxlTileSize" . "px);";
        echo "    grid-template-rows: repeat($this->height, $this->pxlTileSize" . "px);";
        echo "    justify-content: center;";
        echo "    align-content: center;";
        echo "    width: max(100%, calc({$this->pxlTileSize}px * $this->width));";
        echo "    height: max(100%, calc({$this->pxlTileSize}px * $this->height));";
        echo "}";
        echo ".tile {";
        echo "    height: {$this->pxlTileSize}px;";
        echo "    width: {$this->pxlTileSize}px;";
        echo "    outline: solid black 2px;";
        echo "}";
        echo "</style>";
    }

    function create_board()
    {
        $this->inject_board_styles();
        echo '<div id="board-container">';
        for ($i = 0; $i < $this->height; $i++) {
            for ($j = 0; $j < $this->width; $j++) {
                $class = chr(65 + $i) . $j;
                echo "<div class=\"tile $class\"></div>";
            }
        }
        echo '</div>';
    }

    function placeSingleShip(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $valid = false;
            $x = 0;
            $y = 0;

            while (!$valid) {
                $x = rand(0, $this->width - 1);
                $y = rand(0, $this->height - 1);

                if ($this->tiles[$x][$y] === TileState::FREE) $valid = true;
            }
            $this->tiles[$x][$y] = TileState::OCCUPIED;
            $class = chr(65 + $x) . $y;
            echo "<script>document.querySelector('.$class').classList.add('one-mast');</script>";
            $this->excludeAdjacent($x, $y);
        }
    }

    private function canPlaceShipHorizontally(int $startX, int $startY, int  $shipSize)
    {
        if ($startX +  $shipSize > $this->width) return false;

        for ($i = $startX; $i < $startX +  $shipSize; $i++) {
            if ($this->tiles[$i][$startY] !== TileState::FREE) return false;
        }

        return true;
    }

    private function canPlaceShipVertically(int $startX, int $startY, int $shipSize)
    {
        if ($startY + $shipSize > $this->height) return false;

        for ($i = $startY; $i < $startY +  $shipSize; $i++) {
            if ($this->tiles[$startX][$i] !== TileState::FREE) return false;
        }

        return true;
    }

    private function checkTile(int $x, int $y)
    {
        if ($x < 0 || $y < 0 || $x > $this->width - 1 || $y > $this->height - 1) {
            return false;
        }
        if ($this->tiles[$x][$y] === TileState::FREE) return true;
        else return false;
    }

    private function updatePossiblePlacements(array &$possiblePlacements)
    {
        $unavailableTiles = $this->getUnavailableTiles();

        $possiblePlacements = array_filter($possiblePlacements, function ($placement) use ($unavailableTiles) {
            foreach ($placement as $coord) if (in_array($coord, $unavailableTiles)) return false;
            return true;
        });
    }

    private function getShipClass(int $shipSize)
    {
        switch ($shipSize) {
            case 1:
                return 'one-mast';
            case 2:
                return 'two-mast';
            case 3:
                return 'three-mast';
            case 4:
                return 'four-mast';
            default:
                return '';
        }
    }

    private function placeShip(array $placement, int $shipSize)
    {
        $tileClass = $this->getShipClass($shipSize);
        $classString = "";
        foreach ($placement as $cord) {
            $this->tiles[$cord[0]][$cord[1]] = TileState::OCCUPIED;
            $classString .= "." . chr(65 + $cord[0]) . $cord[1] . ",";
            $this->excludeAdjacent($cord[0], $cord[1]);
        }
        $classString = substr($classString, 0, -1);
        echo "<script>";
        echo "document.querySelectorAll('$classString').forEach(e => e.classList.add('$tileClass'));";
        echo "</script>";
    }

    public function placeDoubleShip(int $number)
    {
        $possiblePlacements = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->canPlaceShipHorizontally($x, $y, 2)) {
                    array_push($possiblePlacements, [[$x, $y], [$x + 1, $y]]);
                }
                if ($this->canPlaceShipVertically($x, $y, 2)) {
                    array_push($possiblePlacements, [[$x, $y], [$x, $y + 1]]);
                }
            }
        }

        for ($i = 0; $i < $number; $i++) {
            if (!empty($possiblePlacements)) {
                $placement = $possiblePlacements[array_rand($possiblePlacements)];
                $this->placeShip($placement, 2);
                $this->updatePossiblePlacements($possiblePlacements);
            }
        }
    }

    function placeTripleShip(int $number)
    {
        $possiblePlacements = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->canPlaceShipHorizontally($x, $y, 3)) {
                    array_push($possiblePlacements, [[$x, $y], [$x + 1, $y], [$x + 2, $y]]);
                }
                if ($this->canPlaceShipVertically($x, $y, 3)) {
                    array_push($possiblePlacements, [[$x, $y], [$x, $y + 1], [$x, $y + 2]]);
                }
                if ($this->checkTile($x, $y)) {
                    if ($this->checkTile($x, $y - 1) && $this->checkTile($x + 1, $y - 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x, $y - 1], [$x + 1, $y - 1]]);
                    }
                    if ($this->checkTile($x, $y + 1) && $this->checkTile($x + 1, $y + 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x, $y + 1], [$x + 1, $y + 1]]);
                    }
                    if ($this->checkTile($x + 1, $y) && $this->checkTile($x + 1, $y - 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x + 1, $y], [$x + 1, $y - 1]]);
                    }
                    if ($this->checkTile($x + 1, $y) && $this->checkTile($x + 1, $y + 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x + 1, $y], [$x + 1, $y + 1]]);
                    }
                }
            }
        }

        for ($i = 0; $i < $number; $i++) {
            if (!empty($possiblePlacements)) {
                $placement = $possiblePlacements[array_rand($possiblePlacements)];
                $this->placeShip($placement, 3);
                $this->updatePossiblePlacements($possiblePlacements);
            }
        }
    }

    function excludeAdjacent(int $x, int $y)
    {
        $this->excludeCell($x - 1, $y);
        $this->excludeCell($x + 1, $y);
        $this->excludeCell($x, $y - 1);
        $this->excludeCell($x, $y + 1);

        $this->excludeCell($x - 1, $y - 1);
        $this->excludeCell($x + 1, $y - 1);
        $this->excludeCell($x - 1, $y + 1);
        $this->excludeCell($x + 1, $y + 1);
    }

    private function excludeCell(int $x, int $y)
    {
        if ($x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height) {
            if ($this->tiles[$x][$y] === TileState::FREE) {
                $this->tiles[$x][$y] = TileState::EXCLUDED;
            }
        }
    }
}

$game = new Board();
$game->create_board();
$game->placeTripleShip(2);
$game->placeDoubleShip(3);
$game->placeSingleShip(4);
