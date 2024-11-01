<?php
header("Content-type: text/css; charset: UTF-8");
require_once('../../../../../wp-load.php');
?>
.wwn-watchlist .wwn-btn {background-color: <?php echo get_option('wwn_button_color'); ?> !important; border: medium none; border-radius: 3px; box-shadow: none; color: <?php echo get_option('wwn_button_text_color'); ?>!important; cursor: pointer; font-size: 13px; font-weight: bold; padding: 10px 15px; text-shadow: initial; text-transform: uppercase; }
.wwn-watchlist .wwn-btn:hover {color: <?php echo get_option('wwn_button_hover_text_color'); ?>!important; background: <?php echo get_option('wwn_button_hover_color'); ?>!important;text-decoration: none; }
form.login-popup input.submit_button{background-color: <?php echo get_option('wwn_button_color'); ?> !important; color: <?php echo get_option('wwn_button_text_color'); ?>!important;}
form.login-popup input.submit_button:hover{color: <?php echo get_option('wwn_button_hover_text_color'); ?>!important; background: <?php echo get_option('wwn_button_hover_color'); ?>!important;text-decoration: none; }
.wwn-watchlist .login-btn {  background-color: <?php echo get_option('wwn_button_color'); ?> !important; color: <?php echo get_option('wwn_button_text_color'); ?>!important;  }
.wwn-watchlist .login-btn:hover {color: <?php echo get_option('wwn_button_hover_text_color'); ?>!important; background: <?php echo get_option('wwn_button_hover_color'); ?>!important;}