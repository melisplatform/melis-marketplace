<?php

/**
 * Capacités d'outils — droits avancés du back-office React (déclaration plate, par module).
 *
 * Même convention que melis-core/config/react.capabilities.php et
 * melis-commerce/config/react.capabilities.php : chaque module déclare ICI les capacités de
 * SES outils, par `melisKey`. Fichier VOLONTAIREMENT SÉPARÉ (pas dans module.config.php),
 * indépendant de `app.interface.php` et du rendu legacy. Mergé dans
 * MelisMarketPlace\Module::getConfig() via la clé `melisReactToolCapabilities`, lu par
 * MelisReactApi\Service\Capabilities et affiché comme sous-cases dans l'onglet « Rights »
 * du formulaire Utilisateur (visible quand l'outil parent est autorisé).
 *
 * Sémantique : ces capacités gouvernent les COMPOSANTS INTERNES d'un outil déjà autorisé.
 * Default-allow — purement déclaratif : pilote l'affichage des cases à cocher (aucune
 * application côté contrôleur pour l'instant). Les libellés d'actions (list/create/edit/…)
 * sont traduits côté React (dictionnaire melis-core `caps.*`) ; on réutilise donc CE
 * vocabulaire standard plutôt que des clés maison (qui s'afficheraient en brut).
 */

return [
    'melisReactToolCapabilities' => [
        // Market Place : un seul outil (parcourir le catalogue Packagist + gérer les modules).
        // Pas d'onglets React (la fiche = un panneau « Gérer ») → liste plate d'actions.
        // Vocabulaire PROPRE À l'outil (rien à créer/éditer/supprimer ici) — chaque action est
        // GARDÉE côté React dans MarketPlacePage.tsx via `useCaps(...).can(...)` :
        //   list     → parcourir / lister les packages (grille du catalogue)
        //   download → récupérer un package via composer : installe (bouton « Télécharger »)
        //              ET met à jour (bouton « Mettre à jour ») — même geste, un état diff. du paquet
        //   remove   → supprimer / désinstaller un module (bouton « Supprimer »)
        // NB : pas de capacité d'export — l'export des tables n'est pas une action autonome, il
        // est joué automatiquement en amont d'une suppression (garde `remove`).
        'melis_market_place_tool_display' => [
            'actions' => ['list', 'download', 'remove'],
        ],
    ],
];
