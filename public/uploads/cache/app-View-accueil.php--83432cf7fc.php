<?php
// source: /opt/blamp-7/www/footup/app/View/accueil.php

use Latte\Runtime as LR;

final class Template83432cf7fc extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
?>
<div class="text-center justify-content-center my-7">
    <h1>Bienvenue à notre illustration de l'utilisation d'une architecture MVC</h1>
    <form class="form-inline" enctype="multipart/form-data" method="POST">
        <div class="form-group">
            <label for="">Fichiers</label>
            <input type="file" class="form-control-file" name="image" id="">
        </div>
        <br>
        <button type="submit">Envoyer</button>
    </form>
</div><?php
		return get_defined_vars();
	}

}
