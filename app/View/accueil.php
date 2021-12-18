<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footup Framework</title>

    <?= css("style.css") ?>
</head>
<body>
    <div class="text-center justify-content-center my-7">
        <h1>Bienvenue à notre illustration de l'utilisation d'une architecture MVC</h1>
        <form class="form-inline" enctype="multipart/form-data" method="POST">
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

