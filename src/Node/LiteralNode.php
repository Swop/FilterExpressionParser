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

class LiteralNode extends Node
{
    const STRING  = 1;
    const BOOLEAN = 2;
    const NUMERIC = 3;

    /** @var int */
    private $type;
    /** @var string */
    private $value;

    /**
     * @param int    $type
     * @param string $value
     */
    public function __construct($type, $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    /**
     * Gets the type attribute
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the value attribute
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
