<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_options_page('WP Google Dorks AI', 'WP Google Dorks AI', 'manage_options', 'wp-google-dorks-ai', 'wp_gdai_options_page');
});

function wp_gdai_options_page() {
    if(isset($_POST['gdai_reset']) && check_admin_referer('wp_gdai_options')) {
        $categories = wp_gdai_default_categories();
        update_option('wp_google_dorks_ai_categories', $categories);
        echo '<div class="updated"><p>Catégories et exemples réinitialisés par défaut.</p></div>';
    }
    elseif(isset($_POST['gdai_save']) && check_admin_referer('wp_gdai_options')) {
        update_option('wp_google_dorks_ai_api_key', sanitize_text_field($_POST['api_key']));
        update_option('wp_google_dorks_ai_model', sanitize_text_field($_POST['openai_model']));
        $categories = [];
        if(isset($_POST['cat_title']) && is_array($_POST['cat_title'])) {
            foreach ($_POST['cat_title'] as $i=>$title) {
                $title = trim(wp_unslash($title));
                $examples = isset($_POST['examples'][$i]) ? trim(wp_unslash($_POST['examples'][$i])) : '';
                $cat_max = (isset($_POST['cat_max'][$i]) && is_numeric($_POST['cat_max'][$i]) && intval($_POST['cat_max'][$i])>0) ? intval($_POST['cat_max'][$i]) : 1;
                if($title !== '') $categories[] = [
                    'cat_title' => $title,
                    'cat_max'   => $cat_max,
                    'examples'  => $examples
                ];
            }
        }
        update_option('wp_google_dorks_ai_categories', $categories);
        echo '<div class="updated"><p>Enregistré.</p></div>';
    }
    $api_key    = get_option('wp_google_dorks_ai_api_key', '');
    $model      = get_option('wp_google_dorks_ai_model', 'gpt-4o-mini');
    $categories = get_option('wp_google_dorks_ai_categories', wp_gdai_default_categories());
    ?>
    <div class="wrap" id="wp-gdai-wrap">
        <h1>WP Google Dorks AI</h1>
        <form method="post">
            <?php wp_nonce_field('wp_gdai_options'); ?>
            <label for="api_key">Clé API OpenAI :</label>
            <input type="text" name="api_key" value="<?php echo esc_attr($api_key); ?>" required style="min-width:300px;" />
            <br><br>
            <label for="openai_model">Modèle OpenAI :</label>
            <select name="openai_model" id="openai_model">
                <option value="gpt-5" <?php selected($model, 'gpt-5'); ?>>gpt-5</option>
                <option value="gpt-4.1-mini" <?php selected($model, 'gpt-4.1-mini'); ?>>gpt-4.1-mini</option>
                <option value="gpt-4.1-nano" <?php selected($model, 'gpt-4.1-nano'); ?>>gpt-4.1-nano</option>
                <option value="gpt-4.1" <?php selected($model, 'gpt-4.1'); ?>>gpt-4.1</option>
                <option value="gpt-4o-mini" <?php selected($model, 'gpt-4o-mini'); ?>>gpt-4o-mini</option>
                <option value="gpt-4o" <?php selected($model, 'gpt-4o'); ?>>gpt-4o</option>
                <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>gpt-3.5-turbo</option>
            </select>
            <br><br>
            <h2>Catégories & Exemples de Google Dorks</h2>
            <p style="margin-top:0;">Modifiez les catégories, leurs exemples, ou le nombre de Google Dorks générés par catégorie.</p>
            <div id="gdai-cats-list">
                <?php foreach($categories as $i=>$cat): ?>
                <div class="gdai-cat-item" style="border:1px solid #ddd;margin-bottom:15px;padding:10px 12px;position:relative;">
                    <label>
                        <b>Titre de la catégorie : </b>
                        <input type="text" name="cat_title[]" value="<?php echo esc_attr($cat['cat_title']); ?>" required style="min-width:220px;">
                    </label>
                    <br>
                    <label>
                        <b>Nombre max de Google Dorks à générer :</b>
                        <input type="number" name="cat_max[]" value="<?php echo isset($cat['cat_max']) ? intval($cat['cat_max']) : 30; ?>" min="1" max="100" step="1" style="width:60px;" required>
                    </label>
                    <br>
                    <label>
                        <b>Exemples (<i>1 par ligne</i>) :</b><br>
                        <textarea rows="4" name="examples[]" style="width:100%;min-width:300px;" required><?php echo esc_textarea($cat['examples']); ?></textarea>
                    </label>
                    <button type="button" class="button gdai-remove-cat" style="position:absolute;top:8px;right:8px;">Supprimer</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="gdai-add-cat" class="button">Ajouter une catégorie</button>
            <p>
                <input type="submit" name="gdai_save" value="Enregistrer" class="button button-primary" />
                <input type="submit" name="gdai_reset" value="Réinitialiser par défaut" class="button" style="margin-left:18px;color:#b00;">
            </p>
            <span>Merci à <a target="_blank"href="https://x.com/freudix_">Freudix</a> pour la liste par défaut ;)</span>
        </form>
    </div>
    <script>
    (function(){
        var tplCat = function(){
            return `<div class="gdai-cat-item" style="border:1px solid #ddd;margin-bottom:15px;padding:10px 12px;position:relative;">
                <label>
                    <b>Titre de la catégorie : </b>
                    <input type="text" name="cat_title[]" value="" required style="min-width:220px;">
                </label>
                <br>
                <label>
                    <b>Nombre max de Google Dorks à générer :</b>
                    <input type="number" name="cat_max[]" value="30" min="1" max="100" step="1" style="width:60px;" required>
                </label>
                <br>
                <label>
                    <b>Exemples (<i>1 par ligne</i>):</b><br>
                    <textarea rows="4" name="examples[]" style="width:100%;min-width:300px;" required></textarea>
                </label>
                <button type="button" class="button gdai-remove-cat" style="position:absolute;top:8px;right:8px;">Supprimer</button>
            </div>`;
        };
        document.getElementById('gdai-add-cat').addEventListener('click', function(){
            var wrap = document.getElementById('gdai-cats-list');
            wrap.insertAdjacentHTML('beforeend', tplCat());
        });
        document.getElementById('gdai-cats-list').addEventListener('click', function(e){
            if(e.target.classList.contains('gdai-remove-cat')) {
                if(document.querySelectorAll('.gdai-cat-item').length > 1) {
                    e.target.closest('.gdai-cat-item').remove();
                } else {
                    alert('Il faut au moins une catégorie.');
                }
            }
        });
    })();
    </script>
    <style>
    #wp-gdai-wrap textarea {font-family:monospace;}
    </style>
    <?php
}