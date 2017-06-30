<?php
/*
 * Pig Latin Translator
 * Chris Petersen 2017
 */

class PigLatin {

    const PigLatinSuffix = 'AY';
    const PigLatinLikeliness = 0.95;
    const WordLowercase = 0;
    const WordUppercase = 1;
    const WordPropercase = 2;
    const WordUnknowncase = 3;

    static protected function getVowels() {
        return array(
            'A', 'E', 'I', 'O', 'U', 'Y'
        );
    }

    static protected function getConstants() {
        return array(
            'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M',
            'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z'
        );
    }

    static protected function getLetters() {
        return array_merge(self::getVowels(), self::getConstants());
    }

    static protected function isVowel($text) {
        mb_internal_encoding('UTF-8');
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        return in_array($first, self::getVowels());
    }

    static protected function isConstant($text) {
        mb_internal_encoding('UTF-8');
        $upper = mb_strtoupper($text);
        $constants = self::getConstants();
        $count = 0;
        while (in_array(mb_substr($upper, $count, 1, 'UTF-8'), $constants) && $count <= strlen($upper)) {
            $count++;
        }
        return $count;
    }
    
    static protected function splitWord($text) {
        // Helpful function to deal with punctuation etc.
        $letters = self::getLetters();
        $upper = mb_strtoupper($text);
        $start = 0;
        while (!in_array(substr($upper, $start, 1), $letters) && $start<strlen($text)) {
            $start++;
        } 
       $end = strlen($text);
        while (!in_array(substr($upper, $end, 1), $letters) && $end>0) {
            $end--;
        }
        return array(
            'prefix' => substr($text, 0, $start),
            'word' => substr($text, $start, $end - $start + 1),
            'suffix' => substr($text, $end + 1, strlen($text) - $end)
        );
    }

    static protected function isLikelyPigLatin($text) {
        // Gives a number between 0 and 1 on the likeliness
        // of the text being pig latin
        mb_internal_encoding('UTF-8');
        $upper = mb_strtoupper($text);
        $words = explode(' ', $upper);
        $totalwords = count($words);
        $count = 0;
        foreach ($words as $word) {
            $split = self::splitWord($word);
            if (mb_substr($split['word'], -2) == self::PigLatinSuffix) {
                $count++;
            }
        }
        return $count / $totalwords;
    }

    static protected function getWordCaseType($text) {
        // Helpful function to deal with different cases of text
        mb_internal_encoding('UTF-8');
        $words = explode(' ', $text);
        $firstword = $words[0];
        if ($firstword == mb_strtoupper($firstword)) {
            return self::WordUppercase;
        }
        if ($firstword == mb_strtolower($firstword)) {
            return self::WordLowercase;
        }
        if ($firstword == ucfirst($firstword)) {
            return self::WordPropercase;
        }
        return self::WordUnknowncase;
    }

    static protected function encodeWord($text) {
        $split = self::splitWord($text);
        $word = $split['word'];
        $isconstant = self::isConstant($word);
        if ($isconstant > 0) {
            switch (self::getWordCaseType($word)) {
                case self::WordUppercase:
                    $translated = mb_substr($word, $isconstant) . mb_substr($word, 0, $isconstant) . mb_strtoupper(self::PigLatinSuffix);
                    break;
                case self::WordPropercase:
                    $lowerword = mb_strtolower($word);
                    $translated = ucfirst(mb_substr($lowerword, $isconstant)) . mb_substr($lowerword, 0, $isconstant) . mb_strtolower(self::PigLatinSuffix);
                    break;
                default:
                    $lowerword = mb_strtolower($word);
                    $translated = mb_substr($lowerword, $isconstant) . mb_substr($lowerword, 0, $isconstant) . mb_strtolower(self::PigLatinSuffix);
                    break;
            }
        } else {
            $translated = $word . mb_strtolower(self::PigLatinSuffix);
        }
        return $split['prefix'] . $translated . $split['suffix'];
    }

    static public function encodeText($text) {
        mb_internal_encoding('UTF-8');
        $words = explode(' ', $text);
        $translated = '';
        foreach ($words as $word) {
            if ($translated != '') {
                $translated .= ' ';
            }
            $translated .= self::encodeWord($word);
        }
        return $translated;
    }

    static public function decodeText($text) {
        // Might not have started this project if I took
        // the time to realise this is theoretically impossible
    }

    static public function autocodeText($text) {
        if (self::isLikelyPigLatin($text) >= self::PigLatinLikeliness) {
            return self::decodeText($text);
        } else {
            return self::encodeText($text);
        }
    }

}

$text = 'This is a really \'good\' piece of text to start with. 12eyes34';
if (isset($_POST['text'])) {
    $text = PigLatin::autocodeText($_POST['text']);
}
?>
<html>
    <head>
        <title>Piglatin translator</title>
    </head>
    <body>
        <form method="post" action="/piglatin">
            <textarea name="text" cols="40" rows="10"><?php echo htmlentities($text); ?></textarea>
            <p><input type="submit" value="Translate" /></p>
        </form>
    </body>
</html>
