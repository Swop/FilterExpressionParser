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

class ConditionalExpressionNode extends Node
{
    /** @var array|Node */
    private $conditionalTerms;

    public function __construct($conditionalTerms)
    {
        $this->conditionalTerms = $conditionalTerms;
    }

    /**
     * Gets the conditionalTerms attribute
     *
     * @return array|\Swop\FilterExpressionParser\Node\Node
     */
    public function getConditionalTerms()
    {
        return $this->conditionalTerms;
    }
}
