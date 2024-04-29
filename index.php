<?php

class Board
{
    public int $width;
    public int $heigth;
    public int $pxlTileSize;
    public array $tiles;

    function __construct(int $width = 10, int $heigth = 10, int $pxlTileSize = 80)
    {
        $this->width = $width;
        $this->heigth = $heigth;
        $this->pxlTileSize = $pxlTileSize;
        for ($i = 0; $i < $width; $i++) $this->tiles[chr(65 + $i)] = array_fill(0, $heigth, 0);
    }

    function inject_board_styles()
    {   
        echo '<link rel="stylesheet" href="styles.css">';
        echo "<style>";
        echo "#board-container {";
        echo "    display: grid;";
        echo "    grid-template-columns: repeat($this->width, $this->pxlTileSize"."px);";
        echo "    grid-template-rows: repeat($this->heigth, $this->pxlTileSize"."px);";
        echo "    justify-content: center;";
        echo "    align-content: center;";
        echo "    width: max(100%, calc({$this->pxlTileSize}px * $this->width));";
        echo "    height: max(100%, calc({$this->pxlTileSize}px * $this->heigth));";
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
        for ($i = 0; $i < $this->heigth; $i++) {
            for ($j = 0; $j < $this->width; $j++) echo '<div class="tile"></div>';
        }
        echo '</div>';
    }
}

$game = new Board();
$game->create_board();

?>