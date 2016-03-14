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

/**
 * User aware interface.
 *
  */
interface UserAwareInterface
{
    /**
     * Get user.
     *
     * @return User
     */
    public function getUser();

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user);
}
