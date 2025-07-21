<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', function(){
    foreach (get_post_types(['public'=>true],'names') as $pt) {
        add_filter("bulk_actions-edit-$pt", function($actions) {
            $actions['export_google_dorks_pdf_js'] = __('Exporter les Google dorks','');
            return $actions;
        });
    }
});

add_action('admin_footer', function(){
    global $typenow;
    if (!$typenow || !post_type_exists($typenow)) return;

    // Enqueue le script jsPDF en local
    wp_enqueue_script('jspdf', plugin_dir_url(__FILE__) . '../js/jspdf.umd.min.js', array(), '2.5.1', true);
    ?>
    <script>
    jQuery(document).ready(function($){
        if ($('#export-gd-pdf').length == 0)
            $('.tablenav.top .bulkactions').append('<button type="button" class="button" id="export-gd-pdf" style="margin-left:8px;">Exporter les Dorks en PDF</button>');
        var loadingBox = $('<div id="gd-loading" style="display:none; position:fixed; top:35%; left:50%; transform:translate(-50%,0); background:#fff;border:1px solid #222;z-index:99999;max-width:320px;padding:18px;box-shadow:0 2px 18px rgba(0,0,0,0.17);font-size:16px;"></div>').appendTo('body');

        $('#doaction, #doaction2').on('click', function(e){
            var sel = $(this).attr('id')=='doaction' ? $('select[name=action]') : $('select[name=action2]');
            if(sel.val()=='export_google_dorks_pdf_js') {
                e.preventDefault(); $('#export-gd-pdf').trigger('click'); sel.val('');
            }
        });

        $('#export-gd-pdf').on('click', function(){
            var checked = $('tbody th.check-column input[type="checkbox"]:checked').map(function(){return $(this).val();}).get();
            if (!checked.length) { alert('Veuillez sélectionner des éléments.'); return false; }
            loadingBox.show().text('Récupération et génération PDF en cours...');
            var dorksData = [], done = 0;
            checked.forEach(function(pid, idx){
                $.post(ajaxurl, { action:'get_google_dorks_json', post_id:pid }, function(response){
                    done++;
                    if (response.success) dorksData.push(response.data);
                    if (done === checked.length) triggerDownloadPDF(dorksData);
                });
            });

            function triggerDownloadPDF(allPosts) {
                loadingBox.html('Création du PDF...');
                const { jsPDF } = window.jspdf;
                var doc = new jsPDF({unit:'mm', format:'a4'});
                var top = 15;

                allPosts.forEach(function(post, idx){
                    if(idx > 0) doc.addPage();
                    doc.setFontSize(13);
                    doc.setFont(undefined, 'normal');
                    doc.text('Google Dorks : '+post.title, 10, top);
                    doc.setFontSize(10);
                    var y = top+8;
                    for(var cat in post.dorks) {
                        doc.setFont(undefined,"bold"); doc.setFontSize(11);
                        doc.text(cat, 10, y); y += 6;
                        doc.setFont(undefined,"normal"); doc.setFontSize(9);
                        post.dorks[cat].forEach(function(entry){
                            if(entry.Requête) {
                                if (y > 280) { doc.addPage(); y = 15; }
                                doc.text('• '+entry.Requête, 13, y); 
                                y += 5;
                            }
                        });
                        y += 3;
                    }
                });

                var totalPages = doc.getNumberOfPages && doc.getNumberOfPages();
                if (totalPages && totalPages > 1 && doc.getPage) {
                    var content = doc.internal.getNumberOfPages ? doc.internal.pages[totalPages] : undefined;
                    if (content && content.length === 0) doc.deletePage(totalPages);
                }
                doc.save('google_dorks_export.pdf');
                loadingBox.hide();
            }
        });
    });
    </script>
    <?php
});