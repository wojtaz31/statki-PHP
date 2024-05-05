<?php

echo '<link rel="stylesheet" href="styles.css">';
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

    function __construct(int $width = 10, int $height = 10, int $pxlTileSize = 70)
    {
        $this->width = $width;
        $this->height = $height;
        $this->pxlTileSize = $pxlTileSize;
        for ($i = 0; $i < $width; $i++) $this->tiles[$i] = array_fill(0, $height, [TileState::FREE, 0]);
    }

    private function getUnavailableTiles()
    {
        $unavailableTiles = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->tiles[$x][$y][0] === TileState::EXCLUDED || $this->tiles[$x][$y][0] === TileState::OCCUPIED) {
                    array_push($unavailableTiles, [$x, $y]);
                }
            }
        }

        return $unavailableTiles;
    }


    function inject_board_styles()
    {
        echo "<style>";
        echo "#board-container {";
        echo "    display: grid;";
        echo "    grid-template-columns: repeat($this->width, $this->pxlTileSize" . "px);";
        echo "    grid-template-rows: repeat($this->height, $this->pxlTileSize" . "px);";
        echo "    justify-content: center;";
        echo "    min-width: 100dvh;";
        echo "    width: fit-content;";
        echo "    height: fit-content;";
        echo "    margin: 0 auto;";
        echo "    padding: 1%;";
        echo "}";
        echo ".tile {";
        echo "    height: {$this->pxlTileSize}px;";
        echo "    width: {$this->pxlTileSize}px;";
        echo "    outline: solid black 1px;";
        echo "}";
        echo "</style>";
    }

    function create_board()
    {
        $this->inject_board_styles();
        echo '<div id="board-container">';
        for ($i = 0; $i < $this->height; $i++) {
            for ($j = 0; $j < $this->width; $j++) {
                $classString = $this->getColumnCode($j) . $i;
                echo "<div class=\"tile $classString\"></div>";
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
            $counter = 0;
            while (!$valid) {
                if ($counter > 500) throw new Exception("Błąd podczas generowania jednomasztowych statków");
                $x = rand(0, $this->width - 1);
                $y = rand(0, $this->height - 1);

                if ($this->tiles[$x][$y][0] === TileState::FREE) $valid = true;
                $counter += 1;
            }
            $this->tiles[$x][$y][0] = TileState::OCCUPIED;
            $this->tiles[$x][$y][1] = 1;
            $classString = $this->getColumnCode($x) . $y;
            echo "<script>document.querySelector('.$classString').classList.add('one-mast');</script>";
            $this->excludeAdjacent($x, $y);
        }
    }

    private function canPlaceShipHorizontally(int $startX, int $startY, int  $shipSize)
    {
        if ($startX < 0 || $startY < 0) return false;
        if ($startY > $this->height - 1) return false;
        if ($startX +  $shipSize > $this->width) return false;

        for ($i = $startX; $i < $startX +  $shipSize; $i++) {
            if ($this->tiles[$i][$startY][0] !== TileState::FREE) return false;
        }

        return true;
    }

    private function canPlaceShipVertically(int $startX, int $startY, int $shipSize)
    {
        if ($startX < 0 || $startY < 0) return false;
        if ($startX > $this->width - 1) return false;
        if ($startY + $shipSize > $this->height) return false;

        for ($i = $startY; $i < $startY +  $shipSize; $i++) {
            if ($this->tiles[$startX][$i][0] !== TileState::FREE) return false;
        }

        return true;
    }

    private function checkTile(int $x, int $y)
    {
        if ($x < 0 || $y < 0 || $x > $this->width - 1 || $y > $this->height - 1) {
            return false;
        }
        if ($this->tiles[$x][$y][0] === TileState::FREE) return true;
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
            case 0:
                return '';
            case 1:
                return 'one-mast';
            case 2:
                return 'two-mast';
            case 3:
                return 'three-mast';
            case 4:
                return 'four-mast';
            case 5:
                return 'five-mast';
            default:
                return '';
        }
    }

    private function getColumnCode($columnNumber)
    {
        $columnName = '';
        $base = 26;
        while ($columnNumber >= 0) {
            $remainder = $columnNumber % $base;
            $columnName = chr(65 + $remainder) . $columnName;
            $columnNumber = intval($columnNumber / $base) - 1;
        }

        return $columnName;
    }

    private function placeShip(array $placement, int $shipSize)
    {
        $tileClass = $this->getShipClass($shipSize);
        $classString = "";
        foreach ($placement as $cord) {
            $this->tiles[$cord[0]][$cord[1]][0] = TileState::OCCUPIED;
            $this->tiles[$cord[0]][$cord[1]][1] = $shipSize;
            $classString .= "." . $this->getColumnCode($cord[0]) . $cord[1] . ",";
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
            } else throw new Exception("Błąd podczas generowania dwumasztowych statków");
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
            } else throw new Exception("Błąd podczas generowania trzymasztowych statków");
        }
    }

    function placeQuadraShip(int $number)
    {
        $possiblePlacements = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->canPlaceShipHorizontally($x, $y, 4)) {
                    array_push($possiblePlacements, [[$x, $y], [$x + 1, $y], [$x + 2, $y], [$x + 3, $y]]);
                }
                if ($this->canPlaceShipVertically($x, $y, 4)) {
                    array_push($possiblePlacements, [[$x, $y], [$x, $y + 1], [$x, $y + 2], [$x, $y + 3]]);
                }
                if ($this->checkTile($x, $y)) {
                    if ($this->canPlaceShipHorizontally($x - 1, $y - 1, 3)) {
                        array_push($possiblePlacements, [[$x, $y], [$x - 1, $y - 1], [$x, $y - 1], [$x + 1, $y - 1]]);
                    }
                    if ($this->canPlaceShipHorizontally($x - 1, $y + 1, 3)) {
                        array_push($possiblePlacements, [[$x, $y], [$x - 1, $y + 1], [$x, $y + 1], [$x + 1, $y + 1]]);
                    }
                    if ($this->canPlaceShipVertically($x - 1, $y - 1, 3)) {
                        array_push($possiblePlacements, [[$x, $y], [$x - 1, $y - 1], [$x - 1, $y], [$x - 1, $y + 1]]);
                    }
                    if ($this->canPlaceShipVertically($x + 1, $y - 1, 3)) {
                        array_push($possiblePlacements, [[$x, $y], [$x + 1, $y - 1], [$x + 1, $y], [$x + 1, $y + 1]]);
                    }
                    if ($this->checkTile($x - 1, $y) && $this->checkTile($x, $y - 1) && $this->checkTile($x - 1, $y - 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x - 1, $y], [$x, $y - 1], [$x - 1, $y - 1]]);
                    }
                    if ($this->checkTile($x + 1, $y) && $this->checkTile($x, $y - 1) && $this->checkTile($x + 1, $y - 1)) {
                        array_push($possiblePlacements, [[$x, $y], [$x + 1, $y], [$x, $y - 1], [$x + 1, $y - 1]]);
                    }
                }
                if ($this->canPlaceShipHorizontally($x - 1, $y + 1, 3)) {
                    if ($this->checkTile($x - 1, $y)) array_push($possiblePlacements, [[$x - 1, $y], [$x - 1, $y + 1], [$x, $y + 1], [$x + 1, $y + 1]]);
                    if ($this->checkTile($x + 1, $y)) array_push($possiblePlacements, [[$x + 1, $y], [$x - 1, $y + 1], [$x, $y + 1], [$x + 1, $y + 1]]);
                }
                if ($this->canPlaceShipHorizontally($x - 1, $y - 1, 3)) {
                    if ($this->checkTile($x - 1, $y)) array_push($possiblePlacements, [[$x - 1, $y], [$x - 1, $y - 1], [$x, $y - 1], [$x + 1, $y - 1]]);
                    if ($this->checkTile($x + 1, $y)) array_push($possiblePlacements, [[$x + 1, $y], [$x - 1, $y - 1], [$x, $y - 1], [$x + 1, $y - 1]]);
                }
                if ($this->canPlaceShipVertically($x - 1, $y - 1, 3)) {
                    if ($this->checkTile($x, $y - 1)) array_push($possiblePlacements, [[$x, $y - 1], [$x - 1, $y - 1], [$x - 1, $y], [$x - 1, $y + 1]]);
                    if ($this->checkTile($x, $y + 1)) array_push($possiblePlacements, [[$x, $y + 1], [$x - 1, $y - 1], [$x - 1, $y], [$x - 1, $y + 1]]);
                }
                if ($this->canPlaceShipVertically($x + 1, $y - 1, 3)) {
                    if ($this->checkTile($x, $y - 1)) array_push($possiblePlacements, [[$x, $y - 1], [$x + 1, $y - 1], [$x + 1, $y], [$x + 1, $y + 1]]);
                    if ($this->checkTile($x, $y + 1)) array_push($possiblePlacements, [[$x, $y + 1], [$x + 1, $y - 1], [$x + 1, $y], [$x + 1, $y + 1]]);
                }
            }
        }

        for ($i = 0; $i < $number; $i++) {
            if (!empty($possiblePlacements)) {
                $placement = $possiblePlacements[array_rand($possiblePlacements)];
                $this->placeShip($placement, 4);
                $this->updatePossiblePlacements($possiblePlacements);
            } else throw new Exception("Błąd podczas generowania czteromasztowych statków");
        }
    }

    function placeQuintaShip(int $number)
    {
        $possiblePlacements = [];

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                if ($this->canPlaceShipHorizontally($x - 1, $y, 3) && $this->canPlaceShipVertically($x, $y - 1, 3)) {
                    array_push($possiblePlacements, [[$x - 1, $y], [$x, $y - 1], [$x, $y], [$x + 1, $y], [$x, $y + 1]]);
                }
                if ($this->canPlaceShipVertically($x - 1, $y - 1, 3) && $this->canPlaceShipHorizontally($x - 1, $y + 1, 3)) {
                    array_push($possiblePlacements, [[$x - 1, $y - 1], [$x - 1, $y], [$x - 1, $y + 1], [$x, $y + 1], [$x + 1, $y + 1]]);
                }
                if ($this->canPlaceShipVertically($x + 1, $y - 1, 3) && $this->canPlaceShipHorizontally($x - 1, $y - 1, 3)) {
                    array_push($possiblePlacements, [[$x - 1, $y - 1], [$x + 1, $y], [$x + 1, $y - 1], [$x, $y - 1], [$x + 1, $y + 1]]);
                }
                if ($this->canPlaceShipVertically($x - 1, $y - 1, 3) && $this->checkTile($x, $y - 1) && $this->checkTile($x, $y + 1)) {
                    array_push($possiblePlacements, [[$x - 1, $y - 1], [$x - 1, $y], [$x - 1, $y + 1], [$x, $y - 1], [$x, $y + 1]]);
                }
                if ($this->canPlaceShipVertically($x + 1, $y - 1, 3) && $this->checkTile($x, $y - 1) && $this->checkTile($x, $y + 1)) {
                    array_push($possiblePlacements, [[$x + 1, $y - 1], [$x + 1, $y], [$x + 1, $y + 1], [$x, $y - 1], [$x, $y + 1]]);
                }
                if ($this->canPlaceShipHorizontally($x - 1, $y - 1, 3) && $this->checkTile($x - 1, $y) && $this->checkTile($x + 1, $y)) {
                    array_push($possiblePlacements, [[$x - 1, $y - 1], [$x, $y - 1], [$x + 1, $y - 1], [$x - 1, $y], [$x + 1, $y]]);
                }
                if ($this->canPlaceShipHorizontally($x - 1, $y + 1, 3) && $this->checkTile($x - 1, $y) && $this->checkTile($x + 1, $y)) {
                    array_push($possiblePlacements, [[$x - 1, $y + 1], [$x, $y + 1], [$x + 1, $y + 1], [$x - 1, $y], [$x + 1, $y]]);
                }
            }
        }

        for ($i = 0; $i < $number; $i++) {
            if (!empty($possiblePlacements)) {
                $placement = $possiblePlacements[array_rand($possiblePlacements)];
                $this->placeShip($placement, 5);
                $this->updatePossiblePlacements($possiblePlacements);
            } else throw new Exception("Błąd podczas generowania pięciomasztowych statków");
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
            if ($this->tiles[$x][$y][0] === TileState::FREE) {
                $this->tiles[$x][$y][0] = TileState::EXCLUDED;
            }
        }
    }

    function saveBoardToFile($fileName, $boardRepresentation)
    {
        try {
            $file = fopen($fileName, "a");
            if ($file) {
                fwrite($file, $boardRepresentation . PHP_EOL);
                fclose($file);
            }
        } catch (Exception $e) {
            echo "Wystąpił błąd: " . $e->getMessage();
        }
    }

    function stringBoardRepresentation()
    {
        $encodedString = $this->width . ' ' . $this->height . ' ' . $this->pxlTileSize . ' ';
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->tiles[$x][$y][0] === TileState::OCCUPIED) {
                    $encodedString .= $this->tiles[$x][$y][1];
                } else $encodedString .= "0";
            }
        }
        return $encodedString;
    }

    function decodeBoardString($boardRepresentation)
    {
        for ($i = 0; $i < strlen($boardRepresentation); $i++) {
            $tileClass = $this->getShipClass($boardRepresentation[$i]);
            echo "<script>document.querySelectorAll('#board-container > div')[$i].classList.add('$tileClass')</script>";
        }
    }
}


function createFileNrForm()
{
    try {
        $lineCount = @count(file('unique-boards.txt'));
        echo '<div id="nrFormWrapper">';
        echo '<h2>Formularz wyboru planszy</h2>';
        echo '<p>Liczba linii w pliku unique-boards.txt: ' . $lineCount . '</p>';
        echo '<form action="board.php" method="get" id="nrForm">';
        echo '<label for="boardIndex">Wybierz indeks planszy (od 0 do ' . $lineCount - 1 . '):</label>';
        echo '<input type="number" id="boardIndex" name="boardIndex" min="' . "0" . '" max="' . $lineCount . '" required>' . "<br>";
        echo '<input type="submit" value="Wybierz">';
        echo '</form>';
        echo '</div>';
    } catch (Throwable $th) {
        echo 'Plik który jest bazą plansz jest pusty';
        echo '<button id="returnButton" onclick="window.location.href = \'index.php\'">Powrót do formularza</button>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['import']) && $_GET['import'] == "on") createFileNrForm();
else if (isset($_GET['boardIndex'])) {
    if (intval($_GET['boardIndex'] < 0)) {
        echo "Podano niepoprawny index planszy w pliku";
        echo '<button id="returnButton" onclick="window.location.href = \'index.php\'">Powrót do formularza</button>';
        return;
    }
    $lines = file('unique-boards.txt', FILE_IGNORE_NEW_LINES);
    $id = $_GET['boardIndex'];
    $parts = explode(" ", $lines[$id]);
    $game = new Board(intval($parts[0]), intval($parts[1]), intval($parts[2]));
    $game->create_board();
    $game->decodeBoardString($parts[3]);
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['width']) && isset($_GET['height']) && isset($_GET['pxlTileSize'])) {
    if (intval($_GET['width'] < 0) || intval($_GET['height'] < 0) || intval($_GET['pxlTileSize'] < 0)) {
        echo "Podano niepoprawne dane dotyczące rozmiaru planszy index planszy w pliku";
        echo '<button id="returnButton" onclick="window.location.href = \'index.php\'">Powrót do formularza</button>';
        return;
    }
    $game = new Board(intval($_GET['width']), intval($_GET['height']), intval($_GET['pxlTileSize']));
    $game->create_board();

    try {
        $game->placeQuintaShip(isset($_GET['ships5']) ? intval($_GET['ships5']) : 0);
        $game->placeQuadraShip(isset($_GET['ships4']) ? intval($_GET['ships4']) : 0);
        $game->placeTripleShip(isset($_GET['ships3']) ? intval($_GET['ships3']) : 0);
        $game->placeDoubleShip(isset($_GET['ships2']) ? intval($_GET['ships2']) : 0);
        $game->placeSingleShip(isset($_GET['ships1']) ? intval($_GET['ships1']) : 0);

        if (isset($_GET['saveMode']) && $_GET['saveMode'] == "auto") {
            $boardRepresentation = $game->stringBoardRepresentation();
            $game->saveBoardToFile('unique-boards.txt', $boardRepresentation);
        }
    } catch (Throwable $th) {
        echo "Wystąpił problem podczas generacji planszy wskutek braku miejsca na wszystkie statki (plansza jest za mała): " . $th->getMessage();
        echo "<script>document.querySelector('#board-container').remove()</script>";
        echo '<br><button id="retryButton" onclick="location.reload()">Spróbuj ponownie</button>';
        echo '<button id="returnButton" onclick="window.location.href = \'index.php\'">Powrót do formularza</button>';
        return;
    }
} else {
    echo "Podano niepoprawne dane w formularzu";
    return;
}
