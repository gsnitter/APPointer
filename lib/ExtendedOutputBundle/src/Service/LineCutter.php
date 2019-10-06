<?php
namespace Sni\ExtendedOutputBundle\Service;

use Sni\ExtendedOutputBundle\Entity\TextSnippet;
use Sni\ExtendedOutputBundle\Entity\StringPart;

class LineCutter
{
    private const COLOR_REG_EXP = '@\e\[\d{1,2}(;\d{1,2})?m@m';
    
    public function getTextSnippet(string $line, int $charOffset, $textWidth, int $debugCalledBefore = 0): TextSnippet
    {
        // var_dump(['line' => $line, 'charOffset' => $charOffset, 'textWidth' => $textWidth, 'debugCalledBefore' => $debugCalledBefore]);
        // TODO SNI: Wenn $charOffset != 0 müssen wir anders arbeiten.
        $strings = preg_split(self::COLOR_REG_EXP, $line);

        // Text splitted by color codes.
        $stringParts = array_map(function(string $string) {
            return new StringPart($string);
        }, $strings);
        $lineLength = array_reduce($stringParts, function($sum, $stringPart) {
            return $sum + $stringPart->getLength();
        }, 0);

        // All color Codes
        preg_match_all(self::COLOR_REG_EXP, $line, $matches);

        $colorCodes = $matches[0];

        $currentOffset = $currentWidth = 0;
        $result = '';
        $isEol = false;
        $gotOne = false;

        foreach ($stringParts as $key => $stringPart) {
            $offset = $gotOne ? 0 : $charOffset - $currentOffset;
            $subString = $stringPart->getSubstring($offset, $textWidth - $currentWidth);
            if ($subString) {
                $gotOne = true;
                $currentWidth += mb_strlen($subString);
                $currentOffset += mb_strlen($subString);
                $result .= $subString;
            } else {
                $currentOffset += $stringPart->getWidth();
            }
            if (isset($colorCodes[$key])) {
                $result .= $colorCodes[$key];
            }
        }
        // if (in_array($debugCalledBefore, [1,2,3,4,5,6,8])) {
        if ($currentWidth + $charOffset === $lineLength) {
            $isEol = true;
        }

        // var_dump(['debugCalledBefore' => $debugCalledBefore, 'lineLength' => $lineLength, 'result' => $result, 'currentWidth + charOffset' => $currentWidth + $charOffset, 'currentWidth' => $currentWidth, 'isEol' => $isEol]);
        return new TextSnippet($result, $currentWidth + $charOffset, $currentWidth, $isEol);
    }
}
