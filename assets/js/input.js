var bea_acf_taxonomy_terms_tpl, bea_acf_taxonomy_terms_ajax;
jQuery(function() {
	bea_acf_taxonomy_terms_tpl = jQuery( '#tmpl-bea-taxonomy-term').html();
});
function bea_acf_term_taxonomy_refresh() {"use strict";

	jQuery('select.bea_acf_taxonomy_term_taxonomies, select.bea_acf_taxonomy_term_taxonomies_terms')
		.select2()
		.on( 'change', function( e ) {"use strict";
			var select = jQuery(e.target);
			if( select.hasClass( 'bea_acf_taxonomy_term_taxonomies' ) ) {
				if( !_.isUndefined( bea_acf_taxonomy_terms_ajax ) ) {
					bea_acf_taxonomy_terms_ajax.abort();
				}
				bea_acf_taxonomy_terms_ajax = jQuery.ajax({
					url : ajaxurl,
					type: "POST",
					dataType : 'json',
					data : {
						action : 'bea_acf_taxonomy_term',
						_ajax_nonce : bea_acf_taxonomy_term.nonce,
						taxonomies : e.val
					}
				}).success( function( response ) {
					var terms_select = select.parent().find('select.bea_acf_taxonomy_term_taxonomies_terms'),
						content = _.template( bea_acf_taxonomy_terms_tpl, {
						terms : response.data,
						selected_terms : terms_select.val()
					});
					terms_select.html( content ).select2( "val", terms_select.val() );
				});
			}
		} );
}

function be_acf_taxonomy_term_selected(value, check) {"use strict";
	return be_acf_taxonomy_term_checked_selected_helper(value, check, 'selected');
};

function be_acf_taxonomy_term_checked_selected_helper (helper, current, type) {"use strict";
	return ( helper === current ) ? type + '="' + type + '"' : '';
};