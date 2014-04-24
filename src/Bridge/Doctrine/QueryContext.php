<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Bridge\Doctrine;

class QueryContext
{
    /** @var string */
    private $alias;
    /** @var array */
    private $parameters;

    /**
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->alias      = $alias;
        $this->parameters = array();
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
