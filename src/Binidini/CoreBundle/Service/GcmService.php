<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Binidini\CoreBundle\Service;

use Guzzle\Service\ClientInterface;
use Psr\Log\LoggerInterface;

class GcmService
{
    private $logger;
    private $apiKey;

    public function __construct(LoggerInterface $logger, $apiKey)
    {
        $this->logger = $logger;
        $this->apiKey = $apiKey;
    }


    /**
     * @param json array $data
     * @param string[] массив токенов $ids
     * @return int
     */
    public function send ($data, $ids)
    {
        $url = 'https://android.googleapis.com/gcm/send';
        $android_ids = [];
        $ios_ids = [];

        foreach($ids as $id) {
            if ($id['type'] == 'ios') {
                $ios_ids[] = $id['token'];
            } else {
                $android_ids[] = $id['token'];
            }
        }

        $ios_post = array(
            'registration_ids' => $ios_ids,
            'content_available' => true,
            'notification' => $data,
        );
        $android_post = array(
            'registration_ids' => $android_ids,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key='. $this->apiKey,
            'Content-Type: application/json'
        );

        if (count($android_ids) > 0) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($android_post));
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->logger->critical(
                    'ids: ' . implode(",", $android_ids) . ', data: ' . json_encode($data) . ', error: ' . curl_error($ch)
                );
            }

            curl_close($ch);

            $this->logger->info(
                'ids: ' . implode(",", $android_ids) . ', data: ' . json_encode($data) . ', result: ' . $result
            );
        }

        if (count($ios_ids) > 0) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ios_post));
            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->logger->critical(
                    'ids: ' . implode(",", $ios_ids) . ', data: ' . json_encode($data) . ', error: ' . curl_error($ch)
                );
            }

            curl_close($ch);

            $this->logger->info(
                'ids: ' . implode(",", $ios_ids) . ', data: ' . json_encode($data) . ', result: ' . $result
            );
        }

    }
}