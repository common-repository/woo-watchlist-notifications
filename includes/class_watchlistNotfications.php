<?php
if( !class_exists( 'watchlistNotifications' ) ) {
	class watchlistNotifications {
		function product_in_watchlist( $product_title, $user_id ) {
			global $wpdb;
			$product_title = trim($product_title);
			$str = htmlspecialchars_decode(preg_replace('/&#8243;|\'/', '?', $product_title));
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `wp_woocommerce_watchlist_notification` WHERE `user_id` = ".$user_id." AND `watchlist_keyword` LIKE '".$str."' ");
			if($count > 0) {
				return true;
			} else {
				return false;
			}
		}	

		function show_button( $product_title ) {
			?>

			<div class="wwn-watchlist">
				<a href="#" class="wwn-btn"  data-title="<?php echo $product_title; ?>" ><i class="fa fa-clock-o "></i> <?php echo empty(get_option('wwn_button_label')) ? 'Add to my watchlist' : get_option('wwn_button_label'); ?></a>
			</div>
			
			<?php
		}

		function show_addedbutton( $product_title ) {
			?>

			<div class="wwn-watchlist">
				<a href="#" class="wwn-btn-added" data-title="<?php echo $product_title; ?>" ><i class="fa fa-clock-o "></i> <?php echo empty(get_option('wwn_already_added_text_label')) ? 'Already added in my watchlist' : get_option('wwn_already_added_text_label'); ?></a>
				<a href="#" class="remove-watchlist"  data-title="<?php echo $product_title; ?>"><i class="fa fa-remove"></i>Remove</a>
			</div>
			
			<?php
		}	

		function add_product_to_watchlist( $user_id , $product_title) {
			global $wpdb;
			$item =  $wpdb->get_var( "SELECT * FROM `wp_woocommerce_watchlist_notification` WHERE `user_id` = ".$user_id." AND `watchlist_keyword` LIKE '".$product_title."' ");
			if( $item == NULL ) {
				$product_title = trim($product_title);
				$result = $wpdb->insert( 
					WWN::$table_name, 
					array(
						'user_id' => $user_id, 
						'watchlist_keyword' => $product_title,
						'watchlist_date'  => current_time( 'mysql' )
					),
					array( 
				      '%d', //data type is string
				      '%s',
				      '%s' 
				    ) 
				);
				
				if( !$result ) {
					return false;
				} else {
					return true;
				}
			} else {
				return false;
			}	

		}	

		function add_new_keyword( $user_id ) {
			$form = '<div class="add-keyword-form">';
			$form .= '<form method="post"><div class="form-group">';
			$form .='<label for="keyword">Add Keyword:</label>';
			$form .='<input type="text" required="required" class="form-control" id="keyword">';
			$form .='</div>';
			$form .='<button type="button" id="save-keyword" class="btn btn-default qbutton">Save</button>';
			$form .='</form>';
			$form .='</div>'; 
			echo $form;
		}

		function render_list( $user_id ) {

			global $wpdb;

			$items =  $wpdb->get_results( 'SELECT distinct watchlist_keyword,id FROM ' . WWN::$table_name . ' WHERE user_id = ' . $user_id );
			$i = 1; 
			if( count( $items ) > 0 ) {
				echo '<table class="wwn-table" id="watchlist-tbl"><thead><tr><th>No.</th><th colspan="3">' . __( 'Keyword List', 'wwn' ) . '</th><th>Action</th>';
				echo '</tr></thead><tbody>';
				foreach( $items as $item ) {
					echo '<tr class="table-row" id="table-row-'.$item->id.'"><td>'.$i.'</td>';
					echo '<td colspan="3">'.$item->watchlist_keyword.'</td>';
					echo '<td><a href="javascript:void(0);" class="delete-link fa fa-trash-o" id="'.$item->id.'" ></a></td>';
					echo "</tr>";	
					$i++;
				}
				echo '</tbody></table>';
			} else {
				echo '<h3>' . __( 'Your wishlist is empty', 'wwn' ) . '</h3>';
			}
			?>
				<script type="text/javascript">
				jQuery(document).ready(function() {
				jQuery('#watchlist-tbl').DataTable({
					"pageLength" : 5,
				    "columns": [
					    { "data": "no" },
				        { "data": "watchlist_keyword" },
				        { "data": "action" },
				    ]
				});
				} );   
				</script> 
			<?php 

		}	

		function remove_keyword_from_watchlist( $user_id, $product_title ) {
			
			global $wpdb;
			$result = $wpdb->delete( 
				WWN::$table_name, 
				array( 
					'user_id'  => $user_id,
					'watchlist_keyword' => $product_title
				), 
				array( 
					'%d',
					'%s'
				) 
			);
			//var_dump($result); 
			//$wpdb->show_errors();
			if( !$result ) {
				return false;
			} else {
				return true;
			}
		}
		function remove_keyword_by_id_from_watchlist( $user_id, $id ) {
			
			global $wpdb;
			$result = $wpdb->delete( 
				WWN::$table_name, 
				array( 
					'user_id'  => $user_id,
					'id' => $id
				), 
				array( 
					'%d',
					'%s'
				) 
			);
			//var_dump($result); 
			//$wpdb->show_errors();
			if( !$result ) {
				return false;
			} else {
				return true;
			}
		}		
		function set_html_content_type() {
		    return 'text/html';
		}

		function build_email_template($email_product_image, $message)
		{
		    $email_header_logo = get_option('wwn_email_header_logo');
		    $email_header_logo_url = wp_get_attachment_url($email_header_logo);
		    $email_footer_logo_url = wp_get_attachment_url(get_option('wwn_email_footer_logo'));

		    $email_template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
				    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				    <title>'.wp_title('').' | '.bloginfo( 'name' ).'</title>
				    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				</head>
				<body style="margin:0; padding:10px 0 0 0;" bgcolor="#F8F8F8">
				<table align="center" border="1" cellpadding="0" cellspacing="0" width="95%%">
				    <tr>
				        <td align="center">
				            <table align="center" border="1" cellpadding="0" cellspacing="0" width="600"
				                   style="border-collapse: separate; border-spacing: 2px 5px; box-shadow: 1px 0 1px 1px #B8B8B8;"
				                   bgcolor="#FFFFFF">
				                <tr>
				                    <td align="center" style="padding: 5px; background:'.get_option('wwn_email_bg_color').';">
				                        <a href="'.home_url().'" target="_blank">
				                            <img src="'.$email_header_logo_url.'" alt="Logo" style="width: 186px;border: 0px none; background:#fff; padding: 10px;"/>
				                        </a>
				                    </td>
				                </tr>
				                <tr>
				                    <td align="center">
				                        <!-- Initial relevant banner image goes here under src-->
				                        <img src="'.$email_product_image.'" alt="Image Banner" style="display: block;border:0;" width="600"/>
				                    </td>
				                </tr>
				                <tr>
				                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
				                        <table border="1" cellpadding="0" cellspacing="0" width="100%%">
				                            <tr>
				                                <td style="padding: 10px; font-family: Avenir, sans-serif; font-size: 16px;">'.$message.'</td>
				                            </tr>
				                        </table>
				                    </td>
				                </tr>
				                <tr>
				                    <td bgcolor="'.get_option('wwn_email_bg_color').'">
				                        <table border="1" cellpadding="0" cellspacing="0" width="100%%" style="padding: 20px 10px 10px 10px;">
				                            <tr>
				                                <td width="260" valign="top" style="padding: 0 0 15px 0;">
				                                    <table border="0" cellpadding="0" cellspacing="0" width="100%%">
				                                        <tr>
				                                            <td align="center">
				                                                <h1 style="color: #fff;margin-top: 20px;text-transform: uppercase;">Contact us</h1>
				                                            </td>
				                                        </tr>
				                                        <tr>
				                                            <td align="center" style="font-family: Avenir, sans-serif; color:#fff;font-size: 13px;padding: 10px 0 0 0;">'.get_option('wwn_contact_address').'</td>
				                                        </tr>
				                                    </table>
				                                </td>
				                                <td style="font-size: 0; line-height: 0;" width="20">
				                                    &nbsp;
				                                </td>
				                                <td width="260" valign="top">
				                                    <table border="0" cellpadding="0" cellspacing="0" width="100%%" >
				                                        <tr>
				                                            <td align="center">
				                                                <a href="'.home_url().'">
				                                                    <img src="'.$email_footer_logo_url.'" alt="Ehub" style="display: block;width:240px;"/>
				                                                </a>
				                                            </td>
				                                        </tr>
				                                        <tr>
				                                            <td align="center">
				                                                <ul id="menu-footer-account-menu" class="menu" style="display: inline-block; margin-top: 25px;text-align: center;">
				                                                	<li style="margin: 20px;list-style: outside none none;"><a href="'.get_permalink( get_option('woocommerce_myaccount_page_id') ).'" style=" color: #fff;text-transform: uppercase;font-weight: bold;text-decoration: none;">My Account</a></li>
																	<li style="margin: 20px;list-style: outside none none;"><a href="'.site_url().'" style=" color: #fff;text-transform: uppercase;font-weight: bold;text-decoration: none;">Home</a></li>
																</ul>
				                                            </td>
				                                        </tr>
				                                    </table>
				                                </td>
				                            </tr>
				                        </table>
				                    </td>
				                </tr>
				                <tr>
				                    <td bgcolor="#3a3a3a" style="padding: 15px 15px 15px 15px;">
				                        <table border="0" cellpadding="0" cellspacing="0" width="100%%">
				                            <tr>
				                                <td align="center">
				                                    <table border="0" cellpadding="0" cellspacing="0">
				                                        <tr>
				                                            <td>
				                                                <a href="mailto:'.get_option('wwn_email_link').'" target="_blank">
				                                                    <img src="'.plugins_url( 'woocommerce-watchlist-notification/assets/images/email.png').'" alt="Email" width="50" height="50"
				                                                         style="display: block;border: medium none;" border="1"/>
				                                                </a>
				                                            </td>
				                                            <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
				                                            <td>
				                                                <a href="'.get_option('wwn_facebook_link').'" target="_blank">
				                                                    <img src="'.plugins_url( 'woocommerce-watchlist-notification/assets/images/facebook.png').'" alt="Facebook" width="50" height="50" style="display: block;border: medium none;" border="1"/>
				                                                </a>
				                                            </td>
				                                            <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
				                                            <td>
				                                                <a href="'.get_option('wwn_twitter_link').'" target="_blank">
				                                                    <img src="'.plugins_url( 'woocommerce-watchlist-notification/assets/images/twitter.png').'" alt="Twitter" width="50" height="50" style="display: block;border: medium none;" border="1"/>
				                                                </a>
				                                            </td>
				                                        </tr>
				                                    </table>
				                                </td>
				                            </tr>
				                        </table>
				                    </td>
				                </tr>
				            </table>
				        </td>
				    </tr>
				</table>
				</body>
				</html>'; 
			//var_dump($email_template); die;			    
			return $email_template;
		}


		function sent_stock_alert_notification_email($post_id){
			global $wpdb;
			$post = get_post( $post_id );
			$post_title = preg_replace('/[0-9]+/', '-', $post->post_title);
			$post_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $post_title);
			$words = explode('-',$post_title); $arr = array();
			foreach ($words as $word) {
				if($word != "" && strlen($word) > 3){
					array_push($arr, $word);	
				}
			}
			$results = $wpdb->get_results( "SELECT distinct user_id FROM `wp_woocommerce_watchlist_notification` WHERE `watchlist_keyword` REGEXP '".implode('|', $arr)."'");
			if( count( $results ) > 0 ) { $email_arr = array();
				foreach ($results as $result) {
					if(!in_array($result->user_id, $email_arr)){
						$user_info = get_userdata($result->user_id);
						$to = $user_info->user_email;
						$name = esc_attr( $user_info->user_login );
						$product = wc_get_product( $post_id );
						$product_price = get_post_meta( $post_id, '_regular_price', true);
						$url = get_permalink( $post_id );
						$from = get_option('wwn_from_email');
						$subject = get_option('wwn_subject_field');
						
						$content =  get_option('wwn_mail_body');
						$content .= '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;margin-bottom: 10px;margin-top: 20px;"  border="1" bordercolor="#eee">';
						$content .= '<tbody>';
						$content .= '<tr>';
						$content .=	'<th scope="row" style="text-align:left; border: 1px solid #eee;">Product Name</th>';
						$content .=	'<td style="text-align:left; border: 1px solid #eee;"><a href="'.get_permalink($post_id).'">'.$product->get_title().'</a></td>';
						$content .= '</tr>';
						$content .= '<tr>';
						$content .=	'<th scope="row" style="text-align:left; border: 1px solid #eee;">Price</th>';
						$content .=	'<td style="text-align:left; border: 1px solid #eee;">'.$product_price.'</td>';
						$content .= '</tr>';
						$content .= '</tbody>';
						$content .= '</table>';
						$content .= '<p style="font-family:Avenir,sans-serif;font-size: 14px;line-height: 22px;">'.wpautop( $post->post_content ).'</p>';

						$email_product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
						$final_message = $this->build_email_template($email_product_image[0], $content);
						
						$headers = "MIME-Version: 1.0" . "\r\n";
						$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
						$headers .= $from;

						add_filter( 'wp_mail_content_type',array($this, 'set_html_content_type' ));
						
						$response = wp_mail( $to, $subject, $final_message, $headers );
							
						remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type' ));
						$sent_emails = array_push($email_arr, $result->user_id);	
					}	
				}
			}
		}


		function remove_product_from_watchlist( $product_id ) {
			
			global $wpdb;

			$result = $wpdb->delete( 
				WWN::$table_name, 
				array( 
					'watchlist_keyword' => $product_title
				), 
				array( 
					'%d'
				) 
			);

			if( !$result ) {
				return false;
			} else {
				return true;
			}			
		
		}	

		function login_popup(){ ?>
			<div class="wwn-watchlist">
				<a href="#" class="login-btn" id="login-user" ><i class="fa fa-sign-in "></i> <?php _e( 'Add to my watchlist', 'wwn' ); ?></a>
			</div>
			<form id="login" class="login-popup" action="login" method="post">
		        <h1>Login for add item to watchlist</h1>

		        <span class="log_error"></span>
		        <p class="status"></p>

		        
		        <label for="username">Username</label>
		        <input id="username" type="text" name="username">
		        <label for="password">Password</label>
		        <input id="password" type="password" name="password">
		        <div class="newrspan"><a class="new-register" href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>">Register</a></div>
		        <div class="lostpasspan"><a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Forgot password?</a></div>
		        <input class="submit_button" type="button" value="Login" name="submit">
		        <a class="close" href="">(Close)</a>

		        <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
    		</form>
    	<?php	
		}
			
	}

}