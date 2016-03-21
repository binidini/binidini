<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Place
 *
 * @ORM\Table(name="place")
 * @ORM\Entity(repositoryClass="Binidini\CoreBundle\Entity\PlaceRepository")
 */
class Place
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var double
     *
     * @ORM\Column(name="longitude", type="decimal", precision=12, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @var double
     *
     * @ORM\Column(name="latitude", type="decimal", precision=12, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="places")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

  
    /**
     * Set address
     *
     * @param string $address
     * @return Place
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return Place
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return Place
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set user
     *
     * @param \Binidini\CoreBundle\Entity\User $user
     * @return Place
     */
    public function setUser(\Binidini\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Binidini\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
