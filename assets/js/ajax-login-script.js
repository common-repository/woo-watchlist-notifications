jQuery(document).ready(function($) {

    // Show the login dialog box on click
    jQuery('a#login-user').on('click', function(e){
        jQuery('body').prepend('<div class="login_overlay"></div>');
        jQuery('form#login').fadeIn(500);
        jQuery('div.login_overlay, form#login a.close').on('click', function(){
            jQuery('div.login_overlay').remove();
            jQuery('form#login').hide();
        });
        e.preventDefault();
    });

    // Perform AJAX login on form submit
    jQuery(".submit_button").on("click", function(){
        var username, password, security;

                username = jQuery('#username').val(), 
                password = jQuery('#password').val(), 
                security = jQuery('#security').val() 
                jQuery(".log_error").text(" ");

            if(username ==''){jQuery(".log_error").text("Please enter username.");}
                else if (password==''){jQuery(".log_error").text("Please enter password.");}
                    else{


                              jQuery.ajax({
                                url: wwn_ajax_function.ajaxurl,
                                type: "POST", 
                                data: { 
                                    'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
                                    'username': jQuery('form#login #username').val(), 
                                    'password': jQuery('form#login #password').val(), 
                                    'security': jQuery('form#login #security').val() 
                                },
                                success: function(data){
                                    if(data == 0)
                                    {
                                        jQuery(".log_error").text("Wrong username or password.");
                                    }
                                    else{
                                        jQuery('.status').show().html("Login successful, redirecting...");
                                        location.reload();
                                    }
                                    
                                }
                            });

                    }




    });
   

});