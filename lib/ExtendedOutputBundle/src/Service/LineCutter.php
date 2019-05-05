<?php
namespace Sni\ExtendedOutputBundle\Service;

use Sni\ExtendedOutputBundle\Entity\TextSnippet;
use Sni\ExtendedOutputBundle\Entity\StringPart;

class LineCutter
{
    private const COLOR_REG_EXP = '@\e\[\d{1,2}(;\d{1,2})?m@m';
    
    public function getTextSnippet(string $line, int $charOffset, $textWidth): TextSnippet
    {
        // TODO SNI: Wenn $charOffset != 0 müssen wir anders arbeiten.
        $strings = preg_split(self::COLOR_REG_EXP, $line);

        $stringParts = array_map(function(string $string) {
            return new StringPart($string);
        }, $strings);

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
                if (mb_strlen($subString) == $stringPart->getWidth()) {
                    if (isset($colorCodes[$key])) {
                        $result .= $colorCodes[$key];
                    }
                    if ($key == count($stringPart) - 1) {
                        $isEol = true;
                    }
                }
            } else {
                $currentOffset += $stringPart->getWidth();
            }
        }

        return new TextSnippet($result, $currentWidth + $charOffset, $currentWidth, $isEol);
    }
}
