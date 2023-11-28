<?php
// Retrieve the encrypted text from the POST data.
$encryption = $_POST['decrypttxt'];
// Specify the ciphering method to be used for decryption.
$ciphering = "AES-256-CTR";
// Determine the IV length for the specified ciphering method.
$iv_length = openssl_cipher_iv_length($ciphering);
// Define options for the openssl_decrypt function. '0' typically means no options.
$options   = 0;
// Define the Initialization Vector (IV) for decryption.
$decryption_iv = '1234567891011121';
// Retrieve the decryption key from the system settings.
// This key should be the same as the one used during the encryption process.
$decryption_key = $module->getSystemSetting("encrypt-key");
// Perform the decryption using the specified parameters.
$decryption = openssl_decrypt($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
// Output the results - both the submitted (encrypted) text and the decrypted IP address.
// The output is wrapped in a div with styling classes for visibility.
echo"<div class='yellow redcap-updates'><span>Submitted Text: ".$encryption."</span>&emsp;<span>Decrypted IP Address: ".$decryption."</span></div>";