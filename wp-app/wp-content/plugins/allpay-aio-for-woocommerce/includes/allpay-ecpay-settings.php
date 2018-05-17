<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for PayPal Gateway.
 */
return array(
    'enabled' => array(
        'title' => '啟用/關閉',
        'type' => 'checkbox',
        'label' => '啟動 歐付寶/綠界全方位金流',
        'default' => 'yes'
    ),
    'title' => array(
        'title' => '標題',
        'type' => 'text',
        'description' => '客戶在結帳時所看到的標題',
        'default' => '歐付寶/綠界全方位金流',
        'desc_tip' => true,
    ),
    'pay_use' => array(
        'title' => '選擇金流',
        'type' => 'select',
        'description' => '選擇',
        'default' => '1',
        'desc_tip' => true,
        'description' => '選擇要使用歐付寶或是綠界金流',
        'options' => array(
            '1' => '歐付寶',
            '2' => '綠界'
        )
    ),
    'MerchantID' => array(
        'title' => '商店代號',
        'type' => 'text',
        'description' => '請填入您的歐付寶/綠界商店代號，測試模式請填入<code>2000132</code>',
        'default' => '',
    ),
    'hash_key' => array(
        'title' => 'Hash Key',
        'type' => 'text',
        'description' => '請填入您歐付寶/綠界廠商後台系統的Hash Key，測試模式請填入<code>5294y06JbISpM5x9</code>',
        'default' => '',
    ),
    'hash_iv' => array(
        'title' => 'Hash IV',
        'type' => 'text',
        'description' => '請填入您歐付寶/綠界廠商後台系統的Hash IV，測試模式請填入<code>v77hoKGq4kWxNNIS</code>',
        'default' => '',
    ),
    'testmode' => array(
        'title' => '測試模式',
        'type' => 'checkbox',
        'label' => '啟用測試模式',
        'default' => 'no',
        'description' => '測試模式請填入測試商店代號、Hash Key、Hash IV',
    ),
    'description' => array(
        'title' => '客戶訊息',
        'type' => 'textarea',
        'description' => '在這裡輸入下訂前，客戶會看到的訊息',
        'default' => '歐付寶/綠界全方位金流 - 儲值支付帳戶、歐付寶/綠界餘額、信用卡、WebATM線上匯款、ATM櫃員機匯款、超商代碼、超商條碼、支付寶付款、財付通付款。',
        'desc_tip' => true,
    ),
    'instruction_succ' => array(
        'title' => '成功付款訂單指示',
        'type' => 'textarea',
        'description' => '在這裡輸入下訂後成功付款，客戶會看到的訊息',
        'default' => '謝謝您，已經成功收到您的付款，我們會儘快進行出貨的動作。',
        'desc_tip' => true,
    ),
    'instruction_on_hold' => array(
        'title' => '待付款訂單指示',
        'type' => 'textarea',
        'description' => '在這裡輸入下訂後尚未付款，客戶會看到的訊息，使用在銀行虛擬帳號、超商代碼、超商條碼',
        'default' => '請注意，我們尚未收到您的付款。使用ATM繳款(虛擬帳號)、超商代碼繳款的顧客，已將繳款訊息寄至您的信箱，若您忘記或移失繳款資訊，請再重新下訂。請依提供的資訊進行付款，若您成功付款，系統會自動接收已付款訊息，我們會儘快進行出貨的動作。',
        'desc_tip' => true,
    ),
    'ExpireDate' => array(
        'title' => 'ATM繳費期限(天)',
        'type' => 'number',
        'placeholder' => 3,
        'description' => 'ATM繳款的允許繳費有效天數，最長60天，最短1天，不填則預設為3天',
        'default' => '',
        'desc_tip' => true,
        'custom_attributes' => array(
            'min' => 1,
            'max' => 60,
            'step' => 1
        ),
        'css' => 'width:60px;',
    ),
    'exclude_payments' => array(
        'title' => '排除付款方式',
        'type' => 'multiselect',
        'class' => 'chosen_select',
        'css' => 'width: 450px;',
        'default' => '',
        'description' => '使用者在歐付寶/綠界付款介面不會看見該付款方式，可留白',
        'desc_tip' => true,
        'options' => self::get_main_payment_type_args(),
        'custom_attributes' => array(
            'data-placeholder' => '選擇排除付款方式'
        ),
    ),
    'payment_method_label' => array(
        'title' => '付款標題顯示方式',
        'type' => 'select',
        'description' => '選擇在訂單列表或電子郵件時付款方式標題如何顯示，主要付款方式的標題會顯示成，例:網路ATM，細項付款方式標題會顯示成，例:台新WebATM',
        'default' => 'default',
        'desc_tip' => true,
        'options' => array(
            'default' => '預設標題',
            'main_label' => '主要付款標題',
            'detailed_label' => '細項付款標題'
        )
    ),
    'order_prefix' => array(
        'title' => '訂單編號前綴',
        'type' => 'text',
        'description' => '訂單編號的前綴，建議只使用英文，不建議使用數字，不可包含特殊符號，可留白，不可包含“TS”字元。如果有設前綴的話，那訂單編號會像是"WC123"，不建議前綴超過兩個字元以避免訂單造成問題。',
        'default' => 'WC',
    ),
    'min_amount' => array(
        'title' => '最低訂單金額',
        'type' => 'number',
        'placeholder' => wc_format_localized_price(0),
        'description' => '顧客訂單金額必需大於此金額才可使用歐付寶/綠界結帳，0 為不限制',
        'default' => '0',
        'desc_tip' => true,
        'custom_attributes' => array(
            'min' => 0,
            'step' => 1
        ),
        'css' => 'width:60px;',
    ),
    'query_allpay_trade_info' => array(
        'title' => '顯示歐付寶/綠界訂單資訊',
        'type' => 'checkbox',
        'label' => '啟用查詢訂單',
        'default' => 'no',
        'description' => '有別於在WooCommerce帳單資訊欄位顯示訂單狀態，這項功能可以讓您了解歐付寶/綠界所儲存訂單的當前資訊',
        'desc_tip' => true,
    ),
    'cron_frequency_min' => array(
        'title' => '檢查過期訂單',
        'type' => 'number',
        'placeholder' => 0,
        'description' => '檢查未付款訂單的頻率(分鐘)，像是超商條碼、代碼付款、ATM付款等有付款期限的訂單，讓它們在到期又未付款的話，自動更改訂單狀態為"取消"，0 為不啟用',
        'default' => '360',
        'desc_tip' => true,
        'custom_attributes' => array(
            'min' => 0,
            'step' => 1
        ),
        'css' => 'width:60px;',
        'autoload' => false
    ),
    'reduce_stock' => array(
        'title' => '庫存扣除',
        'type' => 'select',
        'description' => '選擇',
        'default' => 'default',
        'desc_tip' => false,
        'description' => '選擇要在下單後，在還沒收到款項前扣除庫存，或是在歐付寶/綠界收到款項之後再扣除',
        'options' => array(
            'default' => '付款前',
            'after' => '付款後'
        )
    ),
    'english' => array(
        'title' => '英文介面',
        'type' => 'select',
        'label' => '啟用英文結帳介面',
        'default' => 'no',
        'options' => array(
            'no' => '不(用中文介面)',
            'yes' => '用英文介面',
            'wpml' => '根據WPML設定的語系來決定'
        ),
        'description' => '進入歐付寶/綠界結帳頁，顯示的語系會是英文，<strong>注意:若開啟此選項，歐付寶/綠界只會提供信用卡刷卡結帳</strong>。',
    ),
    'admin_mode' => array(
        'title' => '管理員模式',
        'type' => 'checkbox',
        'label' => '啟用管理員測試模式',
        'default' => 'no',
        'description' => '開啟這項選項，可以只讓管理員看到歐付寶/綠界結帳方式',
        'desc_tip' => true,
    ),
    'debug' => array(
        'title' => '除錯紀錄',
        'type' => 'checkbox',
        'label' => '啟用除錯紀錄',
        'default' => 'no',
        'description' => sprintf('紀錄除錯/回應訊息，檔案存放於<code>%s</code>', wc_get_log_file_path('innovext_allpay_aio')),
    )
);
