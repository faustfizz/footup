<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./favicon.svg" />
    <title><?= frameworkName() ?> - version: <?= frameworkVersion() ?></title>

    <?= css("style.css") ?>
</head>
<body>
    <div class="text-center justify-content-center my-7" align="center">
        <h1><?= frameworkName() ?> - version: <?= frameworkVersion() ?></h1>
        <h2>Bienvenue à notre illustration de l'utilisation d'une architecture MVC</h2>
        <form class="form-inline" enctype="multipart/form-data" method="POST" style="margin:auto;width:100%;max-width:500px">
            <div class="form-group">
                <label for="">Fichiers</label>
                <input type="file" class="form-control-file" name="image" id="" >
            </div>
            <br>
            <button type="submit">Envoyer</button>
        </form>
    </div>
    
    <?= js("script.js") ?>
</body>
</html>

