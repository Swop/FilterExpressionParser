<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swop\FilterExpressionParser\Parser;

use Swop\FilterExpressionParser\Exception\ExpressionException;
use Swop\FilterExpressionParser\Lexer\Lexer;
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

class Parser
{
    /**@var Lexer */
    private $lexer;

    /** @var string */
    private $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
        $this->lexer = new Lexer($expression);
    }

    /**
     * Parses and builds AST for the given Query.
     *
     * @return Node
     */
    public function getAST()
    {
        // Parse & build AST
        $AST = $this->filterLanguage();

        return $AST;
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     *
     * If they match, updates the lookahead token; otherwise raises a syntax
     * error.
     *
     * @param int $token The token type.
     *
     * @return void
     *
     * @throws ExpressionException If the tokens don't match.
     */
    public function match($token)
    {
        $lookaheadType = $this->lexer->lookahead['type'];

        // short-circuit on first condition, usually types match
        if ($lookaheadType !== $token && $token !== Lexer::T_IDENTIFIER && $lookaheadType <= Lexer::T_IDENTIFIER) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        $this->lexer->moveNext();
    }

    /**
     * Frees this parser, enabling it to be reused.
     *
     * @param boolean $deep     Whether to clean peek and reset errors.
     * @param integer $position Position to reset.
     *
     * @return void
     */
    public function free($deep = false, $position = 0)
    {
        // WARNING! Use this method with care. It resets the scanner!
        $this->lexer->resetPosition($position);

        // Deep = true cleans peek and also any previously defined errors
        if ($deep) {
            $this->lexer->resetPeek();
        }

        $this->lexer->token = null;
        $this->lexer->lookahead = null;
    }

    /**
     * Generates a new syntax error.
     *
     * @param string      $expected Expected string.
     * @param array|null  $token    Got token.
     *
     * @return void
     *
     * @throws ExpressionException
     */
    public function syntaxError($expected = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $tokenPos = (isset($token['position'])) ? $token['position'] : '-1';

        $message  = "line 0, col {$tokenPos}: Error: ";
        $message .= ($expected !== '') ? "Expected {$expected}, got " : 'Unexpected ';
        $message .= ($this->lexer->lookahead === null) ? 'end of string.' : "'{$token['value']}'";

        throw ExpressionException::syntaxError($message, ExpressionException::expressionError($this->expression));
    }

    /**
     * Generates a new semantical error.
     *
     * @param string     $message Optional message.
     * @param array|null $token   Optional token.
     *
     * @return void
     *
     * @throws ExpressionException
     */
    public function semanticalError($message = '', $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        // Minimum exposed chars ahead of token
        $distance = 12;

        // Find a position of a final word to display in error string
        $expression    = $this->expression;
        $length = strlen($expression);
        $pos    = $token['position'] + $distance;
        $pos    = strpos($expression, ' ', ($length > $pos) ? $pos : $length);
        $length = ($pos !== false) ? $pos - $token['position'] : $distance;

        $tokenPos = (isset($token['position']) && $token['position'] > 0) ? $token['position'] : '-1';
        $tokenStr = substr($expression, $token['position'], $length);

        // Building informative message
        $message = 'line 0, col ' . $tokenPos . " near '" . $tokenStr . "': Error: " . $message;

        throw ExpressionException::semanticalError($message, ExpressionException::expressionError($this->expression));
    }

    /**
     * Peeks beyond the matched closing parenthesis and returns the first token after that one.
     *
     * @param boolean $resetPeek Reset peek after finding the closing parenthesis.
     *
     * @return array
     */
    private function peekBeyondClosingParenthesis($resetPeek = true)
    {
        $token = $this->lexer->peek();
        $numUnmatched = 1;

        while ($numUnmatched > 0 && $token !== null) {
            switch ($token['type']) {
                case Lexer::T_OPEN_PARENTHESIS:
                    ++$numUnmatched;
                    break;

                case Lexer::T_CLOSE_PARENTHESIS:
                    --$numUnmatched;
                    break;

                default:
                    // Do nothing
            }

            $token = $this->lexer->peek();
        }

        if ($resetPeek) {
            $this->lexer->resetPeek();
        }

        return $token;
    }

    /**
     * filterLanguage ::= filterNode
     *
     * @return FilterNode
     */
    public function filterLanguage()
    {
        $this->lexer->moveNext();

        $node = $this->filterNode();

        // Check for end of string
        if ($this->lexer->lookahead !== null) {
            $this->syntaxError('end of string');
        }

        return $node;
    }

    /**
     * filterNode ::= conditionalExpression
     *
     * @return FilterNode
     */
    public function filterNode()
    {
        $filterNode = new FilterNode($this->conditionalExpression());

        return $filterNode;
    }

    /**
     * conditionalExpression ::= conditionalTerm {"OR" conditionalTerm}*
     *
     * @return ConditionalExpressionNode
     */
    public function conditionalExpression()
    {
        $conditionalTerms = array();
        $conditionalTerms[] = $this->conditionalTerm();

        while ($this->lexer->isNextToken(Lexer::T_OR)) {
            $this->match(Lexer::T_OR);

            $conditionalTerms[] = $this->conditionalTerm();
        }

        // Phase 1 AST optimization: Prevent AST\ConditionalExpression
        // if only one AST\ConditionalTerm is defined
        if (count($conditionalTerms) == 1) {
            return $conditionalTerms[0];
        }

        return new ConditionalExpressionNode($conditionalTerms);
    }

    /**
     * conditionalTerm ::= conditionalFactor {"AND" conditionalFactor}*
     *
     * @return ConditionalTermNode
     */
    public function conditionalTerm()
    {
        $conditionalFactors = array();
        $conditionalFactors[] = $this->conditionalFactor();

        while ($this->lexer->isNextToken(Lexer::T_AND)) {
            $this->match(Lexer::T_AND);

            $conditionalFactors[] = $this->conditionalFactor();
        }

        // Phase 1 AST optimization: Prevent AST\ConditionalTerm
        // if only one AST\ConditionalFactor is defined
        if (count($conditionalFactors) == 1) {
            return $conditionalFactors[0];
        }

        return new ConditionalTermNode($conditionalFactors);
    }

    /**
     * conditionalFactor ::= ["NOT"] conditionalPrimary
     *
     * @return ConditionalFactorNode
     */
    public function conditionalFactor()
    {
        $not = false;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);

            $not = true;
        }

        $conditionalPrimary = $this->conditionalPrimary();

        // Phase 1 AST optimization: Prevent AST\ConditionalFactor
        // if only one AST\ConditionalPrimary is defined
        if ( ! $not) {
            return $conditionalPrimary;
        }

        $conditionalFactor = new ConditionalFactorNode($conditionalPrimary);
        $conditionalFactor->setNot($not);

        return $conditionalFactor;
    }

    /**
     * conditionalPrimary ::= simpleConditionalExpression | "(" conditionalExpression ")"
     *
     * @return ConditionalPrimaryNode
     */
    public function conditionalPrimary()
    {
        $condPrimary = new ConditionalPrimaryNode();

        if ( ! $this->lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $condPrimary->setSimpleConditionalExpression($this->simpleConditionalExpression());

            return $condPrimary;
        }

        // Peek beyond the matching closing parenthesis ')'
        $peek = $this->peekBeyondClosingParenthesis();

        if (in_array($peek['value'], array("=",  "<", "<=", "<>", ">", ">=", "!=")) ||
            in_array($peek['type'], array(Lexer::T_NOT, Lexer::T_BETWEEN, Lexer::T_LIKE, Lexer::T_IN, Lexer::T_IS))) {
            $condPrimary->setSimpleConditionalExpression($this->simpleConditionalExpression());

            return $condPrimary;
        }

        $this->match(Lexer::T_OPEN_PARENTHESIS);
        $condPrimary->setConditionalExpression($this->conditionalExpression());
        $this->match(Lexer::T_CLOSE_PARENTHESIS);

        return $condPrimary;
    }

    /**
     * simpleConditionalExpression ::=
     *      comparisonExpression | betweenExpression | likeExpression |
     *      inExpression | nullComparisonExpression
     *
     * @return BetweenExpressionNode|LikeExpressionNode|InExpressionNode|NullComparisonExpressionNode|ComparisonExpressionNode|Node
     */
    public function simpleConditionalExpression()
    {
        $token      = $this->lexer->lookahead;
        $lookahead  = $token;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $token = $this->lexer->glimpse();
        }

        if ($token['type'] === Lexer::T_IDENTIFIER) {
            // Peek beyond the matching closing parenthesis.
            $beyond = $this->lexer->peek();

            // Peek beyond the PathExpression or InputParameter.
            $token = $beyond;

            while ($token['value'] === '.') {
                $this->lexer->peek();

                $token = $this->lexer->peek();
            }

            // Also peek beyond a NOT if there is one.
            if ($token['type'] === Lexer::T_NOT) {
                $token = $this->lexer->peek();
            }

            // We need to go even further in case of IS (differentiate between NULL and EMPTY)
            $lookahead = $this->lexer->peek();

            // Also peek beyond a NOT if there is one.
            if ($lookahead['type'] === Lexer::T_NOT) {
                $lookahead = $this->lexer->peek();
            }

            $this->lexer->resetPeek();
        }

        if ($token['type'] === Lexer::T_BETWEEN) {
            return $this->betweenExpression();
        }

        if ($token['type'] === Lexer::T_LIKE) {
            return $this->likeExpression();
        }

        if ($token['type'] === Lexer::T_IN) {
            return $this->inExpression();
        }

        if ($token['type'] === Lexer::T_IS && $lookahead['type'] === Lexer::T_NULL) {
            return $this->nullComparisonExpression();
        }

        return $this->comparisonExpression();
    }

    /**
     * betweenExpression ::= identificationVariable ["NOT"] "BETWEEN" arithmeticPrimary "AND" arithmeticPrimary
     *
     * @return BetweenExpressionNode
     */
    public function betweenExpression()
    {
        $not = false;
        $arithExpr1 = $this->identificationVariable();

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_BETWEEN);
        $arithExpr2 = $this->arithmeticPrimary();
        $this->match(Lexer::T_AND);
        $arithExpr3 = $this->arithmeticPrimary();

        $betweenExpr = new BetweenExpressionNode($arithExpr1, $arithExpr2, $arithExpr3);
        $betweenExpr->setNot($not);

        return $betweenExpr;
    }

    /**
     * comparisonExpression ::= arithmeticPrimary comparisonOperator arithmeticPrimary
     *
     * @return ComparisonExpressionNode
     */
    public function comparisonExpression()
    {
        $this->lexer->glimpse();

        $leftExpr  = $this->arithmeticPrimary();
        $operator  = $this->comparisonOperator();
        $rightExpr = $this->arithmeticPrimary();

        return new ComparisonExpressionNode($leftExpr, $operator, $rightExpr);
    }

    /**
     * inExpression ::= identificationVariable ["NOT"] "IN" "(" inParameter {"," inParameter}* ")"
     *
     * @return InExpressionNode
     */
    public function inExpression()
    {
        $inExpression = new InExpressionNode($this->identificationVariable());

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $inExpression->setNot(true);
        }

        $this->match(Lexer::T_IN);
        $this->match(Lexer::T_OPEN_PARENTHESIS);

        $literals = array();
        $literals[] = $this->inParameter();

        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $literals[] = $this->inParameter();
        }

        $inExpression->setLiterals($literals);

        $this->match(Lexer::T_CLOSE_PARENTHESIS);

        return $inExpression;
    }

    /**
     * inParameter ::= Literal
     *
     * @return string
     */
    public function inParameter()
    {
        return $this->literal();
    }

    /**
     * likeExpression ::= identificationVariable ["NOT"] "LIKE" stringExpression
     *
     * @return LikeExpressionNode
     */
    public function likeExpression()
    {
        $stringExpr = $this->identificationVariable();
        $not = false;

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);
            $not = true;
        }

        $this->match(Lexer::T_LIKE);

        $stringPattern = $this->stringExpression();

        $likeExpr = new LikeExpressionNode($stringExpr, $stringPattern);
        $likeExpr->setNot($not);

        return $likeExpr;
    }

    /**
     * nullComparisonExpression ::= identificationVariable "IS" ["NOT"] "NULL"
     *
     * @return NullComparisonExpressionNode
     */
    public function nullComparisonExpression()
    {
        $expr = $this->identificationVariable();

        $nullCompExpr = new NullComparisonExpressionNode($expr);

        $this->match(Lexer::T_IS);

        if ($this->lexer->isNextToken(Lexer::T_NOT)) {
            $this->match(Lexer::T_NOT);

            $nullCompExpr->setNot(true);
        }

        $this->match(Lexer::T_NULL);

        return $nullCompExpr;
    }

    /**
     * comparisonOperator ::= "=" | "<" | "<=" | "<>" | ">" | ">=" | "!="
     *
     * @return string
     */
    public function comparisonOperator()
    {
        switch ($this->lexer->lookahead['value']) {
            case '=':
                $this->match(Lexer::T_EQUALS);

                return '=';
            case '<':
                $this->match(Lexer::T_LOWER_THAN);
                $operator = '<';

                if ($this->lexer->isNextToken(Lexer::T_EQUALS)) {
                    $this->match(Lexer::T_EQUALS);
                    $operator .= '=';
                } else if ($this->lexer->isNextToken(Lexer::T_GREATER_THAN)) {
                    $this->match(Lexer::T_GREATER_THAN);
                    $operator .= '>';
                }

                return $operator;

            case '>':
                $this->match(Lexer::T_GREATER_THAN);
                $operator = '>';

                if ($this->lexer->isNextToken(Lexer::T_EQUALS)) {
                    $this->match(Lexer::T_EQUALS);
                    $operator .= '=';
                }

                return $operator;

            case '!':
                $this->match(Lexer::T_NEGATE);
                $this->match(Lexer::T_EQUALS);

                return '<>';

            default:
                $this->syntaxError('=, <, <=, <>, >, >=, !=');
        }

        return null;
    }

    /**
     * arithmeticPrimary ::= literal | identificationVariable
     */
    public function arithmeticPrimary()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_IDENTIFIER:
                return $this->identificationVariable();
            default:
                return $this->literal();
        }
    }

    /**
     * Literal ::= string | char | integer | float | boolean
     *
     * @return LiteralNode
     */
    public function literal()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);
                return new LiteralNode(LiteralNode::STRING, $this->lexer->token['value']);

            case Lexer::T_INTEGER:
            case Lexer::T_FLOAT:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_FLOAT
                );
                return new LiteralNode(LiteralNode::NUMERIC, $this->lexer->token['value']);

            case Lexer::T_TRUE:
            case Lexer::T_FALSE:
                $this->match(
                    $this->lexer->isNextToken(Lexer::T_TRUE) ? Lexer::T_TRUE : Lexer::T_FALSE
                );
                return new LiteralNode(LiteralNode::BOOLEAN, $this->lexer->token['value']);

            default:
                $this->syntaxError('Literal');
        }

        return null;
    }

    /**
     * identificationVariable ::= identifier
     *
     * @return IdentificationVariableNode
     */
    public function identificationVariable()
    {
        $this->match(Lexer::T_IDENTIFIER);

        return new IdentificationVariableNode($this->lexer->token['value']);
    }

    /**
     * stringExpression ::= identificationVariable | string
     *
     * @return IdentificationVariableNode|LiteralNode
     */
    public function stringExpression()
    {
        $lookaheadType = $this->lexer->lookahead['type'];

        switch ($lookaheadType) {
            case Lexer::T_IDENTIFIER:
                return $this->identificationVariable();

            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);
                return new LiteralNode(LiteralNode::STRING, $this->lexer->token['value']);
        }

        $this->syntaxError(
            'StateFieldPathExpression | string'
        );

        return null;
    }
}
