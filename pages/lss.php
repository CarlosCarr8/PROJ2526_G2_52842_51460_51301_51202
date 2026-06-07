<?php
$resultado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $comando = $_POST["comando"] ?? "";

    // Aqui mais tarde vais chamar o Python
    // Por enquanto apenas mostra o texto recebido

    $resultado = "Comando recebido:\n" . $comando;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>PCU - LSS</title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;

    background:linear-gradient(
        135deg,
        #eaf4fc,
        #d6ecff
    );

    min-height:100vh;
}

.container{
    width:900px;
    margin:50px auto;

    background:white;

    border-radius:20px;

    padding:30px;

    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h1{
    text-align:center;
    color:#0b1f3a;
}

textarea{
    width:100%;
    height:300px;

    background:#0d1117;
    color:#58a6ff;

    border:none;
    border-radius:10px;

    padding:15px;

    font-family:Consolas, monospace;
    font-size:16px;

    resize:none;

    box-sizing:border-box;
}

button{
    margin-top:20px;

    width:100%;
    height:50px;

    border:none;
    border-radius:25px;

    background:linear-gradient(
        90deg,
        #2d98da,
        #5dade2
    );

    color:white;
    font-size:18px;
    font-weight:bold;

    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.resultado{

    margin-top:25px;

    background:#f5f8fb;

    border-radius:10px;

    padding:15px;

    white-space:pre-wrap;
}

.voltar{

    display:inline-block;

    margin-bottom:20px;

    text-decoration:none;

    color:#2d98da;

    font-weight:bold;
}

</style>

</head>
<body>

<div class="container">

    <a href="dashboard.php" class="voltar">
        ← Voltar
    </a>

    <h1>PCU - Linguagem LSS</h1>

    <form method="POST">

        <textarea
            name="comando"
            placeholder="Escreva o comando LSS..."
        ></textarea>

        <button type="submit">
            Executar
        </button>

    </form>

    <?php if(!empty($resultado)): ?>

        <div class="resultado">
            <?= htmlspecialchars($resultado) ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>