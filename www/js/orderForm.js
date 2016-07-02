	var surname = $("input[name=surname]");
	var address = $("input[name=address]");
	var phone = $("input[name=phone]");
	var email = $("input[name=email]");
	var note = $("input[name=note]");
	    
	var monday = $('input[name=monday]');
	var tuesday = $('input[name=tuesday]');
	var wednesday = $('input[name=wednesday]');
	var thursday = $('input[name=thursday]');
	var friday = $('input[name=friday]');
	
	// schovávání a zobrazování labelů vstupních polí
	$(surname).focusin(function() {  if( $(this).val() == "Jméno a příjmení")   $(this).val("") })
	       .focusout(function() {    if( $(this).val() == "")        $(this).val("Jméno a příjmení")});
	
	$(address).focusin(function() {  if( $(this).val() == "Adresa")  $(this).val("")})
	          .focusout(function() { if( $(this).val() == "")        $(this).val("Adresa")});
	
	$(phone).focusin(function() {    if( $(this).val() == "Telefon") $(this).val("")})
	        .focusout(function() {   if( $(this).val() == "")        $(this).val("Telefon")}); 
	
	$(email).focusin(function() {    if( $(this).val() == "e-mail")  $(this).val("")})
	        .focusout(function() {   if( $(this).val() == "")        $(this).val("e-mail")});
       
	$(note).focusin(function() {    if( $(this).val() == "Vaše poznámka")    $(this).val("")})
	        .focusout(function() {   if( $(this).val() == "")        $(this).val("Vaše poznámka")});
	
	// odesílací tlačítko
	$('form#frm-orderForm').submit(function () {
	    // kontrola zadaných údajů
	    if(surname.val() == "Jméno a příjmení" || surname.val() == "") {
	        alert("Prosím zadejte celé jméno.");
	        surname.val("");
	        surname.focus();
	        return false;
	    }
	    if(address.val() == "Adresa" || address.val() == "") {
	        alert("Prosím zadejte adresu.");
	        address.val("");
	        address.focus();
	        return false;
	    }
	    if(phone.val() == "Telefon" || phone.val() == "") {
	        alert("Prosím zadejte telefon.");
	        phone.val("");
	        phone.focus();
	        return false;
	    }
	    if(phone.val().length < 9) {
	        alert("Telefon musí mít 9 číslic.");
	        phone.focus();
	        return false;
	    }
	    
	    var is_some_order = false;
	    $(".order-table input:not(:disabled)").each(function() {
	        if($(this).val() != "" && $(this).val() != "0") {
	          is_some_order = true;
	          return false; //break each
	        }
	    }); 
	    
	    if(!is_some_order) {
	        alert("Prosím objednejte si obědy.");
	        return false;
	    }
	    
	    $('form#frm-orderForm').submit();
	});
	
	// pole telefon přijímá pouze číslice a znak +
	$("input[name=phone]").keypress(function (evt) {
	    var charCode = evt.which;
	    var value = $(this).val();
	
	//        if(charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57) && value.indexOf('+') == 0 && value.length == 11)
	//	       	return false;
	    
		if (charCode == 43) {
			if($(this).val().length != 0)
	            return false;		
	    }
	    else if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
	    	return false;
	});
	
	// formátuje telefonní číslo při zadávání
	$("input[name=phone]").keyup(function () {
	    var phone = $(this).val();
	    var sepphone = ("" + phone).replace(/(\d\d\d)(?=(\d\d\d)+(?!\d))/g, function($1) { return $1 + " " });
	    $(this).val(sepphone);
	});	    
	
	// pole pro počet obědů přijímají jen dvoumístné číslice
	$('.order-table input').keypress(function (evt){
	    var charCode = evt.which;
	    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) // pouze číslice
	        return false;
	    
	    if($(this).val().length >= 2 && // pouze 2 znaky
	       charCode != 8 && charCode != 0 // tab, backspace a sipky prijima
	      ) 
	    return false;
	    
	    var keyCode = evt.keyCode;
		var count = parseInt($(this).val());
	    if (keyCode == 38) {
	  		var arrow = $(this).parent().next().find(".arrow-increase");
			arrow.css("background-image", 'url("/images/layout/arrow-up-hover.png")');
			
			if(isNaN(count))
				count = 0;
			if(count == 99)
				return;
			count++;
			
			$(this).val(count);	
		}
		
		if (keyCode == 40) {
	  		var arrow = $(this).parent().next().find(".arrow-decrease");
			arrow.css("background-image", 'url("/images/layout/arrow-down-hover.png")');
	
			if(isNaN(count))
				count = 0;
			if(count == 0)
				return;
			count--;
			
			$(this).val(count);	
		}
	    count_price($(this));
	});	    
	
	$('.order-table .arrow-increase').click(function() {
		var input = $(this).parent().find("input");
		var count = parseInt(input.val());
		if(isNaN(count))
			count = 0;
		if(count == 99)
			return;
		count++;
		input.val(count);		
	
	    count_price(input);
	});
	
	$('.order-table .arrow-decrease').click(function() {
		var input = $(this).parent().find("input");
		var count = parseInt(input.val());
		if(isNaN(count))
			count = 0;
		if(count == 0)
			return;
		count--;
		input.val(count);		
	
	    count_price(input);
	});
	
	$('.order-table input').keyup(function (evt){
	    var keyCode = evt.keyCode;
	    if (keyCode == 38) {
	   		var arrow = $(this).parent().next().find(".arrow-increase");
			arrow.css("background-image", 'url("/images/layout/arrow-up.png")');
		}
		if (keyCode == 40) {
	  		var arrow = $(this).parent().next().find(".arrow-decrease");
			arrow.css("background-image", 'url("/images/layout/arrow-down.png")');
		}
		count_price($(this));
	});  
	
	// vypočítá ceny obědů
	function count_price(input) {
	    var lunch_price = 90;
	    var count = input.val();
	    var day_price = count * lunch_price;
        input.parent().find('.price span').html(day_price);

	    var total_price = 0;
	    $("#order-table input:not(:disabled), #order-table-next input").each(function() {
	        day_price = $(this).val() * lunch_price;
	        total_price += day_price;
	    });
	    
	    $('#sum-price').html(total_price);
	}