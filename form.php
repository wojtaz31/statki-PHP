<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formStyles.css">
    <title>Formularz</title>
</head>

<body>
    <form action="index.php" method="get" target="_blank">
        <div id="formTileWrapper">
            <div>
                <fieldset>
                    <legend>Rozmiar planszy</legend>
                    <label for="width">Szerokość:</label>
                    <input type="number" id="width" name="width" value="10" min="0"><br>
                    <label for="height">Wysokość:</label>
                    <input type="number" id="height" name="height" value="10" min="0"><br>
                    <label for="pxlTileSize">Rozmiar pola w px:</label>
                    <input type="number" id="pxlTileSize" name="pxlTileSize" value="70" min="0"><br>
                    <label>Tryb zapisu planszy</label>
                    <select id="saveMode" name="saveMode">
                        <option value="auto">Zapis automatyczny</option>
                        <option value="none">Brak zapisu</option>
                    </select>
                    <hr>
                    <label for="importFile">Plansza i ułożenie statków z pliku:</label>
                    <input type="file" id="importFile" name="importFile">

                </fieldset>
            </div>

            <div>
                <fieldset>
                    <legend>Ilość statków</legend>
                    <select id="rulesMode" name="rulesMode">
                        <option value="classic" selected>Zasady klasyczne</option>
                        <option value="custom">Niestandardowe zasady</option>
                    </select><br>
                    <label for="ships1">1-masztowych:</label>
                    <input type="number" id="ships1" name="ships1" value="4" min="0"><br>
                    <label for="ships2">2-masztowych:</label>
                    <input type="number" id="ships2" name="ships2" value="3" min="0"><br>
                    <label for="ships3">3-masztowych:</label>
                    <input type="number" id="ships3" name="ships3" value="2" min="0"><br>
                    <label for="ships4">4-masztowych:</label>
                    <input type="number" id="ships4" name="ships4" value="1" min="0"><br>
                    <label for="ships5">5-masztowych:</label>
                    <input type="number" id="ships5" name="ships5" value="0" min="0"><br>
                </fieldset>

            </div>
        </div>

        <input type="submit" value="GENERUJ">
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rulesModeSelect = document.getElementById('rulesMode')
            const shipsInputs = [
                document.getElementById('ships1'),
                document.getElementById('ships2'),
                document.getElementById('ships3'),
                document.getElementById('ships4'),
                document.getElementById('ships5')
            ];

            function setShipsValues(disabled) {
                const defaultValues = [4, 3, 2, 1, 0]
                shipsInputs.forEach((input, index) => {
                    input.value = defaultValues[index]
                    input.disabled = disabled
                })
            }

            setShipsValues(true);

            rulesModeSelect.addEventListener('change', function() {
                if (this.value === 'classic') setShipsValues(true)
                else setShipsValues(false)
            })
        })
        let form = document.querySelector('form')
        form.addEventListener('submit', (e) => {
            const disabledInputs = form.querySelectorAll('input[disabled]')
            disabledInputs.forEach(input => input.disabled = false)
        })
    </script>
</body>

</html>