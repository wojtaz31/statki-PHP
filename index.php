<?php

class Board
{
    public int $width;
    public int $heigth;
    public array $tiles;

    function __construct(int $width = 10, int $heigth = 10)
    {
        $this->width = $width;
        $this->heigth = $heigth;
        for ($i = 0; $i < $width; $i++) $this->tiles[chr(65 + $i)] = array_fill(0, $heigth, 0);
    }

    function inject_board_styles()
    {
        echo "<style>";
        echo "#board-container {";
        echo "    display: grid;";
        echo "    grid-template-columns: repeat(10, 100px);";
        echo "    grid-template-rows: repeat(10, 100px);";
        echo "    justify-content: center;";
        echo "    align-content: center;";
        echo "}";
        echo ".tile {";
        echo "    height: 100px;";
        echo "    width: 100px;";
        echo "    border: solid black 2px;";
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
