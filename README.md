# PHP Library for WhatsApp Media Streams to Encrypt or Decrypt
## Requires
1. PHP Version 7.4.33+
2. PHP Extension OpenSSL

## Install:
```
composer require yuryiseliverstov\whatsapp-stream
```
## Usage:
```
<?php

require 'vendor/autoload.php';

use yuryiseliverstov\WhatsAppStream;

/**
 * Encryption
 */
$imageStream = new WhatsAppStream('samples/IMAGE.encrypted', 'samples/IMAGE.key', 'IMAGE',false);
file_put_contents('output/IMAGE.encrypted', $imageStream->getEncryptedContents());

/**
 * Decryption Audio
 */
$audioStream = new WhatsAppStream('samples/AUDIO.encrypted', 'samples/AUDIO.key', 'AUDIO',true);
file_put_contents('output/AUDIO.original', $audioStream->getContents());

/**
 * Decryption Video
 */
$videoStream = new WhatsAppStream('samples/VIDEO.encrypted', 'samples/VIDEO.key', 'VIDEO',true);
file_put_contents('output/VIDEO.original', $videoStream->getContents());
?>
```
