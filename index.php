<?php 

	/*
		Plugin Name: WooCommerce Payment Reminder
		Plugin URI: https://wordpress.org/plugins/woocommerce-payment-reminder/
		Description: افزونه ارسال ایمیل یادآور برای سفارشات در انتظار پرداخت ...
		Version: 0.8
		Author: Nima Saberi
		Author URI: http://ideyeno.ir
	*/
	
	if ( ! defined( 'ABSPATH' ) ) {  exit; }

	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		
		function get_lng_wpr($text='') {
			$text = (empty($text) ? '' : strip_tags($text));
			$url = admin_url("plugin-install.php").'?tab=plugin-information&plugin=woocommerce-count-orders-in-adminbar&TB_iframe=true&width=772&height=308';
			if ( get_bloginfo("language") === "fa-IR" ) {
				$lng = array(
					'btn_payment_reminder' => 'ارسال یادآور پرداخت',
					'admin_payment_reminder' => 'یادآور پرداخت',
					'admin_attach_title' => 'پیوست ایمیل »',
					'admin_attach_desc' => 'شما می‌توانید تصویر موردنظرتان را به ایمیل یادآور پیوست کنید.',
					'admin_attach_upload_btn' => 'آپلود یا انتخاب تصویر',
					'admin_attach_upload_input' => 'شناسه پیوست :',
					'admin_attach_save' => 'ذخیره تغییرات',
					'alert_recommend' => 'پیشنهاد می‌کنیم افزونه <a href="'.$url.'" target="_blank" class="thickbox">«نمایش تعداد سفارشات در انتظار تحویل»</a> را نصب کنید.',
					'email_title' => 'یادآور پرداخت سفارش',
					'email_header' => 'با سلام و احترام ؛',
					'email_message' => 'صورت حساب صادر شده برای شما تا این لحظه پرداخت نشده است ...',
					'email_message_desc' => 'لطفاً نسبت به مشخص کردن وضعیت سفارش خود اقدام نمایید :',
					'email_message_noreply' => 'این پیام به صورت خودکار ارسال شده است ؛ از ارسال پاسخ خودداری کنید.',
					'email_footer' => 'با تشکر',
					'email_footer_sign' => 'مدیریت '.get_bloginfo('name'),
				);
			} else {
				$lng = array(
					'btn_payment_reminder' => 'Payment Reminder',
					'admin_payment_reminder' => 'Payment Reminder',
					'admin_attach_title' => 'Email attachment',
					'admin_attach_desc' => 'You can attach an image to email.',
					'admin_attach_upload_btn' => 'Upload or Select Image',
					'admin_attach_upload_input' => 'Attach ID :',
					'admin_attach_save' => 'Save Changes',
					'alert_recommend' => 'We recommend you install <a href="'.$url.'" target="_blank" class="thickbox">WooCommerce Pending Orders in AdminBar</a>.',	
					'email_title' => 'Payment Reminder',
					'email_header' => 'Dear Customer',
					'email_message' => 'Your invoice has not been paid ...',
					'email_message_desc' => 'Please specify your order status :',
					'email_message_noreply' => 'This is an automated message, please do not answer.',
					'email_footer' => 'Kind regards',
					'email_footer_sign' => get_bloginfo('name').' Support',
				);
			}
			return (isset($lng[$text]) ? $lng[$text] : '');
		}

		$woopr_default_settings = array(
			'woopr_attach' => '',
		);
		if ( ! is_array(get_option('payment_reminder') ) ) {
			add_option('payment_reminder', $woopr_default_settings);
		}
		$woopr_options = get_option('payment_reminder');
		
		if ( isset( $_GET['action'], $_GET['email'] ) && $_GET['action'] == 'reminder' ) {
			$email = strip_tags(trim($_GET['email']));
			if ( is_admin() ) {
				require_once(ABSPATH.'wp-includes/pluggable.php');
				$message = get_lng_wpr('email_header')."\n\n";
				$message .= get_lng_wpr('email_message')."\n";
				$message .= get_lng_wpr('email_message_desc')."\n\n"; 
				$message .= home_url()."/my-account \n\n"; 
				$message .= "* ".get_lng_wpr('email_message_noreply')."\n\n";
				$message .= get_lng_wpr('email_footer')."\n".get_lng_wpr('email_footer_sign');
				$headers = "From: ".get_bloginfo('name')." <".get_bloginfo('admin_email').">\r\n";
				$attachment = (isset($woopr_options["woopr_attach"]) && !empty($woopr_options["woopr_attach"]) ? WP_CONTENT_DIR.'/'.strip_tags($woopr_options["woopr_attach"]) : FALSE); 
				if ( is_email($email) ) {
					@wp_mail( $email, get_lng_wpr('email_title'), $message, $headers, $attachment );
					wp_redirect( admin_url("/edit.php?post_status=wc-pending&post_type=shop_order") );
					exit;
				}
			}
		}
		
		function wc_btn_reminder() {
			global $post;
			if ( isset($post) && $post->post_type == 'shop_order' ) {
				$order = new WC_Order($post->ID);
				if ( $order->status == 'pending' ) {
					$html = '<div class="clear"></div>';
					$html .= '<a class="button tips email" href="'.admin_url("/edit.php?post_status=wc-pending&post_type=shop_order&action=reminder&email=".trim($order->billing_email)).'" data-tip="'.get_lng_wpr('btn_payment_reminder').'" style="margin-top: 5px;font: 11px tahoma;">';
					$html .= '<span class="dashicons dashicons-email" style="margin: 6px 0px 0 0px;font-size: 14px;"></span>';
					$html .= '</a>';
					echo $html;
				}
			}
		}
		
		function wc_payment_reminder_recommend() {
			if ( is_admin() ) {
				if ( ! is_plugin_active( 'woocommerce-count-orders-in-adminbar/index.php' ) ) {
					add_action( 'admin_notices', 'wc_pr_admin_notice' );
				}
			}
		}
		
		function wc_pr_admin_notice() {
			?>
			<style>.closebtn { background: #FF5151;color: white;padding: 0px 5px 2px 5px;font: 11px tahoma;float: left;margin: 0 10px 0 0;}</style>
			<div class="updated"> 
				<p><?= get_lng_wpr('alert_recommend'); ?> <a href="<?= admin_url("?action=wprclosebtn") ?>" class="closebtn">x</a></p>
			</div>
			<?php
		}
		
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wprclosebtn' ) {
			if ( is_admin() ) {
				require_once(ABSPATH.'wp-includes/pluggable.php');
				add_option( 'close_wc_pr_notice_v0.7', 1, '', 'yes' );
				wp_redirect( admin_url("/edit.php?post_status=wc-pending&post_type=shop_order") );
				exit;
			}
		}

		add_filter('woocommerce_admin_order_actions_end', 'wc_btn_reminder');
		add_action( 'plugins_loaded', 'wc_btn_reminder' );
		if ( is_admin() ) {
			if ( get_option( 'close_wc_pr_notice_v0.7' ) != 1 ) {
				add_action( 'admin_init', 'wc_payment_reminder_recommend' );
			}
			wp_enqueue_script( 'media-upload' );
			add_action('admin_head', 'woopr_admin_js');
		}
		
		function woopr_admin_js() {
			echo "<script type='text/javascript' src='".plugins_url('assets/admin_script.js?v1.11', __FILE__)."'></script>"; 
		}
		
		add_action('admin_menu', 'woopr_plugin_setup_menu');
		function woopr_plugin_setup_menu(){
			add_submenu_page(
				'woocommerce',
				get_lng_wpr('admin_payment_reminder'),
				get_lng_wpr('admin_payment_reminder'),
				'manage_options',
				'payment-reminder',
				'woopr_plugin_setup_menu_content'
			);
		}
		
		function woopr_plugin_setup_menu_content() {
			$woopr_options = get_option('payment_reminder');
			$html = '';
			if ( isset($_POST['form_save']) ) {
				$woopr_options["woopr_attach"] = (isset($_POST['wpss_upload_image']) ? esc_attr($_POST['wpss_upload_image']) : '');
				update_option('payment_reminder', $woopr_options);
				$html .= '<div class="updated notice is-dismissible" id="message">';
				$html .= '<p>تغییرات به درستی ذخیره شد.</p>';
				$html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button>';
				$html .= '</div>';
			}
			$html .= '<div class="wrap"><h2>'.get_lng_wpr('admin_payment_reminder').'</h2></div>';
			$html .= '<div>توسط <a href="mailto:nima@ideyeno.ir" target="_blank" style="text-decoration: none;">نیما صابری</a> در <a href="http://ideyeno.ir/" target="_blank" style="text-decoration: none;">آزمایشگاه ایده نو</a></div>';
			$html .= '<hr>';
			$html .= '<form action="" method="post">';
			$html .= '<div class="card pressthis" style="float: right;width: 100%;">';
			$html .= '<b>'.get_lng_wpr('admin_attach_title').'</b><br>';
			$html .= '<small>'.get_lng_wpr('admin_attach_desc').'</small><br><br>';
			$html .= '<input id="wpss_upload_image_button" type="button" value="'.get_lng_wpr('admin_attach_upload_btn').'" class="wpss-filebtn button" style="padding: 11px; font: 12px tahoma; height: auto;width: 310px;  margin-bottom: 10px;" />'; 
			$html .= '<br><small>'.get_lng_wpr('admin_attach_upload_input').'</small><br>';
			$html .= '<input id="wpss_upload_image" type="text" name="wpss_upload_image" value="'.$woopr_options["woopr_attach"].'" class="wpss_text wpss-file" style="padding: 10px; margin: 0 0 0 5px; width: 200px;" />';
			$html .= '<input value="'.get_lng_wpr('admin_attach_save').'" type="submit" name="form_save" class="button button-secondary" style="padding: 6px 15px; height: auto;"/>';
			$html .= '</div>';
			$html .= '</form>';
			echo $html;
		}
		
		// انتقال به پنل تنظیمات پس از نصب
		register_activation_hook(__FILE__, 'woopr_activate');
		add_action('admin_init', 'woopr_plugin_redirect');
		function woopr_activate() {
			add_option('woopr_plugin_do_activation_redirect', true);
		}
		function woopr_plugin_redirect() {
			if (get_option('woopr_plugin_do_activation_redirect', false)) {
				delete_option('woopr_plugin_do_activation_redirect');
				wp_redirect(admin_url('admin.php?page=payment-reminder'));
				exit;
			}
		}
		
	}