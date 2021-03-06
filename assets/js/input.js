(function ($) {

    var bea_acf_taxonomy_terms_tpl, bea_acf_taxonomy_terms_ajax;
    jQuery(function () {
        bea_acf_taxonomy_terms_tpl = jQuery('#tmpl-bea-taxonomy-term').html();
    });

    if (typeof acf.add_action !== 'undefined') {

        /*
         *  ready append (ACF5)
         *
         *  These are 2 events which are fired during the page load
         *  ready = on page load similar to $(document).ready()
         *  append = on new DOM elements appended via repeater field
         *
         *  @type	event
         *  @date	20/07/13
         *
         *  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
         *  @return	n/a
         */

        acf.add_action('ready append', function ($el) {

            // search $el for fields of type 'FIELD_NAME'
            acf.get_fields({type: 'taxonomy_term'}, $el).each(function () {

                $el.find('select.bea_acf_taxonomy_term_taxonomies, select.bea_acf_taxonomy_term_taxonomies_terms')
                    .select2()
                    .on('change', function (e) {
                        "use strict";
                        var select = jQuery(e.target);
                        if (select.hasClass('bea_acf_taxonomy_term_taxonomies')) {
                            if (!_.isUndefined(bea_acf_taxonomy_terms_ajax)) {
                                bea_acf_taxonomy_terms_ajax.abort();
                            }
                            bea_acf_taxonomy_terms_ajax = jQuery.ajax({
                                url: ajaxurl,
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    action: 'bea_acf_taxonomy_term',
                                    _ajax_nonce: bea_acf_taxonomy_term.nonce,
                                    taxonomies: e.val
                                }
                            }).success(function (response) {
                                var terms_select = select.parent().find('select.bea_acf_taxonomy_term_taxonomies_terms'),
                                    content = _.template(bea_acf_taxonomy_terms_tpl, {
                                        terms: response.data,
                                        selected_terms: terms_select.data()
                                    });
                                terms_select.html(content).select2("val", terms_select.data());
                            });
                        }
                    });
            });
        });

    } else {


        /*
         *  acf/setup_fields (ACF4)
         *
         *  This event is triggered when ACF adds any new elements to the DOM.
         *
         *  @type	function
         *  @since	1.0.0
         *  @date	01/01/12
         *
         *  @param	event		e: an event object. This can be ignored
         *  @param	Element		postbox: An element which contains the new HTML
         *
         *  @return	n/a
         */

        $(document).on('acf/setup_fields', function (e, postbox) {

            $(postbox).find('.field[data-field_type="taxonomy_term"]').each(function () {

                jQuery('select.bea_acf_taxonomy_term_taxonomies, select.bea_acf_taxonomy_term_taxonomies_terms')
                    .select2()
                    .on('change', function (e) {
                        "use strict";
                        var select = jQuery(e.target);
                        if (select.hasClass('bea_acf_taxonomy_term_taxonomies')) {
                            if (!_.isUndefined(bea_acf_taxonomy_terms_ajax)) {
                                bea_acf_taxonomy_terms_ajax.abort();
                            }
                            bea_acf_taxonomy_terms_ajax = jQuery.ajax({
                                url: ajaxurl,
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    action: 'bea_acf_taxonomy_term',
                                    _ajax_nonce: bea_acf_taxonomy_term.nonce,
                                    taxonomies: e.val
                                }
                            }).success(function (response) {
                                var terms_select = select.parent().find('select.bea_acf_taxonomy_term_taxonomies_terms'),
                                    content = _.template(bea_acf_taxonomy_terms_tpl, {
                                        terms: response.data,
                                        selected_terms: terms_select.data()
                                    });
                                terms_select.html(content).select2("val", terms_select.data());
                            });
                        }
                    });
            });
        });
    }
})(jQuery);

function be_acf_taxonomy_term_selected(value, check) {
    "use strict";
    return be_acf_taxonomy_term_checked_selected_helper(value, check, 'selected');
};

function be_acf_taxonomy_term_checked_selected_helper(helper, current, type) {
    "use strict";
    return ( helper === current ) ? type + '="' + type + '"' : '';
};

// init sortable
jQuery(document).ready(function () {
    jQuery('.bea_acf_taxonomy_term_taxonomies').select2();
    jQuery("ul.select2-selection__rendered").sortable({
        containment: 'parent',
    });
});