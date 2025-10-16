<?php
// 1
$text = 'abcde';
echo $text[0] . $text[2] . $text[4] . "\n";

// 2
$text = 'abcde';
$text[0] = '!';
echo $text . "\n";

// 3
$num = '12345';
$sum = $num[0] + $num[1] + $num[2] + $num[3] + $num[4];
echo $sum . "\n";
?>
