<?php 

	/*
		Plugin Name: Woocommerce Payment Reminder
		Plugin URI: https://wordpress.org/plugins/woocommerce-payment-reminder/
		Description: افزونه ارسال ایمیل یادآور برای سفارشات در انتظار پرداخت ...
		Version: 0.3
		Author: Nima Saberi
		Author URI: http://ideyeno.ir
	*/
	
	if ( ! defined( 'ABSPATH' ) ) {  exit; }

	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		
		if ( isset( $_GET['action'], $_GET['email'] ) && $_GET['action'] == 'reminder' ) {
			$email = strip_tags(trim($_GET['email']));
			if ( is_admin() ) {
				require_once(ABSPATH.'wp-includes/pluggable.php');
				$message = "با سلام و احترام ؛\n\n";
				$message .= "صورت حساب صادر شده برای شما تا این لحظه پرداخت نشده است!\n";
				$message .= "لطفاً نسبت به مشخص کردن وضعیت سفارش خود اقدام نمایید :\n\n"; 
				$message .= home_url()."/my-account \n\n"; 
				$message .= "* این پیام به صورت خودکار ارسال شده است ؛ از ارسال پاسخ خودداری کنید.\n";
				$message .= "با تشکر\n".get_bloginfo('name');
				$headers = "From: ".get_bloginfo('name')." <".get_bloginfo('admin_email').">\r\n";
				//$attachments = array( WP_CONTENT_DIR . '/themes/stxir/assets/ramadan1-min.jpg' );
				if ( is_email($email) ) {
					wp_mail( $email, 'Payment Reminder - یادآور پرداخت', $message, $headers );
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
					$html .= '<a class="button tips email" href="'.admin_url("/edit.php?post_status=wc-pending&post_type=shop_order&action=reminder&email=".trim($order->billing_email)).'" data-tip="Payment Reminder<br>یادآور پرداخت" style="margin-top: 5px;font: 11px tahoma;">';
					$html .= '<span class="dashicons dashicons-email" style="margin: 6px 0px 0 0px;font-size: 14px;"></span>';
					$html .= '</a>';
					echo $html;
				}
			}
		}
		
		add_filter('woocommerce_admin_order_actions_end', 'wc_btn_reminder');
		add_action( 'plugins_loaded', 'wc_btn_reminder' );
		
	}