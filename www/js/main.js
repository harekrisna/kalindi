$(function(){
	// display flash message smoothly
	$('.flash').hide().delay(500).fadeIn(1000);
	$('.flash:not(.error)').delay(3200).fadeOut(800);
    
    $('.flash').click(function() {
      $(this).fadeOut(800);
    });
});
