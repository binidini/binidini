<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\Client;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * OAuthClient
 * @ORM\Entity
 */
class OAuthClient extends Client
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

}
