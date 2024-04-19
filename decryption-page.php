<?php
/*// Set the initial directory to the current file's directory.
$dir = dirname(__FILE__);
// Traverse up the directory tree to find the 'redcap_connect.php' file.
while (!file_exists($dir . '/redcap_connect.php') && strlen($dir) > 3) {
    $dir = dirname($dir);
}
// Include 'redcap_connect.php' if found, or exit the script if not found.
if (file_exists($dir . '/redcap_connect.php')) {
    require_once $dir . '/redcap_connect.php';
} else {
    exit;
}*/
// Get the URL for the 'decryptajax.php' file.
$ajaxurl = $module->getUrl('decryptajax.php');
?>
    <title>IP-ENCRYPT Action Tag Decryption Page</title>
    <link rel="shortcut icon" href="<?php echo APP_PATH_WEBROOT; ?>Resources/images/favicon.ico">
    <link rel="stylesheet" href="<?php echo APP_PATH_WEBROOT; ?>Resources/css/style.css">
    <!-- Create a form for users to input encrypted text -->
<div class="well">
        <table>
            <caption>@IP-ENCRYPT Action Tag Decryption Tool</caption>
            <tr>
                <td>
                    <table style="border: 1px solid black;">
                        <tr>
                            <td style="padding-right: 30px;"><b><label>Encrypted IP Address:</label></b></td>
                            <td><input name="decrypttxt" id="decrypttxt" type="text" placeholder="Encrypted Text"></td>
                            <td><input id="clickMe" type="button" value="Decrypt" onclick="decrypt(document.getElementById('decrypttxt').value);" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="ftitle">
            <td>
                <table>
                    <tr class="ftitle">
                        <td colspan="3"><b>IMPORTANT NOTE:</b> Decrypting an IP Address may constitute a breach of confidentiality and may require approval by the appropriate entities at your institution (IRB, Legal...).</td>
                    </tr>
                    <tr class="ftitle">
                        <td><b>REMINDER:</b> If the submitter is using a proxy server / VPN, @IP-ENCRYPT will capture the IP address of that server.</td>
                    </tr>
                </table>
            </td>
            </tr>
        </table>
</div>
    <!-- Table to display decrypted results -->
        <table id="ip_decrypt_table">

        </table>
    <!-- Include necessary JavaScript libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function decrypt (iptext) {
        // Disable the decrypt button to prevent multiple submissions.
        document.getElementById("clickMe").disabled = true;
        // AJAX call to 'decryptajax.php', sending the encrypted text.
        $.ajax({
            type: "POST",
            url: "<?php echo $ajaxurl; ?>",

            data: `decrypttxt=${iptext}&redcap_csrf_token=<?=$module->getCSRFToken()?>`,
            contentType: 'application/x-www-form-urlencoded',
            success: function(data){
                // Insert the decrypted data into the table.
                var table = document.getElementById("ip_decrypt_table");
                var row = table.insertRow(0);
                var cell1 = row.insertCell(0);
                cell1.innerHTML = data;
                // Re-enable the decrypt button and clear the input field.
                document.getElementById("clickMe").disabled = false;
                document.getElementById("decrypttxt").value = "";
            }
        });    }
</script>
    <?php


