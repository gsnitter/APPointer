<?php

namespace APPointer\Parser;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class ParserBase {

    // We cannot type hint params or return value, since they differ from parser to parser.
    abstract public function normalize($member);
}
