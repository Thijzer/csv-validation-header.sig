<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Cache\CacheSettings;
use Misery\Component\Common\Cache\Local\LocalFilesystemCache;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class GetImageFromBynderAction implements OptionsInterface
{
    use OptionsTrait;
    public const NAME = 'get_image_from_bynder';

    /** @var array */
    private $options = [
        'fields' => [],
        'bynder_url' => 'https://demo.getbynder.com/api/v4/media/',
        'bynder_token' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'bynder_cookieid' => 'xxxxxxxx-xxxx-xxxx-xxxxxxxxxxxxxxxx',
        'array_location' => [ 'original' ],
    ];
    private ?LocalFilesystemCache $cachePool = null;

    public function init(): void
    {
        if (null === $this->cachePool) {
            $cachePath = $_ENV['CACHE_PATH'] ?? sys_get_temp_dir();

            $this->cachePool = new LocalFilesystemCache(
                $cachePath .
                DIRECTORY_SEPARATOR .
                'bynder_cache' .
                DIRECTORY_SEPARATOR .
                md5($this->getOption('bynder_url'))
            );
        }
    }

    public function apply(array $item): array
    {
        $this->init();
        $fields = $this->options['fields'];
        $separator = $fields['separator'] ?? ',';

        // Generate a unique cache key based on your data
        $cacheKey = md5(json_encode($item)); // You can modify this based on your data structure

        // fetch the images and cache them if not empty
        $images = $this->cachePool->retrieve($cacheKey, function (CacheSettings $settings) use ($item) {
            $settings->setTtl(3600 * 24 * 2); # 2 days
            $fields = $this->options['fields'];
            $bynder_url = $this->options['bynder_url'];
            $bynder_token = $this->options['bynder_token'];
            $bynder_cookieid = $this->options['bynder_cookieid'];
            $array_location = $this->options['array_location'];

            $bynder = [
                'url' => $bynder_url,
                'token' => $bynder_token,
                'cookieid' => $bynder_cookieid,
            ];

            return $this->sendRequest($bynder, $item, $fields, $array_location);
        });

        if (is_array($images)) {
            $item[$fields['to']] = implode($separator, $images);
        }

        return $item;
    }

    public function sendRequest(array $bynder, array $item, array $fields, array $array_location)
    {
        $images = null;

        if(!isset($fields) || !is_array($fields['from']) || !is_string($fields['to'])) {
            return null;
        }

        $fullcodeArray = [];
        foreach($fields['from'] as $field) {
            if(!isset($item[$field])) {
                continue;
            }

            // Trim /download
            $code = str_replace('/download', '', $item[$field]);
            // Trim until last /
            $code = preg_replace('/^.*\//', '', $code);

            if($code === '') {
                continue;
            }
            $fullcodeArray[] = $code;

        }

        // Get the code from the item
        $fullcode = $bynder['url'] . implode(',', $fullcodeArray);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $fullcode); // API endpoint
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        //curl_setopt($ch, CURLOPT_POST, true); // Use HTTP POST method

        // Add Authorization header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $bynder['token']
        ));

        // Add Cookie header
        $cookie = 'DEFAULTLOCALE=en_US; bynder=' . $bynder['cookieid'];
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if(curl_error($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            // Decode the response JSON string into an array
            $response_array = json_decode($response, true);

            // Check if the API returned a success status code
            if(isset($response_array['statuscode']) && $response_array['statuscode'] == '400') {
                // Close the cURL session
                curl_close($ch);
                return null;
            }

            // Get the media item from the response
            if(isset($response_array)) {
                $responseArrayWithIdCodes = [];
                foreach ($response_array as $response) {
                    $responseArrayWithIdCodes[$response['id']] = $response;
                }

                $images = [];
                foreach($fullcodeArray as $fullCode) {
                    foreach ($array_location as $location) {
                        $response = $responseArrayWithIdCodes[$fullCode] ?? [];
                        $image = null;
                        if(isset($response[$location])) {
                            $image = $response[$location];
                            //$response = $response[$location];
                        }
                    }
                    $images[] = $image;
                }
            }
        }

        // Close the cURL session
        curl_close($ch);

        return is_array($images) ? array_filter($images): $images;
    }
}
