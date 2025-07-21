<?php
if (!defined('ABSPATH')) exit;

function wp_gdai_default_categories() {
    return [
        [
            'cat_title' => 'Articles invités',
            'cat_max'   => 30,
            'examples'  => "inurl:/guest-post/ \"write for us\"\ninurl:/contribute/ \"submit article\"\ninurl:/write-for-us/ SEO\ninurl:/become-a-contributor/ VOTRE_MOT_CLE\nintitle:\"write for us\" blog\nintitle:\"contribute to this site\" VOTRE_MOT_CLE\nintext:\"guest author\" VOTRE_MOT_CLE\nintext:\"submit guest post\" VOTRE_MOT_CLE"
        ],
        [
            'cat_title' => 'Annuaires',
            'cat_max'   => 30,
            'examples'  => "intitle:\"ajouter un site\" annuaire\ninurl:/submit-site VOTRE_MOT_CLE\ninurl:/addurl VOTRE_MOT_CLE\ninurl:/soumettre-site VOTRE_MOT_CLE\ninurl:/ajouter-un-lien VOTRE_MOT_CLE\nintitle:\"inscrire votre site\" VOTRE_MOT_CLE"
        ],
        [
            'cat_title' => 'Forums',
            'cat_max'   => 30,
            'examples'  => "inurl:/forum/ \"s'inscrire\"\ninurl:/forum/ \"poster un message\"\ninurl:/forums/ \"nouveau sujet\"\nintitle:Forum VOTRE_MOT_CLE\ninurl:/viewtopic.php?t= VOTRE_MOT_CLE\ninurl:index.php?board= VOTRE_MOT_CLE"
        ],
        [
            'cat_title' => 'Empreintes CMS',
            'cat_max'   => 30,
            'examples'  => "intitle:\"powered by WordPress\" VOTRE_MOT_CLE\nintitle:\"powered by Drupal\" VOTRE_MOT_CLE\nintitle:\"powered by Joomla\" VOTRE_MOT_CLE\ninurl:/wp-content/plugins/ VOTRE_MOT_CLE\nsite:http://competitor.com -www."
        ],
        [
            'cat_title' => 'Documents avec liens',
            'cat_max'   => 30,
            'examples'  => "filetype:pdf intext:\"http\" VOTRE_MOT_CLE\nfiletype:doc intext:\"http\" VOTRE_MOT_CLE\nfiletype:xls intext:\"http\" VOTRE_MOT_CLE"
        ],
        [
            'cat_title' => 'Sites de presse',
            'cat_max'   => 30,
            'examples'  => "site:http://lefigaro.fr intext:\"VOTRE_MARQUE\"\nsite:http://huffpost.com inurl:/contributor\nsite:http://medium.com VOTRE_MOT_CLE"
        ],
        [
            'cat_title' => 'Footprints SEO',
            'cat_max'   => 30,
            'examples'  => "inurl:/tag/ VOTRE_MOT_CLE\ninurl:/category/ VOTRE_MOT_CLE\nintitle:archives VOTRE_MOT_CLE\ninurl:?s= VOTRE_MOT_CLE"
        ]
    ];
}