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
use FOS\UserBundle\Model\UserInterface;

/**
 * User aware interface.
 *
  */
interface UserAwareInterface
{
    /**
     * Get user.
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Set user.
     *
     * @param UserInterface $user
     *
     * @return self
     */
    public function setUser(UserInterface $user);
}
