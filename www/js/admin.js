jQuery.extend({
	nette: {
		beforeSend: function (xhr, settings) {
			$('div.loading').fadeIn(300);
		},
		
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload, textStatus, xhr) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}
			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
		    $('div.loading').fadeOut(400);
		},
		
		error: function (xhr, textStatus, errorThrown) {
			$('div.loading div').addClass('error');
		}
	}
});

jQuery.ajaxSetup({
	beforeSend: jQuery.nette.beforeSend,
	success: jQuery.nette.success,
	error: jQuery.nette.error,
	dataType: "json"
});

$(function(){
    $('.flash').hide().delay(500).fadeIn(1000).delay(3200).fadeOut(800);
    
    $('.flash').click(function() {
      $(this).fadeOut(800);
    });
});