# PHP Library for WhatsApp Media Streams to Encrypt or Decrypt
## Requires
1. PHP Version 7.4.33+
2. PHP Extension OpenSSL

## Install:
```
composer require yuryiseliverstov\whatsapp-stream
```
## Usage:
Create .php file in root directory, as well create "WhatsAppStreamOutput" directory with 777 rights if you use Linux Operating System.
```
<?php

require 'vendor/autoload.php';


use yuryiseliverstov\WhatsAppStream\WhatsAppStream;

$samplesDir = 'vendor/yuryiseliverstov/whatsapp-stream/samples/';
$outputDir = 'WhatsAppStreamOutput';

if (!is_dir($outputDir))
	mkdir($outputDir);

/**
 * Encryption
 */
$imageStream = new WhatsAppStream($samplesDir.'IMAGE.encrypted', $samplesDir.'IMAGE.key', 'IMAGE',false);
file_put_contents($outputDir.'/IMAGE.encrypted', $imageStream->getEncryptedContents());

/**
 * Decryption
 */
$audioStream = new WhatsAppStream($samplesDir.'AUDIO.encrypted', $samplesDir.'AUDIO.key', 'AUDIO',true);
file_put_contents($outputDir.'/AUDIO.original', $audioStream->getContents());

echo 'Job Done!';

?>
```
