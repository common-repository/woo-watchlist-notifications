jQuery(document).ready(function() {
	jQuery('.wwn-btn').click(function(e) {
		e.preventDefault();
		var this_button = jQuery(this);
		var product_title = jQuery(this).data('title');
		this_button.off('click');
		this_button.addClass('wwn-btn-disabled');
	    post_data = 'action=wwn-addtowatchlist&&product_title=' + product_title;
	    jQuery.ajax({
			type: 'post',
			url: wwn_ajaxurl,
			data:{
               'action':'wwn-addtowatchlist',
               'product_title': product_title,
            },
			dataType: "json",
			error: function(XMLHttpRequest, textStatus, errorThrown){
				this_button.addClass('wwn-btn-ko');
				this_button.text(wwn_msg_ko);				
			},
			success: function(data, textStatus){
				console.log(data);
				if(data.response && data.response == 'OK') {
					this_button.addClass('wwn-btn-ok');
					this_button.text(wwn_msg_ok);
				} else {
					this_button.addClass('wwn-btn-ko');
					this_button.text(wwn_msg_ko);
				}			
			}
		});
	});

	jQuery('.remove-watchlist').click(function(e) {
		e.preventDefault();
		var this_button = jQuery(this);
		var product_title = jQuery(this).data('title');
		this_button.off('click');
		this_button.addClass('wwn-btn-disabled');
		if(confirm("Are you sure you want to delete this product keyowrd?")) {
		    jQuery.ajax({
				type: 'post',
				url: wwn_ajaxurl,
				data:{
	               'action':'wwn-removewatchlist',
	               'product_title': product_title,
	            },
				dataType: "json",
				error: function(XMLHttpRequest, textStatus, errorThrown){
					this_button.addClass('wwn-btn-ko');
					this_button.text(wwn_msg_ko);				
				},
				success: function(data, textStatus){
					console.log(data);
					if(data.response && data.response == 'OK') {
						jQuery('.wwn-watchlist').html('<div class="wwn-watchlist"><span class="remove">Product keyword has been removed from your watchlist!</span></div>');
					} else {
						this_button.addClass('wwn-btn-ko');
						this_button.text(wwn_msg_ko);
					}			
				}
			});
		}
	});

	jQuery(".wwn-table").on("click", ".delete-link", function(e){
		e.preventDefault();
		var id = jQuery(this).attr('id');
		if(confirm("Are you sure you want to delete this keyowrd?")) {
			post_data = 'action=wwn-removewatchlistbyid&&id=' + id;
			jQuery.ajax({
				url: wwn_ajaxurl,
				type: "POST",
				data:post_data,
				dataType: "json",
				success: function(data){
					if(data.response && data.response == 'OK') {
						jQuery("#table-row-"+id).remove();
					} else {
						alert("Something went wrong. Please try again later!")
					}	
				}
			});
		}
	});

	jQuery('.add-keyword').live('click',function(){
        jQuery('.add-keyword-form').toggle();
    });

    jQuery('#save-keyword').live('click',function(e){
    	e.preventDefault();
		var keyword = jQuery('#keyword').val();
    	var this_button = jQuery(this);
		this_button.off('click');
		this_button.addClass('wwn-btn-disabled');
	    if(keyword == ""){
    		jQuery('#keyword').css("border", "red thin solid");
    		return false;
    	}
    	post_data = 'action=wwn-addtowatchlist&&product_title=' + keyword;
	    jQuery.ajax({
			type: 'post',
			url: wwn_ajaxurl,
			data:{
               'action':'wwn-addtowatchlist',
               'product_title': keyword,
            },
			dataType: "json",
			error: function(XMLHttpRequest, textStatus, errorThrown){
				this_button.addClass('wwn-btn-ko');
				this_button.text(wwn_msg_ko);				
			},
			success: function(data, textStatus){
				console.log(data);
				if(data.response && data.response == 'OK') {
					jQuery('.add-keyword-form').html('<div class="response-message"><h4>Keyword has been added to your watchlist!</h4></div>');
					 setTimeout(function(){ location.reload(); }, 1500);
				} else {
					alert("Keyword is already exist. Please try another keyword!");
				}			
			}
		});
    });

});
