<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table(name = "payment")
 * @ORM\Entity
 */
class Payment
{
    const TYPE_INSURANCE = 'insurance';
    const TYPE_GUARANTEE = 'guarantee';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /*
     * @var integer
     *
     * @ORM\Column(name="credit_debit_flag", columnDefinition="ENUM(-1, 1))
     */
    private $flagCreditDebit;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=64)
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(name="ref", type="string", length=127)
     */
    private $ref;
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="payments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * Set amount
     *
     * @param integer $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Payment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return Payment
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set ref
     *
     * @param string $ref
     * @return Payment
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return string 
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set user
     *
     * @param \Binidini\CoreBundle\Entity\User $user
     * @return Payment
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
