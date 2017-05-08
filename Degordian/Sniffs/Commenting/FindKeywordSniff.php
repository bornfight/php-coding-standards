<?php

/**
 * Class for a sniff to find keywords in comments.
 *
 * For example you could use this sniff to find "hack", "fixme", "todo" comments in the code
 */

namespace PHP_Codesniffer\Standards\Degordian\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class FindKeywordSniff implements Sniff
{

    public $keywords = [
        'hack',
        'todo',
        'fixme'
    ];

    public function register()
    {
        return Tokens::$commentTokens;
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $content = $tokens[$stackPtr]['content'];
        $matches = [];
        $search = implode('|', $this->keywords);
        $pattern = sprintf('/(?:\A|[^\p{L}]+)(%s)([^\p{L}]+(.*)|\Z)/ui', $search);
        preg_match($pattern, $content, $matches);
        if (empty($matches) === false) {
            $keyword = $matches[1];
            $comment = $matches[1] . $matches[2];
            $type = 'Found';
            $message = trim($comment);
            $message = trim($message, '-:[](). ');
            $warning = 'Comment contains a discouraged keyword';
            $data = [$message];
            if ($message !== '') {
                $type = 'Found';
                $warning .= ' "%s"';
            }

            $phpcsFile->addWarning($warning, $stackPtr, $type, $data);
        }
    }
}