/**
 * EdupreneurPro Admin Helpers
 */
(function($) {
    'use strict';

    window.eduEditAsset = function(data) {
        $('#asset_id').val(data.id);
        $('#asset_title').val(data.title);
        $('#asset_type').val(data.asset_type);
        $('#asset_content').val(data.content);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.eduEditCategory = function(data) {
        $('#cat_id').val(data.id);
        $('#cat_name').val(data.name);
        $('#cat_desc').val(data.description);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.eduEditKB = function(data) {
        $('#kb_id').val(data.id);
        $('#kb_title').val(data.title);
        $('#kb_category').val(data.category);
        $('#kb_content').val(data.content);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.eduEditProduct = function(data) {
        $('#prod_id').val(data.id);
        $('#prod_title').val(data.title);
        $('#prod_price').val(data.price);
        $('#prod_url').val(data.file_url);
        $('#prod_limit').val(data.download_limit);
        $('#prod_expiry').val(data.expiry_days);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

})(jQuery);
