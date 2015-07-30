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

        $post = array(
            'registration_ids' => $ids,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key='. $this->apiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->logger->critical('ids: ' . implode(",", $ids) .', data: '. json_encode($data) .', error: ' . curl_error($ch));
        }

        curl_close($ch);

        $this->logger->info('ids: ' . implode(",", $ids) .', data: '. json_encode($data) .', result: ' . $result);

    }
}