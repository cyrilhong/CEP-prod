jQuery(document).ready(function ($) {
    $('.wvm-card-details').hide();
    $('.wvm_payment_method').on('change', function () {
        var method = $(this).val();
        if (method === 'card') {
            $('.wvm-card-details').show();
        } else {
            $('.wvm-card-details').hide();
        }
    });

    $('[data-numeric]').payment('restrictNumeric');
    $('.cc-number').payment('formatCardNumber');
    $('.cc-exp').payment('formatCardExpiry');
    $('.cc-cvc').payment('formatCardCVC');
    var ajax_url = vendor_plan_param.ajax_url;
    $.fn.toggleInputError = function (erred) {
        this.parent('.form-field').toggleClass('has-error', erred);
        return this;
    };

    $('#paypal_submit_btn').on('click', function (event) {
        var hasError = false;
        var method = $('.wvm_payment_method:checked').val();
        if (method == 'card') {
            var errorMessage = [];
            if ($("#cc-card-holder").val() == "") {
                hasError = true;
                errorMessage.push("Please enter card holder name");
            }
            if ($("#wcmp_cat_card_type").val() == "") {
                hasError = true;
                errorMessage.push("Please select card type");
            }
            var cardType = $.payment.cardType($('.cc-number').val());

            if (!$.payment.validateCardNumber($('.cc-number').val())) {
                hasError = true;
                errorMessage.push("Invalid Card number");
            }
            if (!$.payment.validateCardExpiry($('.cc-exp').payment('cardExpiryVal'))) {
                hasError = true;
                errorMessage.push("Enter valid expire date");
            }
            if (!$.payment.validateCardCVC($('.cc-cvc').val(), cardType)) {
                hasError = true;
                errorMessage.push("Invalid CVV");
            }
            if (hasError) {
                $('.wvn-payment-error').html('');
                $.each(errorMessage, function (index, error) {
                    $('.wvn-payment-error').append('<li>' + error + '</li>');
                });
                event.preventDefault();
            }
        }
    });


    $("#credit_card_submit_btn").click(function (e) {
        if ($("#cc-card-holder").val() == "") {
            var cardholder_error = true;
        } else {
            var cardholder_error = false;
        }
        $("#cc-card-holder").parent('.form-field').toggleClass('has-error', cardholder_error);
        if ($("#wcmp_cat_card_type").val() == "") {
            var cardtype_error = true;
        } else {
            var cardtype_error = false;
        }
        $("#wcmp_cat_card_type").parent('.form-field').toggleClass('has-error', cardtype_error);
        var cardType = $.payment.cardType($('.cc-number').val());
        $('.cc-number').toggleInputError(!$.payment.validateCardNumber($('.cc-number').val()));
        $('.cc-exp').toggleInputError(!$.payment.validateCardExpiry($('.cc-exp').payment('cardExpiryVal')));
        $('.cc-cvc').toggleInputError(!$.payment.validateCardCVC($('.cc-cvc').val(), cardType));
        $(".validation_message").html('');
        /*}*/
        var wcmp_checkout_Email = $("#reg_email").val();
        var wcmp_checkout_first_name = $("#wcmp_checkout_first_name").val();
        var wcmp_checkout_last_name = $("#wcmp_checkout_last_name").val();
        var wcmp_checkout_company = $("#wcmp_checkout_company").val();
        var wcmp_checkout_address1 = $("#wcmp_checkout_address1").val();
        var wcmp_checkout_address2 = $("#wcmp_checkout_address2").val();
        var wcmp_checkout_country = $("#wcmp_checkout_country").val();
        var wcmp_checkout_city = $("#wcmp_checkout_city").val();
        var wcmp_checkout_zip = $("#wcmp_checkout_zip").val();
        var wcmp_checkout_phone = $("#wcmp_checkout_phone").val();
        var wcmp_checkout_state = $("#wcmp_checkout_state").val();
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if ((!emailReg.test(wcmp_checkout_Email)) || (wcmp_checkout_Email == '')) {
            var email_error = true;
        } else {
            var email_error = false;
        }
        $("#reg_email").parent('.form-field').toggleClass('has-error', email_error);
        if (wcmp_checkout_first_name == '') {
            var first_name_error = true;
        } else {
            var first_name_error = false;
        }
        $("#wcmp_checkout_first_name").parent('.form-field').toggleClass('has-error', first_name_error);

        if (wcmp_checkout_last_name == '') {
            var last_name_error = true;
        } else {
            var last_name_error = false;
        }
        $("#wcmp_checkout_last_name").parent('.form-field').toggleClass('has-error', last_name_error);

        if (wcmp_checkout_address1 == '') {
            var address1_error = true;
        } else {
            var address1_error = false;
        }
        $("#wcmp_checkout_address1").parent('.form-field').toggleClass('has-error', address1_error);

        if (wcmp_checkout_country == '') {
            var country_error = true;
        } else {
            var country_error = false;
        }
        $("#wcmp_checkout_country").parent('.form-field').toggleClass('has-error', country_error);

        if (wcmp_checkout_state == '') {
            var state_error = true;
        } else {
            var state_error = false;
        }
        $("#wcmp_checkout_state").parent('.form-field').toggleClass('has-error', state_error);

        if (wcmp_checkout_city == '') {
            var city_error = true;
        } else {
            var city_error = false;
        }
        $("#wcmp_checkout_city").parent('.form-field').toggleClass('has-error', city_error);

        if (wcmp_checkout_zip == '') {
            var zip_error = true;
        } else {
            var zip_error = false;
        }
        $("#wcmp_checkout_zip").parent('.form-field').toggleClass('has-error', zip_error);

        if (wcmp_checkout_phone == '') {
            var phone_error = true;
        } else {
            var phone_error = false;
        }
        $("#wcmp_checkout_phone").parent('.form-field').toggleClass('has-error', phone_error);

        if ($("#credit_card_submit_btn").hasClass("old-subscriber")) {
            var action = 'paypal_api_call_update_process';
        } else if ($("#credit_card_submit_btn").hasClass("new-subscriber")) {
            var action = 'paypal_api_call_form_process';
        } else {
            return;
        }

        if ($('.has-error').length > 0) {
            $('.has-error').each(function (e) {
                var message = $(this).attr('data-message');
                var prevmsg = $(".validation_message").html();
                var newmsg = prevmsg + '<br/>' + message;
                $(".validation_message").html(newmsg);
            });
        } else {
            var wait_msg = "<span>Please wait... While your transaction being proceed.</span><br><span>Don't press back or refresh button.</span>"
            $(".validation_message").html(wait_msg);
            if ($("#credit_card_submit_btn").hasClass("new-subscriber")) {
                var c_holder = $('#cc-card-holder').val();
                var c_number = $('#wcmp_cat_card_number').val();
                var c_type = $('#wcmp_cat_card_type').val();
                var c_month_year = $('#wcmp_cat_exp_month_year').val();
                var c_cvv = $('#wcmp_cat_card_cvc').val();
            } else {
                var c_holder = '';
                var c_number = '';
                var c_type = '';
                var c_month_year = '';
                var c_cvv = '';
            }
            var user_type_for_registration = $("#user_type_for_registration").val();
            var data = {
                action: action,
                c_holder: c_holder,
                c_number: c_number,
                c_type: c_type,
                c_month_year: c_month_year,
                c_cvv: c_cvv,
                user_type_for_registration: user_type_for_registration,
                wcmp_checkout_Email: wcmp_checkout_Email,
                wcmp_checkout_first_name: wcmp_checkout_first_name,
                wcmp_checkout_last_name: wcmp_checkout_last_name,
                wcmp_checkout_company: wcmp_checkout_company,
                wcmp_checkout_address1: wcmp_checkout_address1,
                wcmp_checkout_address2: wcmp_checkout_address2,
                wcmp_checkout_country: wcmp_checkout_country,
                wcmp_checkout_state: wcmp_checkout_state,
                wcmp_checkout_city: wcmp_checkout_city,
                wcmp_checkout_zip: wcmp_checkout_zip,
                wcmp_checkout_phone: wcmp_checkout_phone
            };
            $.post(ajax_url, data, function (response) {
                $(".validation_message").html(response);
            });
        }
    });
    /**Country change state call*/
    $("#wcmp_checkout_country").change(function (e) {
        var data = {
            action: 'state_for_country_ajax',
            country_code: $(this).val()
        }
        $.post(ajax_url, data, function (response) {
            $(".wcmp_checkout_state_parent").html(response);
        });
    });
    $("#reg_email").focusout(function (e) {
        var data = {
            action: 'check_email_exits_or_not',
            emailaddress: $(this).val()
        }
        $.post(ajax_url, data, function (response) {
            if (response == 'exits') {
                $("#reg_email").parent('.form-field').toggleClass('has-error', true);
                $(".validation_message").html('Email already registed with please enter other email');
            } else {
                $("#reg_email").parent('.form-field').toggleClass('has-error', false);
                $(".validation_message").html('');
            }
        });

    });

    $("#cancel_subscription").click(function () {
        var data = {
            action: 'paypal_api_call_cancel_process',
            subscription: 'cancel'
        }
        $.post(ajax_url, data, function (response) {
            $(".validation_message").html(response);
        });
    });

});
