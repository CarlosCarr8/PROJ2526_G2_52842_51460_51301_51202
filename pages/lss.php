<form method="POST">
    <textarea name="comando" rows="10" cols="60"></textarea>
    <br>
    <button type="submit">Executar</button>
</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $comando = $_POST["comando"];

    $comando = escapeshellarg($comando);

    $resultado = shell_exec(
        "python ../lss/executar.py $comando"
    );

    echo "<pre>";
    echo $resultado;
    echo "</pre>";
}
?>