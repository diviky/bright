<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters\Ql;

class Parser
{
    public const T_WHITESPACE = 0;
    public const T_GENERIC_SYMBOL = 1;
    public const T_IDENTIFIER = 2;
    public const T_IDENTIFIER_SEPARATOR = 3;
    public const T_VALUE = 4;
    public const T_COMPARISON_OPERATOR = 5;
    public const T_PRECEDENCE_OPERATOR = 6;
    public const T_LOGIC_OPERATOR = 7;

    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @var int
     */
    protected $tokenIndex = 0;

    /**
     * @param string $input
     *
     * @return null|\Diviky\Bright\Database\Filters\Ql\ParseTree
     */
    public function parse($input)
    {
        $patterns = [];
        $patterns[] = '([a-z-_\\\][a-z0-9-_\\\:]*[a-z0-9_]{1})';
        $patterns[] = '((?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?)';
        $patterns[] = '(\'(?:[^\']|\'\')*\')';
        $patterns[] = '("(?:[^"]|"")*")';
        $patterns[] = '([!><=~\:]{1,3})';
        $patterns[] = '(\s+)';
        $patterns[] = '(.)';

        $segments = preg_split(
            '#' . implode('|', $patterns) . '#i',
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
        );

        $this->tokens = [];
        foreach ($segments as $segment) {
            $type = $this->getTokenType($segment[0]);
            $this->tokens[] = [
                'value' => $segment[0],
                'type' => $type,
                'position' => $segment[1],
            ];
        }

        $parseTree = new ParseTree();
        $token = $this->currentToken();
        $logic = ParseTree::COMBINED_BY_AND;

        while (!empty($token) && isset($parseTree)) {
            switch (true) {
                case self::T_LOGIC_OPERATOR == $token['type']:
                    if (in_array(strtolower($token['value']), ['or', '||'])) {
                        $logic = ParseTree::COMBINED_BY_OR;
                    } elseif (in_array(strtolower($token['value']), ['in'])) {
                        $logic = ParseTree::COMBINED_BY_IN;
                    } else {
                        $logic = ParseTree::COMBINED_BY_AND;
                    }

                    break;
                case self::T_PRECEDENCE_OPERATOR == $token['type'] && '(' == $token['value']:
                    $parseTree = $parseTree->nest();

                    break;
                case self::T_PRECEDENCE_OPERATOR == $token['type'] && ')' == $token['value']:
                    $parseTree = $parseTree->unnest();

                    break;
                case self::T_IDENTIFIER == $token['type']:
                    $parseTree->addPredicate($this->parsePredicate(), $logic);

                    break;
                default:
                    throw new ParserException("Was expecting a Logic Operator (and, or, in), Precendence Operator ) or (, or an Identifier (' after {$token['value']}");
            }

            $token = $this->nextToken();
        }

        return $parseTree;
    }

    /**
     * @param string $value
     *
     * @return int
     */
    protected function getTokenType(&$value)
    {
        $type = self::T_GENERIC_SYMBOL;

        switch (true) {
            case '' === trim($value):
                return self::T_WHITESPACE;
            case '.' == $value:
                return self::T_IDENTIFIER_SEPARATOR;
            case is_numeric($value) || is_numeric($value[0]):
                return self::T_VALUE;
            case "'" === $value[0]:
                $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));

                return self::T_VALUE;
            case '"' === $value[0]:
                $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

                return self::T_VALUE;
            case '(' == $value || ')' == $value:
                return self::T_PRECEDENCE_OPERATOR;
            case in_array($value[0], ['=', '>', '<', '!', ':']):
                return self::T_COMPARISON_OPERATOR;
            case in_array(strtolower($value), ['and', 'or', '&&', '||', 'in']):
                return self::T_LOGIC_OPERATOR;
            case ctype_alpha($value[0]):
                return self::T_IDENTIFIER;
        }

        return $type;
    }

    /**
     * @return array
     */
    protected function currentToken()
    {
        if (!isset($this->tokens[$this->tokenIndex])) {
            return [];
        }

        return $this->tokens[$this->tokenIndex];
    }

    /**
     * @return array
     */
    protected function nextToken()
    {
        INCREMENT_TOKEN:
        ++$this->tokenIndex;

        if (!isset($this->tokens[$this->tokenIndex])) {
            return [];
        }

        if (self::T_WHITESPACE === $this->tokens[$this->tokenIndex]['type']) {
            goto INCREMENT_TOKEN;
        }

        return $this->tokens[$this->tokenIndex];
    }

    /**
     * @param int $increment
     *
     * @return array|bool
     */
    protected function peekToken($increment = 1)
    {
        if (!isset($this->tokens[$this->tokenIndex + $increment])) {
            return false;
        }

        return $this->tokens[$this->tokenIndex + $increment];
    }

    /**
     * @return Comparison
     *
     * @throws ParserException
     */
    protected function parsePredicate()
    {
        $identifier = new Identifier();
        $identifier->field = $this->currentToken()['value'];
        $token = $this->nextToken();
        if (isset($token['type']) && self::T_IDENTIFIER_SEPARATOR == $token['type']) {
            $identifier->name = $identifier->field;
            $token = $this->nextToken();
            if (self::T_IDENTIFIER !== $token['type']) {
                throw new ParserException('Parser error: predicate expects an identifier (unquoted string) after an identifier separator (dot)');
            }
            $identifier->field = $token['value'];
            $token = $this->nextToken();
        }

        if (!isset($token['type']) || self::T_COMPARISON_OPERATOR !== $token['type']) {
            throw new ParserException('A function name or comparison operator must follow an identifer');
        }

        if (isset($token['type']) && self::T_COMPARISON_OPERATOR == $token['type']) {
            $operator = $token['value'];
            $token = $this->nextToken();
            $peekToken = $this->peekToken();

            if (self::T_IDENTIFIER !== $token['type'] && self::T_VALUE !== $token['type']) {
                throw new ParserException('Comparisons must have an identifier or value on the right side');
            }

            $predicate = new Comparison();
            $predicate->leftType = Comparison::TYPE_IDENTIFIER;
            $predicate->left = $identifier;
            $predicate->op = $operator;

            if (self::T_IDENTIFIER == $token['type']) {
                $predicate->rightType = Comparison::TYPE_IDENTIFIER;
                $predicate->right = new Identifier();

                if ($peekToken && isset($peekToken['type']) && self::T_IDENTIFIER_SEPARATOR === $peekToken['type']) {
                    $predicate->right->name = $token['value'];

                    $this->nextToken(); // separator token
                    $token = $this->nextToken();
                    $peekToken = $this->peekToken();

                    $predicate->right->field = $token['value'];
                } else {
                    $predicate->right->field = $token['value'];
                }
            } else {
                $predicate->rightType = Comparison::TYPE_VALUE;
                $predicate->right = $token['value'];
            }

            if ($peekToken && isset($peekToken['type']) && !in_array($peekToken['type'], [self::T_WHITESPACE, self::T_PRECEDENCE_OPERATOR])) {
                $type = (self::T_IDENTIFIER === $token['type']) ? 'identifier' : 'value';

                throw new ParserException("Expected the *{$type}* {$token['value']} to be followed by whitespace or a ), was followed by {$peekToken['value']}");
            }
        } else {
            throw new ParserException('Non-comparison not supported');
        }

        return $predicate;
    }
}
