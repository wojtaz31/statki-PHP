<?php

class Board{
    public int $width;
    public int $heigth;
    public array $tiles;

    function __construct(int $width = 10, int $heigth = 10)
    {
        $this->width = $width;
        $this->heigth = $heigth;
        for ($i=0; $i < $width; $i++) $this->tiles[chr(65 + $i)] = array_fill(0, $heigth, 0);
    }

    function create_board()
    {   
        echo '<link rel="stylesheet" href="styles.css">';
        echo '<div id="board-container">';
        for ($i = 0; $i < $this->heigth; $i++) {
            for ($j = 0; $j < $this->width; $j++) echo '<div class="tile"></div>';
        }
        echo '</div>';
    }
}

$game = new Board();
$game ->create_board();
?>