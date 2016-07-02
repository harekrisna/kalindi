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
    		console.log(textStatus);
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

$('a.ajax').click(function (event) {
     event.preventDefault();		    
	 $.get(this.href);
});

function ajax(event, a) {
	event.preventDefault();
	$.get(a.href);
}

