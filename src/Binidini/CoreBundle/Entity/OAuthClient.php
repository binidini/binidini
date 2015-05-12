<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * OAuthClient
 * @ORM\Entity
 * @ORM\Table(name="oauth_client")
 */
class OAuthClient extends Client
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

}
