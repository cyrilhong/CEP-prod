<script src="http://lib.sinaapp.com/js/jquery/1.5.2/jquery.min.js"></script>
<script>
    $(function() {

        $.ajax({
            url: 'api_paypal/cls.paypal_service.php',
            type: 'POST',
            dataType: 'JSON',
            data: {'methodName': 'SetExpressCheckout', 'nvpStr': '<?php echo $nvpStr;?>'},
            success: function(data) {
                redirect(data);
            }

        });

    });


    function redirect($resArray) {
        var $ack = $resArray['ACK'].toUpperCase();

        var PAYPAL_URL = '<?php echo $paypal_config['PAYPAL_URL'];?>';

        if ($ack == "SUCCESS") {
            // Redirect to paypal.com here
            //$token = urldecode($resArray["TOKEN"]);
            var $token = $resArray["TOKEN"];

            var $payPalURL = PAYPAL_URL + $token;

            window.location.href = $payPalURL;
        } else {
            window.location.href = 'tpl.tip.php?info=timeout&pms=sudo';
            //alert('连接超时');
        }

    }


</script>
