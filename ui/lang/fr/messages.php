<?php

declare(strict_types=1);

return [
    'welcome' => 'Bienvenue :name',
    'goodbye' => 'Au revoir {name}, à bientôt!',
    'greeting' => 'Bonjour :name, vous avez {count} nouveaux messages',
    
    'auth' => [
        'login_success' => 'Connecté avec succès en tant que :name',
        'logout_success' => 'Vous avez été déconnecté',
        'unauthorized' => 'Vous ne disposez pas des autorisations nécessaires pour accéder à {resource}',
    ],
    
    'validation' => [
        'required' => 'Le champ :field est obligatoire',
        'email' => 'Le champ {field} doit être une adresse email valide',
        'min' => 'Le champ :field doit contenir au moins :min caractères',
        'max' => 'Le champ {field} ne peut pas dépasser {max} caractères',
    ],
    
    'user' => [
        'created' => 'Utilisateur :name créé avec succès',
        'updated' => 'Profil de :name mis à jour à {time}',
        'deleted' => 'Utilisateur {name} supprimé',
        'last_login' => 'Dernière connexion: :date',
    ],
    
    'email' => [
        'subject' => ':action - {appname}',
        'greeting' => 'Bonjour :name,',
        'message' => '{message}',
        'closing' => 'Cordialement,\n{appname}',
    ],
];
