<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class Woocommerce_watchlist_notifications_settings {
    private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $settings_base;
	private $settings;
	public function __construct() {
		
		$this->settings_base = 'wwn_';
		// Initialise settings
		add_action( 'admin_init', array( $this, 'wwn_admin_init' ) );
		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'wwn_register_settings' ) );
		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'wwn_add_watchlist_menu' ) );
		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ) , array( $this, 'wwn_add_settings_link' ) );
	}
	/**
	 * Initialise settings
	 * @return void
	 */
	public function wwn_admin_init() {
		$this->settings = $this->wwn_settings_fields();
	}
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function wwn_add_watchlist_menu() {
		$page = add_menu_page( __( 'Watchlist Notification', 'wwn_textdomain' ) , __( 'Watchlist Notification', 'wwn_textdomain' ) , 'manage_options' , 'wwn_settings' ,  array( $this, 'wwn_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wwn_settings_assets' ) );
	}
	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function wwn_settings_assets() {
		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_register_script( 'wwn-admin', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), null, true );
		wp_enqueue_script( 'wwn-admin' );
		wp_register_style( 'wwn-css', plugins_url( 'assets/css/admin.css', __FILE__ ), array(),null, false );
		wp_enqueue_style( 'wwn-css' );
	}
	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function wwn_add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=plugin_settings">' . __( 'Settings', 'wwn_textdomain' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}
	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function wwn_settings_fields() {
		$settings['standard'] = array(
			'title'					=> __( 'General Settings', 'wwn_textdomain' ),
			'description'			=> __( 'These are standard settings of the watchlist notification.', 'wwn_textdomain' ),
			'fields'				=> array(
				array(
					'id' 			=> 'button_label',
					'label'			=> __( 'Button Label' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter button label for the single product page.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Add to my watchlist', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'button_color',
					'label'			=> __( 'Button Background Color' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter background color for button', 'wwn_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#1a49b8',
					'placeholder'	=> __( '#1a49b8', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'button_hover_color',
					'label'			=> __( 'Button Hover Color' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter background color on hover for button', 'wwn_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#3cb0fd',
					'placeholder'	=> __( '#3cb0fd', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'button_text_color',
					'label'			=> __( 'Button text color' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter text color on button', 'wwn_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#ffffff',
					'placeholder'	=> __( '#ffffff', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'button_hover_text_color',
					'label'			=> __( 'Button hover text color' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter text color on button', 'wwn_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#ffffff',
					'placeholder'	=> __( '#ffffff', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'after_added_button_label',
					'label'			=> __( 'Added keyword button label' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter button label when user will add any product to the watchlist on the single product page.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Added keyword to my watchlist!', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'already_added_text_label',
					'label'			=> __( 'Already added keyword text' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter message which will display when user has already added any product to the watchlist on the single product page.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Already added in my watchlist', 'wwn_textdomain' )
				),

				
			)
		);
		$settings['extra'] = array(
			'title'					=> __( 'Email Settings', 'wwn_textdomain' ),
			'description'			=> __( 'These are emails settings for the notification email when any new product will be matched with the saved keyword according to the users.', 'wwn_textdomain' ),
			'fields'				=> array(
				array(
					'id' 			=> 'email_header_logo',
					'label'			=> __( 'Email header logo' , 'wwn_textdomain' ),
					'description'	=> __( 'Please upload a logo for notification email header.', 'wwn_textdomain' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> __( 'Upload logo', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'email_bg_color',
					'label'			=> __( 'Email background color' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter background color for header and footer', 'wwn_textdomain' ),
					'type'			=> 'color',
					'default'		=> '#1a48b8',
					'placeholder'	=> __( '#1a48b8', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'subject_field',
					'label'			=> __( 'Subject' , 'wwn_textdomain' ),
					'description'	=> __( 'Please enter the subject for notification emails to the users.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'New product added on the stock', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'from_email',
					'label'			=> __( 'From name', 'wwn_textdomain' ),
					'description'	=> __( 'Enter the from name for email.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> 'Website Name <test@websitedomain.com>'
				),
				array(
					'id' 			=> 'mail_body',
					'label'			=> __( 'Email Content' , 'wwn_textdomain' ),
					'description'	=> __( 'Enter short content of an email. Which will be appear before the product item details.', 'wwn_textdomain' ),
					'type'			=> 'editor',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'contact_address',
					'label'			=> __( 'Contact address' , 'wwn_textdomain' ),
					'description'	=> __( 'Enter contact address to display that on email footer on left column.', 'wwn_textdomain' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'email_footer_logo',
					'label'			=> __( 'Email footer logo' , 'wwn_textdomain' ),
					'description'	=> __( 'Please upload a logo for notification email on footer right column.', 'wwn_textdomain' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> __( 'Upload footer logo', 'wwn_textdomain' )
				),
				array(
					'id' 			=> 'email_link',
					'label'			=> __( 'Mail to Link', 'wwn_textdomain' ),
					'description'	=> __( 'Enter the email to link for sending email.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'facebook_link',
					'label'			=> __( 'Facebook Link', 'wwn_textdomain' ),
					'description'	=> __( 'Enter the facebook link for email footer.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'twitter_link',
					'label'			=> __( 'Twitter Link', 'wwn_textdomain' ),
					'description'	=> __( 'Enter the twitter link for email footer.', 'wwn_textdomain' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				)
			)
		);
		$settings = apply_filters( 'plugin_settings_fields', $settings );
		return $settings;
	}
	/**
	 * Register plugin settings
	 * @return void
	 */
	public function wwn_register_settings() {
		if( is_array( $this->settings ) ) {
			foreach( $this->settings as $section => $data ) {
				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'wwn_settings_section' ), 'plugin_settings' );
				foreach( $data['fields'] as $field ) {
					// Validation callback for field
					$validation = '';
					if( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}
					// Register field
					$option_name = $this->settings_base . $field['id'];
					register_setting( 'plugin_settings', $option_name, $validation );
					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this, 'wwn_display_field' ), 'plugin_settings', $section, array( 'field' => $field ) );
				}
			}
		}
	}
	public function wwn_settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}
	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function wwn_display_field( $args ) {
		$field = $args['field'];
		$html = '';
		$option_name = $this->settings_base . $field['id'];
		$option = get_option( $option_name );
		$data = '';
		if( isset( $field['default'] ) ) {
			$data = $field['default'];
			if( $option ) {
				$data = $option;
			}
		}
		switch( $field['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '"/>' . "\n";
			break;
			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value=""/>' . "\n";
			break;
			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;
			case 'checkbox':
				$checked = '';
				if( $option && 'on' == $option ){
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
			break;
			case 'checkbox_multi':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( in_array( $k, $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;
			case 'radio':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;
			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach( $field['options'] as $k => $v ) {
					$selected = false;
					if( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;
			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach( $field['options'] as $k => $v ) {
					$selected = false;
					if( in_array( $k, $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
				}
				$html .= '</select> ';
			break;
			case 'image':
				$image_thumb = '';
				if( $data ) {
					$image_thumb = wp_get_attachment_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'wwn_textdomain' ) . '" data-uploader_button_text="' . __( 'Use image' , 'wwn_textdomain' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'wwn_textdomain' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'wwn_textdomain' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;
			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="color" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;
			case 'editor':
	        $editor = 'wpt_mail_body';
	        // See my comment below
	        $editor_settings = array(
	                                'wpautop'       =>      true,
	                                'media_buttons' =>      true,
	                                'textarea_name' =>      esc_attr( $option_name ),
	                                'textarea_rows' =>      10,
	                                'drag_drop_upload' =>   true);

	        $html .= wp_editor( stripslashes( $data ), $editor, $editor_settings);

			break;	
		}
		switch( $field['type'] ) {
			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;
			default:
				$html .= '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
			break;
		}
		echo $html;
	}
	/**
	 * Validate individual settings field
	 * @param  string $data Inputted value
	 * @return string       Validated value
	 */
	public function wwn_validate_field( $data ) {
		if( $data && strlen( $data ) > 0 && $data != '' ) {
			$data = urlencode( strtolower( str_replace( ' ' , '-' , $data ) ) );
		}
		return $data;
	}
	/**
	 * Load settings page content
	 * @return void
	 */
	public function wwn_settings_page() {
		// Build page HTML
		$html = '<div class="wwn-watchlist-admin wrap" id="wwn_plugin_settings">' . "\n";
		$html .= '<h2>' . __( 'Watchlist Keyword Notification Settings' , 'wwn_textdomain' ) . '</h2>' . "\n"; 
		if( isset($_GET["settings-updated"]) ) {
			$html .= '<div id="message" class="updated"><p><strong>Settings saved.</strong></p></div>';
		} 
		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";
		// Setup navigation
		$html .= '<div class="watchlist-admin">' . "\n";
		// Get settings fields
		ob_start();
		settings_fields( 'plugin_settings' );
		do_settings_sections( 'plugin_settings' );
		$html .= ob_get_clean();
		$html .= '<p class="submit">' . "\n";
		$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'wwn_textdomain' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";
		echo $html;
	}
}