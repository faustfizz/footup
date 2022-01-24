<?php

return [
	// Response
	'invalidBodyType' 	=> 'le corps de la reponse doit être une chaine de caractère ou un object implementant la méthode __toString(). le type trouvé est {0}',

	// RedirectResponse
	'invalidRoute' 	=> '{0} n\'est pas une route valide.',
	'error500' 		=> 'Erreur de configuration, impossible de démarrer FOOTUP Framework ! Raison : {0}',

	// Page Not Found
	'pageNotFound'       => 'Page non trouvée',
	'pageNotFoundMessage'  => "Erreur 404 : L'Url <code style='color: blue;background-color: #0aabb345;padding: 2px 6px;border: 1px solid #eff2f4;border-radius: 6px;'>{0}</code> ne correspond à aucune des routes définies",
	'emptyController'    => 'Pas de contrôleur spécifié.',
	'controllerNotFound' => 'Le contrôleur est introuvables : {0}',
	'controllerBailed'	 => 'Le contrôleur doit être de type string(fonction, class) ou objet. Type trouvé : {0}',
	'methodNotFound'     => 'La méthode `{0}` du contrôleur {1} est introuvable',
	'routeMethodNotFound'     => 'La méthode de routage {0} est introuvable',

	'disallowedAction' => 'Votre demande n\'est pas autorisée.',

	// Uploaded file moving
	'alreadyMoved' => 'Le fichier uploadé a déjà été déplacé.',
	'invalidFile'  => 'Le fichier original n\'est pas un fichier valide.',
	'moveFailed'   => 'Impossible de déplacer le fichier {0} vers {1} ({2})',
];
