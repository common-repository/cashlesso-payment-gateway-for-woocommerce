<?php

/*
  Plugin Name: Cashlesso Woocommerce Kit
  Description: WooCommerce with Cashlesso Payment Gateway.
  Version: 2.1.0
  Author: Cashlesso
  Author URI: https://www.cashlesso.com/

  Copyright: © 2015-2021 Cashlesso.

 */


if (!defined('ABSPATH'))
    exit;
add_action('plugins_loaded', 'woocommerce_cashlesso_initialize', 0);

function woocommerce_cashlesso_initialize() {

    if (!class_exists('WC_Payment_Gateway'))
        return;

    class WC_Cashlesso_class extends WC_Payment_Gateway {

        public $payment_option;
        public $card_type;
        public $card_name;
        public $data_accept;
        public $card_number;
        public $expiry_month;
        public $expiry_year;
        public $cvv_number;
        public $issuing_bank;
        public $TXNTYPE;
        public $user_id;
        public $title = "CASHLESSO";

        public function __construct() {

            $this->id = 'cashlesso';
            $this->method_title = __('Cashlesso', 'woocommerce');
            $this->icon = plugins_url('images/logo.png', __FILE__);
            $this->method_description = __('Pay with Cashlesso India fastest Payment Gateway.', 'woocommerce');
            $this->has_fields = true;
            $this->get_title();
            $this->init_form_fields();
            $this->init_settings();
            $this->supports = array(
                'products',
                'refunds'
            );

            //$this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['merchant_id'];
            $this->working_key = $this->settings['working_key'];

          //$this->enable_currency_conversion = $this->settings['enable_currency_conversion'];
            $this->default_add1 = $this->settings['default_add1'];
            $this->default_country = $this->settings['default_country'];
            $this->default_state = $this->settings['default_state'];
            $this->default_city = $this->settings['default_city'];
            $this->default_zip = $this->settings['default_zip'];
            $this->default_phone = $this->settings['default_phone'];

            $this->return_url = home_url('/wc-api/WC_Cashlesso_class');
            $this->sandbox = $this->settings['sandbox'];
            $this->product_description = 'Product Description';
            $this->msg['message'] = "";
            $this->msg['class'] = "";
            $this->TXNTYPE = $this->settings['TXNTYPE'];
            $this->payment_option = isset($_POST['payment_option']) ? sanitize_text_field($_POST['payment_option']) : "";
            $this->card_type = isset($_POST['card_type']) ? sanitize_text_field($_POST['card_type']) : "";
            $this->card_name = isset($_POST['card_name']) ? sanitize_text_field($_POST['card_name']) : "";
            $this->data_accept = isset($_POST['data_accept']) ? sanitize_text_field($_POST['data_accept']) : "";
            $this->card_number = isset($_POST['card_number']) ? sanitize_text_field($_POST['card_number']) : "";
            $this->expiry_month = isset($_POST['expiry_month']) ? sanitize_text_field($_POST['expiry_month']) : "";
            $this->expiry_year = isset($_POST['expiry_year']) ? sanitize_text_field($_POST['expiry_year']) : "";
            $this->cvv_number = isset($_POST['cvv_number']) ? sanitize_text_field($_POST['cvv_number']) : "";
            $this->issuing_bank = isset($_POST['issuing_bank']) ? sanitize_text_field($_POST['issuing_bank']) : "";


            if ($this->sandbox == 'yes') {
                $this->liveurlonly = "https://uat.cashlesso.com/pgui/jsp/paymentrequest";
            } else {
                $this->liveurlonly = "https://www.cashlesso.com/pgui/jsp/paymentrequest";
            }

            $this->liveurl = $this->liveurlonly;

            add_action('woocommerce_api_wc_cashlesso_class', array($this, 'check_cashlesso_response'));
            add_action('valid-cashlesso-request', array($this, 'successful_request'));


            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            }


            add_action('woocommerce_receipt_cashlesso', array($this, 'receipt_page'));
            add_action('woocommerce_thankyou_cashlesso', array($this, 'thankyou_page'));
        }

        function init_form_fields() {
            $countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'variable'),
                    'type' => 'checkbox',
                    'label' => __('Enable Cashlesso Payment Module.', 'variable'),
                    'default' => 'no'),
                'sandbox' => array(
                    'title' => __('Enable Sandbox?', 'variable'),
                    'type' => 'checkbox',
                    'label' => __('Enable Sandbox Cashlesso Payment.', 'variable'),
                    'default' => 'no'),
                'merchant_id' => array(
                    'title' => __('Merchant ID', 'variable'),
                    'type' => 'text',
                    'description' => __('Given to Merchant by Cashlesso', 'variable')),
                'working_key' => array(
                    'title' => __('Salt', 'variable'),
                    'type' => 'text',
                    'description' => __('Given to Merchant by Cashlesso', 'variable')),
                'TXNTYPE' => array(
                    'title' => __('TXNTYPE', 'variable'),
                    'type' => 'text',
                    'description' => __('Like sale etc', 'variable')),
                'description' => array(
                    'title' => __('Description:', 'variable'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'variable'),
                    'default' => __('Pay securely using India\'s one of the Payment Gateway.', 'variable')),
                'default_add1' => array(
                    'title' => __('Default Address', 'variable'),
                    'type' => 'text',
                    'description' => __('Enter Address in case of user address not selected while checkout. eg: 302 california, US', 'variable'),
                ),
                'default_city' => array(
                    'title' => __('Default City', 'variable'),
                    'type' => 'text',
                    'description' => __('Enter City in case of user city not selected while checkout. eg: California', 'variable'),
                ),
                'default_state' => array(
                    'title' => __('Default State', 'variable'),
                    'type' => 'text',
                    'description' => __('Enter State in case of user state not selected while checkout. eg: US', 'variable'),
                ),
                'default_zip' => array(
                    'title' => __('Default Zip', 'variable'),
                    'type' => 'text',
                    'description' => __('Enter Zip in case of user zip not selected while checkout. eg: 452145', 'variable'),
                ),
                'default_country' => array(
                    'title' => __('Default Country', 'variable'),
                    'type' => 'select',
                    'options' => $countries,
                    'description' => __('Select Country in case of user country not selected while checkout. eg: US', 'variable'),
                ),
                'default_phone' => array(
                    'title' => __('Default Phone Number', 'variable'),
                    'type' => 'text',
                    'description' => __('Enter Phone Number in case of user phone number not selected while checkout. eg: 91-253-258694', 'variable'),
                ),
            );
        }

        public function admin_options() {
            echo '<h3>' . __('Cashlesso Payment Gateway', 'variable') . '</h3>';
            echo '<p>' . __('Cashlesso is most popular payment gateway for online shopping in India') . '</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }

        function receipt_page($order) {
            echo '<p>' . __('Thank you for your order, please click the button below to pay with Cashlesso.', 'variable') . '</p>';
            echo $this->generate_cashlesso_form($order);
        }

        function thankyou_page($order) {
            if (!empty($this->instructions))
                echo wpautop(wptexturize($this->instructions));
        }

        function process_payment($order_id) {
            $order = new WC_Order($order_id);
            update_post_meta($order_id, '_post_data', filter_input_array(INPUT_POST));
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true));
        }

        function check_cashlesso_response() {
            global $woocommerce;

            $msg['class'] = 'error';
            $msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {


                $responce_array = filter_input_array(INPUT_POST);

                foreach ($responce_array as $key => $value) {
                    if ($key == 'HASH') {
                        continue;
                    } else {
                        $responceParamsJoined[] = "$key=$value";
                    }
                }

                $responce_data_hash = cashlesso_hash_responce_data($responceParamsJoined, $this->working_key);

                $receivedHash = sanitize_text_field($_REQUEST['HASH']);
                $order_id = (int) $_REQUEST['ORDER_ID'];
                $RESPONSE_CODE = sanitize_text_field($_REQUEST['RESPONSE_CODE']);
                $PG_REF_NUM = sanitize_text_field($_REQUEST['PG_REF_NUM']);
                $TXN_ID = sanitize_text_field($_REQUEST['TXN_ID']);


                if ($responce_data_hash == $receivedHash) {
                    if ($order_id != '') {

                        try {
                            $order = new WC_Order($order_id);
                            $transauthorised = false;

                            if ($order->status !== 'completed') {
                                if ($RESPONSE_CODE == "000") {
                                    $transauthorised = true;
                                    $msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
                                    $msg['class'] = 'success';
                                    if ($order->status != 'processing') {
                                        $order->payment_complete($PG_REF_NUM);
                                        $order->set_transaction_id($PG_REF_NUM);
                                        $order->set_payment_method($title);
                                        $order->set_payment_method_title($title);
                                        $order->add_order_note('Cashlesso payment successful.<br/>Ref Number: ' . $PG_REF_NUM);
                                        $order->add_order_note('Your TXN_ID is:' . $TXN_ID);
                                        $woocommerce->cart->empty_cart();
                                    }
                                } else {

                                    $msg['class'] = 'error';
                                    $msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
                                }

                                if ($transauthorised == false) {
                                    $order->update_status('failed');
                                    $order->add_order_note('Cashlesso payment Failed');
                                    $order->set_payment_method($title);
                                    $order->set_payment_method_title($title);
                                    $order->add_order_note($this->msg['message']);
                                }
                            }
                        } catch (Exception $e) {

                            $msg['class'] = 'error';
                            $msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
                            // $woocommerce->cart->empty_cart();
                        }
                    }
                } else {

                    $notice_type = 'error';
                    $msg['message'] = "Hash code not match";

                    $order->update_status('failed');
                    $order->add_order_note('Cashlesso payment Failed');
                    $order->add_order_note($this->msg['message']);
                    //$woocommerce->cart->empty_cart();
                }
            }
            if (function_exists('wc_add_notice')) {
                wc_add_notice($msg['message'], $msg['class']);
            } else {
                if ($msg['class'] == 'success') {
                    $woocommerce->add_message($msg['message']);
                } else {
                    $woocommerce->add_error($msg['message']);
                    // $woocommerce->cart->empty_cart();
                }
                $woocommerce->set_messages();
            }

            $redirect_url = $this->get_return_url($order);
            wp_redirect($redirect_url);
            exit;
        }

        public function generate_cashlesso_form($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);
            $order_id = $order_id;

            $post_data = get_post_meta($order_id, '_post_data', true);
            update_post_meta($order_id, '_post_data', array());

            if ($order->billing_address_1 && $order->billing_country && $order->billing_state && $order->billing_city && $order->billing_postcode) {
                $country = wc()->countries->countries [$order->billing_country];
                $state = $order->billing_state;
                $city = $order->billing_city;
                $zip = $order->billing_postcode;
                $phone = $order->billing_phone;
                $billing_address_1 = trim($order->billing_address_1, ',');
            } else {
                $billing_address_1 = $this->default_add1;
                $country = $this->default_country;
                $state = $this->default_state;
                $city = $this->default_city;
                $zip = $this->default_zip;
                $phone = $this->default_phone;
            }

            $the_order_totals = $order->order_total;
            $the_order_total = $the_order_totals * 100;
            $the_currency = get_woocommerce_currency();

            if ($the_currency == 'INR') {
                $the_currency = '356';
            } elseif ($the_currency == 'GBP') {
                $the_currency = '826';
            } elseif ($the_currency == 'USD') {
                $the_currency = '840';
            } elseif ($the_currency == 'EUR') {
                $the_currency = '978';
            } else {
                $the_currency = '356';
            }


            $cashlesso_args = array(
                'PAY_ID' => $this->merchant_id,
                'ORDER_ID' => $order_id,
                'AMOUNT' => $the_order_total,
                'TXNTYPE' => $this->TXNTYPE,
                'CUST_NAME' => $order->billing_first_name . ' ' . $order->billing_last_name,
                'CUST_STREET_ADDRESS1' => $billing_address_1,
                'CUST_ZIP' => $zip,
                'CUST_PHONE' => $phone,
                'CUST_EMAIL' => $order->billing_email,
                'CUST_ID' => 'CUST' . $this->merchant_id . $order_id,
                'PRODUCT_DESC' => 'DemoProduct',
                'CURRENCY_CODE' => $the_currency,
                'RETURN_URL' => $this->return_url,
            );

            ksort($cashlesso_args);
            foreach ($cashlesso_args as $param => $value) {
                $paramsJoined[] = "$param=$value";
            }

            $Secret_hash = cashlesso_hash_data($paramsJoined, $this->working_key);


            $form = '';

            //redirect to Cashlesso site
            wc_enqueue_js('
					$.blockUI({
						message: "' . esc_js(__('Thank you for your order. We are now redirecting you to Cashlesso to make payment.', 'woocommerce')) . '",
						baseZ: 99999,
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
							padding:        "20px",
							zindex:         "9999999",
							textAlign:      "center",
							color:          "#555",
							border:         "3px solid #aaa",
							backgroundColor:"#fff",
							cursor:         "wait",
							lineHeight:     "24px",
						}
					});
				jQuery("#submit_cashlesso_payment_form").click();
				');
            $targetto = 'target="_top"';
            $transact = "/transact";
            $cashlesso_args_array = array();
            foreach ($cashlesso_args as $param => $value) {

                $cashlesso_args_array[] = "<input type='hidden' name='$param' value='$value'/>";
            }

            $cashlesso_args_array[] = "<input type='hidden' name='HASH' value='$Secret_hash'/>";

            $form .= '<form action="' . esc_url($this->liveurl) . '" method="post" id="cashlesso_payment_form"  ' . $targetto . '>
				' . implode('', $cashlesso_args_array) . '
				<!-- Button Fallback -->
				<div class="payment_buttons">
				<input type="submit" class="button alt" id="submit_cashlesso_payment_form" value="' . __('Pay via Cashlesso', 'woocommerce') . '" /> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'woocommerce') . '</a>
				</div>
				<script type="text/javascript">
				jQuery(".payment_buttons").hide();
				</script>
				</form>';

            return $form;
        }

        public function can_refund_order($order) {
            return $order && $this->supports('refunds');
        }

        public function process_refund($order_id, $amount = null, $reason = '') {
            $order = wc_get_order($order_id);
            $trans = $order->transaction_id;
            $OrdesId = $order->id;
            $Amount = $amount * 100;
            $RefundOrderId = REF . $OrdesId . rand(1, 1000);
            $Refund_Hash_ARRAY = array(
                'PG_REF_NUM' => $trans,
                'AMOUNT' => $Amount,
                'REFUND_ORDER_ID' => $RefundOrderId,
                'TXNTYPE' => 'REFUND',
                'ORDER_ID' => $OrdesId,
                'PAY_ID' => $this->merchant_id,
                'CURRENCY_CODE' => '356'
            );
            ksort($Refund_Hash_ARRAY);
            foreach ($Refund_Hash_ARRAY as $param => $value) {
                $refundParamsJoined[] = "$param=$value";
            }
            $Secret_hash = cashlesso_hash_data($refundParamsJoined, $this->working_key);

            if (!$this->can_refund_order($order)) {
                return new WP_Error('error', __('Refund failed.', 'woocommerce'));
            }

            $refundJsonObj->PG_REF_NUM = $trans;
            $refundJsonObj->AMOUNT = $Amount;
            $refundJsonObj->TXNTYPE = 'REFUND';
            $refundJsonObj->ORDER_ID = $OrdesId;
            $refundJsonObj->REFUND_ORDER_ID = $RefundOrderId;
            $refundJsonObj->PAY_ID = $this->merchant_id;
            $refundJsonObj->CURRENCY_CODE = '356';
            $refundJsonObj->HASH = $Secret_hash;

            $RefundJson = json_encode($refundJsonObj);
            
             if ($this->sandbox == 'yes') {
                $refundUrl = "https://uat.cashlesso.com/pgws/transact";
            } else {
                $refundUrl = "https://www.cashlesso.com/pgws/transact";
            }

            $raw_response = wp_safe_remote_post($refundUrl, array(
                'method' => 'POST',
                'headers' => array('Content-Type' => 'application/json'),
                'body' => $RefundJson
                    )
            );
            $body = wp_remote_retrieve_body($raw_response);
            $data = json_decode($body);
            if ($data->RESPONSE_CODE == "000") {
                $order->add_order_note(
                        sprintf(__('Money Refunded Status: %1$s - Refund Order Id: %2$s ', 'woocommerce'), $data->PG_TXN_MESSAGE, $data->REFUND_ORDER_ID) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                );
                return true;
            } else {

                $order->add_order_note(
                        sprintf(__('Not Refunded responce code: %1$s - Refund Order Id: %2$s - Refund Fail Reason: %3$s', 'woocommerce'), $data->RESPONSE_CODE, $data->REFUND_ORDER_ID, $data->RESPONSE_MESSAGE) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                );
                 
                return false;
            }
        }

        function get_pages($title = false, $indent = true) {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title)
                $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                // show indented child pages?
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while ($has_parent) {
                        $prefix .= ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }

                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }

    }

    function woocommerce_add_cashlesso_gateway($methods) {
        $methods[] = 'WC_Cashlesso_class';

        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_cashlesso_gateway');
}

function cashlesso_hash_data($plainTextArray, $salt_key) {

    $merchant_data_string = implode('~', $plainTextArray);
    $format_Data_string = $merchant_data_string . $salt_key;
    $hashData_uf = hash('sha256', $format_Data_string);
    $hashData = strtoupper($hashData_uf);
    return $hashData;
}

function cashlesso_hash_responce_data($array, $salt_key) {

    sort($array);
    $merchant_data_string = implode('~', $array);
    $format_Data_string = $merchant_data_string . $salt_key;
    $hashData_uf = hash('sha256', $format_Data_string);
    $hashData = strtoupper($hashData_uf);
    return $hashData;
}

function cashlesso_debug($what) {
    echo '<pre>';
    print_r($what);
    echo '</pre>';
}

?>