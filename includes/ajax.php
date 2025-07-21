<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_generate_google_dorks', 'wp_gdai_generate_google_dorks_ajax');
add_action('wp_ajax_get_google_dorks_json', 'wp_gdai_generate_google_dorks_ajax');

function wp_gdai_generate_google_dorks_ajax() {
    $is_bulk = ($_POST['action'] == 'get_google_dorks_json');
    if (!$is_bulk && (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'generate_dorks_nonce')))
        wp_send_json_error('Nonce incorrect.');
    $content = isset($_POST['content']) ? sanitize_text_field($_POST['content']) : '';
    $title   = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    if ($is_bulk && isset($_POST['post_id'])) {
        $p = get_post((int)$_POST['post_id']);
        if (!$p) wp_send_json_error('Article inconnu');
        $title = $p->post_title;
        $content = $p->post_content;
    }
    $api_key = get_option('wp_google_dorks_ai_api_key');
    $model   = get_option('wp_google_dorks_ai_model', 'gpt-4o-mini');
    $categories = get_option('wp_google_dorks_ai_categories', wp_gdai_default_categories());
    if (!$api_key) wp_send_json_error("API key non configurée.");
    $cat_blocks = [];
    $instructions = [];
    foreach($categories as $cat) {
        $examples = array_filter(array_map('trim', explode("\n", $cat['examples'])));
        $cat_max = isset($cat['cat_max']) ? intval($cat['cat_max']) : 30;
        $cat_blocks[] = '"'. $cat['cat_title'] . '": ['. implode(',', array_map(function($eg){
            return '{ "Requête": "'.str_replace('"','\\"',$eg).'" }';
        }, $examples)) . ']';
        $instructions[] = '- Pour la catégorie "' . $cat['cat_title'] . '", génère ' . $cat_max . ' Google Dorks pertinents.';
    }
    $json_schema = '{ "Google_Dorks_By_Category": {'.implode(',',$cat_blocks).' } }';
    $prompt = "Je veux une structure JSON appelée \"Google_Dorks_By_Category\" qui regroupe des dorks Google contextuels à cet article, classés par catégories selon ce modèle :\n";
    $prompt .= $json_schema . "\n";
    $prompt .= "- N'inclus une catégorie que si elle peut contenir au moins un dork pertinent pour le texte fourni.\n";
    foreach($instructions as $inst) $prompt .= $inst . "\n";
    $prompt .= "- Pour chaque catégorie, prends comme exemple sa syntaxe/structure, mais utilise les mots-clés/faits réellement présents (pas de générique !).\n";
    $prompt .= "- Ne retourne jamais 'VOTRE_MOT_CLE', 'MOT_CLE', 'KW', ou des variables génériques/vides.\n";
    foreach($categories as $cat) {
        $cat_title = $cat['cat_title'];
        $examples  = array_filter(array_map('trim', explode("\n", $cat['examples'])));
        $operators = [];
        foreach ($examples as $ex) {
            preg_match_all('/\b(inurl|intitle|intext|filetype|site|ext):[^\s]*/i', $ex, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $op) $operators[] = strtolower($op);
            }
        }
        $operators = array_unique($operators);
        if (count($operators) > 0) {
            $prompt .= "- Pour la catégorie \"$cat_title\", tu dois utiliser STRICTEMENT ces opérateurs et PAS D'AUTRES : " . implode(", ", $operators) . ".\n";
        } else {
            $prompt .= "- Pour la catégorie \"$cat_title\", ne mets AUCUN opérateur Google spécial non présent dans les exemples.\n";
        }
    }
    $prompt .= "- Si un opérateur n'est pas dans les exemples d'une catégorie, n'utilise jamais cet opérateur dans cette catégorie, même si tu penses qu'il serait pertinent.\n";
    $prompt .= "- Pour chaque catégorie, prends comme exemple sa syntaxe/structure, mais utilise les mots-clés/faits réellement présents (pas de générique !).\n";
    $prompt .= "- Ne retourne jamais 'VOTRE_MOT_CLE', 'MOT_CLE', 'KW', ou des variables génériques/vides.\n";
    $prompt .= "- Utilise EXCLUSIVEMENT les mots-clés du titre et du contenu donnés.\n";
    $prompt .= "\nTitre de l'article :\n\"$title\"\nContenu de l'article :\n\"$content\"\n";
    $prompt .= "Structure la réponse en JSON strict selon le modèle ci-dessus (aucun texte en dehors du JSON).";
    $openai_body = [
        'model' => $model,
        'messages' => [
            [ 'role' => 'user', 'content' => $prompt ]
        ],
        'temperature' => 1,
        'max_tokens' => 4096,
        'response_format' => ['type' => 'json_object']
    ];
    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        [
            'body' => json_encode($openai_body),
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 90
        ]
    );
    if (is_wp_error($response)) wp_send_json_error($response->get_error_message());
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['choices'][0]['message']['content'])) {
        $dorksJson = json_decode($body['choices'][0]['message']['content'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($dorksJson['Google_Dorks_By_Category'])) {
            if (!$is_bulk) wp_send_json_success($dorksJson);
            else wp_send_json_success(['title'=>$title, 'dorks'=>$dorksJson['Google_Dorks_By_Category']]);
        }
        wp_send_json_error('Réponse IA non valide.');
    } else {
        wp_send_json_error('Réponse API inattendue.');
    }
}