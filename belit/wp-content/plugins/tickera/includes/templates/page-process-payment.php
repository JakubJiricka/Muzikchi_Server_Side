<?php

if(is_admin()){
    return;
}

if (isset($_POST['action']) && $_POST['action'] == 'heartbeat') {
    return;
}

global $tc, $tc_gateway_plugins, $wp;

if (!session_id()) {
    session_start();
}

$cart_contents = $tc->get_cart_cookie();

$cart_total = isset($_SESSION['tc_cart_total']) ? $_SESSION['tc_cart_total'] : null;

if (is_null($cart_total)) {
    $tc->checkout_error = true;
    $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
    wp_redirect($tc->get_payment_slug(true));
    tc_js_redirect($tc->get_payment_slug(true));
    exit;
}

if (!isset($_REQUEST['tc_choose_gateway'])) {//set free orders as gateway since none is selected
    if ($cart_total > 0) {//do it only if total is more than zero
        $tc->checkout_error = true;
        $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
        wp_redirect($tc->get_payment_slug(true));
        tc_js_redirect($tc->get_payment_slug(true));
        exit;
    } else {//set free orders since total is exactly zero
        if (isset($_SESSION['tc_cart_total'])) {
            $_SESSION['tc_gateway_error'] = '';
            $tc->checkout_error = false;
            $payment_class_name = $tc_gateway_plugins[apply_filters('tc_not_selected_default_gateway', 'free_orders')][0];
        } else {
            $tc->checkout_error = true;
            $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
            wp_redirect($tc->get_payment_slug(true));
            tc_js_redirect($tc->get_payment_slug(true));
            exit;
        }
    }
} else {
    if (($cart_total > 0 && $_REQUEST['tc_choose_gateway'] !== 'free_orders') || ($cart_total == 0 && $_REQUEST['tc_choose_gateway'] == 'free_orders')) {
        $_SESSION['tc_gateway_error'] = '';
        $tc->checkout_error = false;
        $payment_class_name = $tc_gateway_plugins[$_REQUEST['tc_choose_gateway']][0];
    } else {
        $tc->checkout_error = true;
        $_SESSION['tc_gateway_error'] = sprintf(__('Sorry, something went wrong. %sPlease try again%s.', 'tc'), '<a href="' . $tc->get_cart_slug(true) . '">', '</a>');
        wp_redirect($tc->get_payment_slug(true));
        tc_js_redirect($tc->get_payment_slug(true));
        exit;
    }
}

$payment_gateway = new $payment_class_name;

if (!empty($cart_contents) && count($cart_contents) > 0) {

    if ($tc->checkout_error == false) {
        $payment_gateway->process_payment($cart_contents);
        exit;
    } else {
        wp_redirect($this->get_payment_slug(true));
        tc_js_redirect($this->get_payment_slug(true));
        exit;
    }
} else {//The cart is empty and this page shouldn't be reached
    wp_redirect($this->get_payment_slug(true));
    tc_js_redirect($this->get_payment_slug(true));
    exit;
}
?>