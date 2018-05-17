<?php
header('Content-Type:text/html; charset=utf-8');

session_start();
$resArray = $_SESSION['reshash'];
?>

<html>
<head>
    <title>错误提示</title>

</head>

<body alink=#0000FF vlink=#0000FF>

<center>

    <table width="400">
        <tr>
            <td colspan="4" class="header">The PayPal API has returned an error!</td>
        </tr>

        <?php //it will print if any URL errors
        if (isset($_SESSION['curl_error_no'])) {
        $errorCode = $_SESSION['curl_error_no'];
        $errorMessage = $_SESSION['curl_error_msg'];
        session_unset();
        ?>


        <tr>
            <td>错误编码:</td>
            <td><?php echo $errorCode; ?></td>
        </tr>
        <tr>
            <td>错误信息:</td>
            <td><?php echo $errorMessage; ?></td>
        </tr>

</center>
</table>
<?php
} else {
    /* If there is no URL Errors, Construct the HTML page with
       Response Error parameters.
       */
    ?>

    <center>
        <font size=2 color=black face=Verdana><b></b></font>
        <br><br>

        <b> 错误提示</b><br><br>

        <table width=400>
            <?php

            foreach ($resArray as $key => $value) {
                echo "<tr><td> $key:</td><td>$value</td>";
            }
            ?>
        </table>
    </center>

<?php
}
// end else
?>
</center>
</table>
<br>
<!--<a class="home"  id="CallsLink" href="inc.paypal.php"><font color=blue><B>Home<B><font></a>
--></body>
</html>

