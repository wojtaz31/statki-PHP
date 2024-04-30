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
            for ($j = 0; $j < $this->width; $j++) echo '<div class="tile"></div>';
        }
        echo '</div>';
    }

    function placeSingleShip()
    {
        $valid = false;
        $x = 0;
        $y = 0;

        while (!$valid) {
            $x = rand(0, $this->width - 1);
            $y = rand(0, $this->height - 1);

            if ($this->tiles[$x][$y] === TileState::FREE) $valid = true;
        }
        $this->tiles[$x][$y] = TileState::OCCUPIED;
        $this->excludeAdjacent($x, $y);
        echo '<pre>';
        print_r($this->tiles);
        echo '</pre>';
    }

    function excludeAdjacent(int $x, int $y)
    {
        $this->excludeCell($x - 1, $y);
        $this->excludeCell($x + 1, $y);
        $this->excludeCell($x, $y - 1);
        $this->excludeCell($x, $y + 1);
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
$game->placeSingleShip();
$game->placeSingleShip();
?>