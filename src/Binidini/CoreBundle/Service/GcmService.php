<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\CoreBundle\Service;

use Apple\ApnPush\Certificate\Certificate;
use Apple\ApnPush\Notification\Connection;
use Apple\ApnPush\Notification\Message;
use Apple\ApnPush\Notification\Notification;
use Apple\ApnPush\Queue\Adapter\ArrayAdapter;
use Apple\ApnPush\Queue\Queue;
use Guzzle\Service\ClientInterface;
use Psr\Log\LoggerInterface;

class GcmService
{
    private $logger;
    private $apiKey;
    private $apnsKey;

    public function __construct(LoggerInterface $logger, $apiKey, $apnsKey)
    {
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->apnsKey = $apnsKey;
    }


    /**
     * @param json array $data
     * @param string[] массив токенов $ids
     * @return int
     */
    public function send($data, $ids)
    {
        $url = 'https://android.googleapis.com/gcm/send';
        $android_ids = [];
        $ios_ids = [];

        foreach ($ids as $id) {
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
            'Authorization: key=' . $this->apiKey,
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
            $certificate = new Certificate('../app/config/aps.pem', $this->apnsKey);
            // Second argument - sandbox mode
            $connection = new Connection($certificate, false);
            $notification = new Notification($connection);
            $adapter = new ArrayAdapter();
            $queue = new Queue($adapter, $notification);
            foreach ($ios_ids as $id) {
                $message = new Message();
                $message->setBody($data['message']);
                $message->setContentAvailable(true);
                $message->setSound("default");
                $message->setDeviceToken($id);
                $message->addCustomData(["shippingId" => $data['shippingId'], 'event' => $data['event']]);
                $queue->addMessage($message);
            }
            $queue->runReceiver();
            $this->logger->info(
                'ids: ' . implode(",", $ios_ids) . ', data: ' . json_encode($data) . ', result: ' . $result
            );
        }

    }
}