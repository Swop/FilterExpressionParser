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

use Swop\FilterExpressionParser\Node\BetweenExpressionNode;
use Swop\FilterExpressionParser\Node\ComparisonExpressionNode;
use Swop\FilterExpressionParser\Node\ConditionalExpressionNode;
use Swop\FilterExpressionParser\Node\ConditionalFactorNode;
use Swop\FilterExpressionParser\Node\ConditionalPrimaryNode;
use Swop\FilterExpressionParser\Node\ConditionalTermNode;
use Swop\FilterExpressionParser\Node\FilterNode;
use Swop\FilterExpressionParser\Node\IdentificationVariableNode;
use Swop\FilterExpressionParser\Node\InExpressionNode;
use Swop\FilterExpressionParser\Node\LikeExpressionNode;
use Swop\FilterExpressionParser\Node\LiteralNode;
use Swop\FilterExpressionParser\Node\Node;
use Swop\FilterExpressionParser\Node\NullComparisonExpressionNode;
use Doctrine\ORM\Query\Expr;

class QueryExpressionBuilder
{
    public function buildQueryExpression(Node $node, QueryContext $context, $checkNot = true)
    {
        switch (true) {
            case ($node instanceof FilterNode):

                /** @var FilterNode $node */
                return $this->buildQueryExpression($node->getChild(), $context);
            case ($node instanceof ConditionalExpressionNode):
                /** @var ConditionalExpressionNode $node */
                $terms = $node->getConditionalTerms();
                if ($terms instanceof Node) {
                    return $this->buildQueryExpression($terms, $context);
                }

                $expressions = array();
                foreach ($terms as $term) {
                    $expressions[] = $this->buildQueryExpression($term, $context);
                }

                return new Expr\Orx($expressions);
            case ($node instanceof ConditionalTermNode):
                /** @var ConditionalTermNode $node */
                $factors = $node->getConditionalFactors();
                if ($factors instanceof Node) {
                    return $this->buildQueryExpression($factors, $context);
                }

                $expressions = array();
                foreach ($factors as $factor) {
                    $expressions[] = $this->buildQueryExpression($factor, $context);
                }

                return new Expr\Andx($expressions);
            case ($node instanceof ConditionalFactorNode):
                /** @var ConditionalFactorNode $node */
                if ($checkNot && $node->isNot()) {
                    return new Expr\Func('NOT', array($this->buildQueryExpression($node, $context, false)));
                }

                return $this->buildQueryExpression($node->getConditionalPrimary(), $context);
            case ($node instanceof ConditionalPrimaryNode):
                /** @var ConditionalPrimaryNode $node */
                $conditionnalExpression = (null !== $node->getSimpleConditionalExpression()
                    ? $node->getSimpleConditionalExpression()
                    : $node->getConditionalExpression());

                return $this->buildQueryExpression($conditionnalExpression, $context);
            case ($node instanceof BetweenExpressionNode):
                /** @var BetweenExpressionNode $node */
                if ($checkNot && $node->isNot()) {
                    return new Expr\Func('NOT', array($this->buildQueryExpression($node, $context, false)));
                }

                return $this->buildQueryExpression(
                    $node->getExpr1(),
                    $context
                ) . ' BETWEEN ' . $this->buildQueryExpression(
                    $node->getExpr2(),
                    $context
                ) . ' AND ' . $this->buildQueryExpression($node->getExpr3(), $context);
            case ($node instanceof ComparisonExpressionNode):

                /** @var ComparisonExpressionNode $node */
                return new Expr\Comparison(
                    $this->buildQueryExpression($node->getLeftExpr(),$context),
                    $node->getOperator(),
                    $this->buildQueryExpression($node->getRightExpr(), $context)
                );
            case ($node instanceof InExpressionNode):
                /** @var InExpressionNode $node */
                if ($checkNot && $node->isNot()) {
                    return new Expr\Func('NOT', array($this->buildQueryExpression($node, $context, false)));
                }

                $expressions = array();
                foreach ($node->getLitterals() as $literal) {
                    $expressions[] = $this->buildQueryExpression($literal, $context);
                }

                return new Expr\Func($this->buildQueryExpression($node->getIdentificationVariableNode(), $context) . ' IN', (array) $expressions);
            case ($node instanceof LikeExpressionNode):

                /** @var LikeExpressionNode $node */
                return new Expr\Comparison(
                    $this->buildQueryExpression($node->getStringExpr(), $context),
                    ($node->isNot() ? 'NOT LIKE' : 'LIKE'),
                    $this->buildQueryExpression($node->getStringPattern(), $context)
                );
            case ($node instanceof NullComparisonExpressionNode):

                /** @var NullComparisonExpressionNode $node */
                return $this->buildQueryExpression($node->getExpr(), $context)
                    . ($node->isNot() ? ' IS NOT NULL' : ' IS NULL');
            case ($node instanceof IdentificationVariableNode):

                /** @var IdentificationVariableNode $node */
                return $context->getAlias() . $node->getValue();
            case ($node instanceof LiteralNode):
                /** @var LiteralNode $node */
                $literal = $node->getValue();

                switch ($node->getType()) {
                    case LiteralNode::NUMERIC:
                        if (strpos($literal, '.') !== false || stripos($literal, 'e') !== false) {
                            $literal = floatval($literal);
                        } else {
                            $literal = intval($literal);
                        }
                        break;
                    case LiteralNode::BOOLEAN:
                        $literal = boolval($literal);
                        break;
                    case LiteralNode::STRING:
                        break;
                }

                $inputParameterName = $this->getUniqueParameterName();
                $context->addParameter($inputParameterName, $literal);

                return new Expr\Literal(':' . $inputParameterName);
            default:
                throw new \RuntimeException('Not managed node type: ' . get_class($node));
        }
    }

    /**
     * Generate a unique parameter name
     *
     * @return string
     */
    protected function getUniqueParameterName()
    {
        return uniqid('filter_');
    }
}
