<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Node;

class InExpressionNode extends Node
{
    /** @var bool */
    private $not = false;
    /** @var IdentificationVariableNode */
    private $identificationVariableNode;
    /** @var array */
    private $litterals = array();

    public function __construct(IdentificationVariableNode $identificationVariableNode)
    {
        $this->identificationVariableNode = $identificationVariableNode;
    }

    /**
     * @param bool $not
     */
    public function setNot($not)
    {
        $this->not = $not;
    }

    public function setLiterals(array $literals)
    {
        $this->litterals = $literals;
    }

    /**
     * Gets the identificationVariableNode attribute
     *
     * @return \Swop\FilterExpressionParser\Node\IdentificationVariableNode
     */
    public function getIdentificationVariableNode()
    {
        return $this->identificationVariableNode;
    }

    /**
     * Gets the litterals attribute
     *
     * @return array
     */
    public function getLitterals()
    {
        return $this->litterals;
    }

    /**
     * Gets the not attribute
     *
     * @return boolean
     */
    public function isNot()
    {
        return $this->not;
    }
}
