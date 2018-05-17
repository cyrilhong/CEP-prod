jQuery(document).ready(function ($) {
    if ($('#allow_all_product_cat').is(':checked')) {
        $('.productcategorydiv li').hide();
    }
    $('#allow_all_product_cat').on('change', function () {
        if ($(this).prop('checked')) {
            $('.productcategorydiv li').hide();
            $('.productcategorydiv li').find('input[type=checkbox]').removeAttr('checked');
        } else {
            $('.productcategorydiv li').show();
        }
    });
});