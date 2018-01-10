<?php

namespace SniTodos\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class ParserBase {

    // We cannot type hint params or return value, since they differ from parser to parser.
    abstract public function normalize($member);
    abstract public function validate($member = null, ExecutionContextInterface $context, $payload);

    /**
     * Return array of members, that needs to be normalized before.
     * The parser needs to implement a getter for it, so that the
     * normalizer can inject it.
     *
     * @return string[]
     */
    public function getNeededNormalizedValues(): array
    {
        return [];
    }
}
