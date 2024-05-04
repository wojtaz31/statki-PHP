<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formStyles.css">
    <title>Formularz</title>
</head>

<body>
    <form action="index.php" method="get">
        <div id="formTileWrapper">
            <div>
                <fieldset>
                    <legend>Rozmiar planszy</legend>
                    <label for="width">Szerokość:</label>
                    <input type="number" id="width" name="width" value="10"><br>
                    <label for="height">Wysokość:</label>
                    <input type="number" id="height" name="height" value="10"><br>
                    <label for="pxlTileSize">Rozmiar pola w px:</label>
                    <input type="number" id="pxlTileSize" name="pxlTileSize" value="80"><br>
                    <label>Tryb zapisu planszy</label>
                    <select id="saveMode" name="saveMode">
                        <option value="auto">Zapis automatyczny</option>
                        <option value="none">Brak zapisu</option>
                    </select>
                    <hr>
                    <label for="importFile">Importuj ułożenie statków z pliku:</label>
                    <input type="file" id="importFile" name="importFile">

                    <br><br>
                    <input type="button" id="randomBoard" value="Losuj planszę ">

                </fieldset>



            </div>

            <div>
                <fieldset>
                    <legend>Ilość statków</legend>
                    <select id="saveMode" name="saveMode">
                        <option value="classic">Zasady klasyczne</option>
                        <option value="custom">Niestandardowe zasady</option>
                    </select><br>
                    <label for="ships1">1-masztowych:</label>
                    <input type="number" id="ships1" name="ships1" value="4"><br>
                    <label for="ships2">2-masztowych:</label>
                    <input type="number" id="ships2" name="ships2" value="3"><br>
                    <label for="ships3">3-masztowych:</label>
                    <input type="number" id="ships3" name="ships3" value="2"><br>
                    <label for="ships4">4-masztowych:</label>
                    <input type="number" id="ships4" name="ships4" value="1"><br>
                    <label for="ships5">5-masztowych:</label>
                    <input type="number" id="ships5" name="ships5" value="0"><br>
                </fieldset>


            </div>
        </div>


        <input type="submit" value="GENERUJ">
    </form>
</body>

</html>