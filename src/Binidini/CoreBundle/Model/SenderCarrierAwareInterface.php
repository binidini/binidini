<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\Model;

use Binidini\CoreBundle\Entity\User;

interface SenderCarrierAwareInterface
{
    /**
     * Get user.
     *
     * @return User
     */
    public function getSender();

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return self
     */
    public function setSender(User $user);

    /**
     * Get user.
     *
     * @return User
     */
    public function getCarrier();

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return self
     */
    public function setCarrier(User $user);
}
