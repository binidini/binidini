<?php

namespace Binidini\CoreBundle\Entity;

use Binidini\CoreBundle\Model\UserAwareInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name = "review")
 *
 */
class Review implements UserAwareInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_to_id", referencedColumnName="id", nullable=false)
     */
    private $userTo;

    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer", nullable=false, options={"default" = 0})
     * @Assert\Range(min=1, max=5)
     */
    private $rating;

    /**
     * @var Shipping
     *
     * @ORM\ManyToOne(targetEntity="Shipping", inversedBy="messages")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id", nullable=false)
     */
    protected $shipping;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
     * Set text
     *
     * @param string $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Shipping
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set shipping
     *
     * @param \Binidini\CoreBundle\Entity\Shipping $shipping
     * @return Message
     */
    public function setShipping(\Binidini\CoreBundle\Entity\Shipping $shipping = null)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * Get shipping
     *
     * @return \Binidini\CoreBundle\Entity\Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }


    /**
     * Set rating
     *
     * @param integer $rating
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set userTo
     *
     * @param \Binidini\CoreBundle\Entity\User $userTo
     * @return Review
     */
    public function setUserTo(\Binidini\CoreBundle\Entity\User $userTo)
    {
        $this->userTo = $userTo;
        return $this;
    }

    /**
     * Get userTo
     *
     * @return \Binidini\CoreBundle\Entity\User
     */
    public function getUserTo()
    {
        return $this->userTo;
    }
}
