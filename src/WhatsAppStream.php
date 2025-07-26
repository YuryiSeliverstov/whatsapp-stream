<?php

namespace yuryiseliverstov;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * WhatsAppStream Class
 */
class WhatsAppStream implements StreamInterface
{
    public const MEDIA_KEY_TYPES = [
        'IMAGE' => 'WhatsApp Image Keys',
        'AUDIO' => 'WhatsApp Audio Keys',
        'VIDEO' => 'WhatsApp Video Keys',
        'DOCUMENT' => 'WhatsApp Document Keys'
    ];

    private Stream $stream;

    private const
        CYPHER_ALGO = 'AES-256-CBC',
        MSG_BAD_MEDIA_TYPE = 'UNKNOWN MEDIA TYPE',
        MSG_BAD_CYPHER_DATA = 'BAD CYPHER KEY DATA';

    private string
            $cipherKey, $iv, $macKey, $refKey, $mediaKeyType;
    private bool
            $streamEncrypted = false;

    /**
     * @param string $streamFileName
     * @param string $streamKeyFileName
     * @param string $mediaType
     * @param $streamEncrypted
     * @throws \Exception
     */
    public function __construct(string $streamFileName, string $streamKeyFileName, string $mediaType, $streamEncrypted = false)
    {
        $this->setMediaKeyType($mediaType);

        $this->streamEncrypted = $streamEncrypted;

        $this->stream = new Stream(fopen($streamFileName, 'r'));
        $key = file_get_contents($streamKeyFileName);

        $expandedKey = $this->hkdf($key, 112, $this->getMediaKeyType());
        $this->iv = substr($expandedKey, 0, 16);
        $this->cipherKey = substr($expandedKey, 16, 32);
        $this->macKey = substr($expandedKey, 48, 32);
        $this->refKey = substr($expandedKey, 80);
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function toFile(string $fileName)
    {
        file_put_contents($fileName, $this->stream->getContents());
    }

    public function toBrowser()
    {

    }

    /**
     * @return array
     */
    public function getAvailableMediaTypes(): array
    {
        return array_keys(self::MEDIA_KEY_TYPES);
    }

    /**
     * @param string $mediaKeyType
     * @return void
     */
    private function setMediaKeyType(string $mediaType)
    {
        if (!isset(self::MEDIA_KEY_TYPES[$mediaType])) {
            throw new \Exception(self::MSG_BAD_MEDIA_TYPE);
        }

        $this->mediaKeyType = self::MEDIA_KEY_TYPES[$mediaType];
    }

    /**
     * @return string
     */
    public function getMediaKeyType(): string
    {
        return $this->mediaKeyType;
    }

    public function getEncryptedContents(): string
    {
        if ($this->streamEncrypted) {
            return $this->stream->getContents();
        }

        $encryptedData = openssl_encrypt($this->stream->getContents(),self::CYPHER_ALGO, $this->cipherKey, 1, $this->iv);

        $hmac = $this->hmac($this->iv . $encryptedData, $this->macKey);
        $mac = substr($hmac, 0, 10);

        if ($encryptedData !== false) {
            return $encryptedData.$mac;
        }

        return '';
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function decrypt(): string
    {
        $streamContents = $this->stream->getContents();

        $streamData = substr($streamContents, 0, -10);
        $mac = substr($streamContents, -10);

        $hmac = $this->hmac($this->iv . $streamData, $this->macKey);
        $expectedMac = substr($hmac, 0, 10);

        if (strncmp($expectedMac, $mac, 10) !== 0) {
            throw new \Exception(self::MSG_BAD_CYPHER_DATA);
        }

        return openssl_decrypt($streamData, self::CYPHER_ALGO, $this->cipherKey, 1, $this->iv) ?? '';
    }

    /**
     * @param string $key
     * @param int $length
     * @param string $info
     * @return string
     */
    private function hkdf(string $key, int $length, string $info): string
    {
        return hash_hkdf('sha256', $key, $length, $info, '');
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    private function hmac(string $data, string $key): string
    {
        return hash_hmac('sha256', $data, $key, true);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContents(): string
    {
        if ($this->streamEncrypted) {
            return $this->decrypt();
        }

        return $this->stream->getContents();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->stream->getContents();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->stream->close();
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    /**
     * @return int
     */
    public function tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    /**
     * @param $offset
     * @param $whence
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->stream->rewind();
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    /**
     * @param $string
     * @return int
     */
    public function write($string): int
    {
        return $this->stream->write($string);
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    /**
     * @param $length
     * @return string
     */
    public function read($length): string
    {
        return $this->stream->read($length);
    }

    /**
     * @param $key
     * @return array|mixed|mixed[]|null
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
