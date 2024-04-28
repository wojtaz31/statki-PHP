<?php

class Board{
    public int $width;
    public int $heigth;
    public array $tiles;

    function __construct(int $width = 10, int $heigth = 10)
    {
        $this->width = $width;
        $this->heigth = $heigth;
        for ($i=0; $i < $width; $i++) $this->tiles[chr(65 + $i)] = array_fill(0, $width, 0);
    }
}

?>