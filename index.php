<?php 

	/*
		Plugin Name: WooCommerce Payment Reminder
		Plugin URI: https://wordpress.org/plugins/woocommerce-payment-reminder/
		Description: افزونه ارسال ایمیل یادآور برای سفارشات در انتظار پرداخت ...
		Version: 0.7
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
				//$attachments = array( WP_CONTENT_DIR . '/themes/stxir/assets/ramadan1-min.jpg' );
				if ( is_email($email) ) {
					@wp_mail( $email, get_lng_wpr('email_title'), $message, $headers );
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
		}
		
	}