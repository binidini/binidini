<?php

namespace Binidini\CoreBundle\Service;

interface SendSmsInterface
{
    /*
     * @param string $phone
     * @param string $msg
     *
     * @return integer
     */
    public function send($phone, $msg);
}
