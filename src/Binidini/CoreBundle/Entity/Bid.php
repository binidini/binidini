<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bid
 *
 * @ORM\Table(name = "bid")
 * @ORM\Entity
 */
class Bid
{
    const STATE_NEW        = 'new';

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
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;


    /**
     * @ORM\ManyToOne(targetEntity="Shipping", inversedBy="bids")
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id")
     */
    protected $shipping;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bids")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Bid state.
     *
     * @var string
     */
    protected $state = Bid::STATE_NEW;

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
     * Set comment
     *
     * @param string $comment
     * @return Bid
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set shipping
     *
     * @param \Binidini\CoreBundle\Entity\Shipping $shipping
     * @return Bid
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
     * Set user
     *
     * @param \Binidini\CoreBundle\Entity\User $user
     * @return Bid
     */
    public function setUser(\Binidini\CoreBundle\Entity\User $user = null)
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
