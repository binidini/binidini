<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\SearchBundle\Model;

use Hateoas\Configuration\Route;
use Hateoas\Representation\PaginatedRepresentation;
use Pagerfanta\Pagerfanta;

/**
 * @author Denis Manilo
 */
class MyPagerfantaFactory
{
    /**
     * @var string
     */
    private $pageParameterName;

    /**
     * @var string
     */
    private $limitParameterName;

    public function __construct($pageParameterName = null, $limitParameterName = null)
    {
        $this->pageParameterName  = $pageParameterName;
        $this->limitParameterName = $limitParameterName;
    }

    /**
     * @param Pagerfanta $pager  The pager
     * @param Route      $route  The collection's route
     * @param mixed      $inline Most of the time, a custom `CollectionRepresentation` instance
     *
     * @return PaginatedRepresentation
     */
    public function createRepresentation(Pagerfanta $pager, Route $route, $inline = null)
    {
        if (null === $inline) {
            $inline = new MyCollectionRepresentation($pager->getCurrentPageResults());
        }

        return new PaginatedRepresentation(
            $inline,
            $route->getName(),
            $route->getParameters(),
            $pager->getCurrentPage(),
            $pager->getMaxPerPage(),
            $pager->getNbPages(),
            $this->getPageParameterName(),
            $this->getLimitParameterName(),
            $route->isAbsolute(),
            $pager->getNbResults()
        );
    }

    /**
     * @return string
     */
    public function getPageParameterName()
    {
        return $this->pageParameterName;
    }

    /**
     * @return string
     */
    public function getLimitParameterName()
    {
        return $this->limitParameterName;
    }
}
