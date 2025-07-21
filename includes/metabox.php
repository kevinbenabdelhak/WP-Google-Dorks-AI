<?php
if (!defined('ABSPATH')) exit;

add_action('add_meta_boxes', function() {
    $post_types = get_post_types(['public'=>true, 'show_ui'=>true], 'names');
    foreach($post_types as $post_type) {
        add_meta_box(
            'google_dorks_meta_box',
            'Générer des Google Dorks',
            'wp_gdai_metabox_content',
            $post_type, 'side'
        );
    }
});

function wp_gdai_metabox_content($post) {
    $nonce = wp_create_nonce('generate_dorks_nonce');
    ?>
    <button id="generate-dorks-button" class="button">Générer</button>
    <div id="dorks-output"></div>
    <script>
    document.getElementById('generate-dorks-button').addEventListener('click', function(e) {
        e.preventDefault();
        const postContent = <?php echo json_encode($post->post_content); ?>;
        const postTitle = <?php echo json_encode($post->post_title); ?>;
        const formData = new FormData();
        formData.append('action', 'generate_google_dorks');
        formData.append('content', postContent);
        formData.append('title', postTitle);
        formData.append('_wpnonce', '<?php echo $nonce; ?>');
        document.getElementById('dorks-output').innerHTML = 'Génération en cours...';
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        }).then(response => {
            if(!response.ok) return response.text().then(text => { throw new Error(text || response.statusText); });
            return response.json();
        }).then(data => {
            if (data.success) {
                let outputHtml = '<div style="max-height:350px;overflow:auto;">';
                let dorksByCategories = data.data.Google_Dorks_By_Category || {};
                if (Object.keys(dorksByCategories).length === 0) {
                    outputHtml += "<em>Aucune donnée reçue.</em>";
                } else {
                    for (const cat in dorksByCategories) {
                        outputHtml += '<h4 style="margin-bottom:3px;margin-top:12px;">' + cat + '</h4><ul style="margin-top:0;">';
                        dorksByCategories[cat].forEach(function(dork) {
                            if (dork.Requête && dork.Requête.trim() !== '') {
                                let query = encodeURIComponent(dork.Requête);
                                let url = 'https://www.google.com/search?q=' + query;
                                outputHtml += '<li style="margin-left: 0.7em;"><a href="' + url + '" target="_blank" rel="noopener noreferrer">' + dork.Requête + '</a></li>';
                            }
                        });
                        outputHtml += '</ul>';
                    }
                }
                outputHtml += '</div>';
                document.getElementById('dorks-output').innerHTML = outputHtml;
            } else {
                document.getElementById('dorks-output').innerHTML = 'Erreur : ' + (data.data || data.message || 'Une erreur inconnue est survenue.');
            }
        }).catch(error => {
            document.getElementById('dorks-output').innerHTML = 'Erreur lors de la communication : ' + error.message;
            console.error('Fetch error:', error);
        });
    });
    </script>
    <?php
}