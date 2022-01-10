<?php 


function msword_conversion($str)
{
$str = str_replace(chr(130), ',', $str);    // baseline single quote
$str = str_replace(chr(131), 'NLG', $str);  // florin
$str = str_replace(chr(132), '"', $str);    // baseline double quote
$str = str_replace(chr(133), '...', $str);  // ellipsis
$str = str_replace(chr(134), '**', $str);   // dagger (a second footnote)
$str = str_replace(chr(135), '***', $str);  // double dagger (a third footnote)
$str = str_replace(chr(136), '^', $str);    // circumflex accent
$str = str_replace(chr(137), 'o/oo', $str); // permile
$str = str_replace(chr(138), 'Sh', $str);   // S Hacek
$str = str_replace(chr(139), '<', $str);    // left single guillemet
$str = str_replace(chr(140), 'OE', $str);   // OE ligature
$str = str_replace(chr(145), "'", $str);    // left single quote
$str = str_replace(chr(146), "'", $str);    // right single quote
$str = str_replace(chr(147), '"', $str);    // left double quote
$str = str_replace(chr(148), '"', $str);    // right double quote
$str = str_replace(chr(149), '-', $str);    // bullet
$str = str_replace(chr(150), '-–', $str);    // endash
$str = str_replace(chr(151), '--', $str);   // emdash
$str = str_replace(chr(152), '~', $str);    // tilde accent
$str = str_replace(chr(153), '(TM)', $str); // trademark ligature
$str = str_replace(chr(154), 'sh', $str);   // s Hacek
$str = str_replace(chr(155), '>', $str);    // right single guillemet
$str = str_replace(chr(156), 'oe', $str);   // oe ligature
$str = str_replace(chr(159), 'Y', $str);    // Y Dieresis
$str = str_replace('°C', '&deg;C', $str);    // Celcius is used quite a lot so it makes sense to add this in
$str = str_replace('£', '&pound;', $str);
$str = str_replace("'", "&#39;", $str); 
$str = str_replace('"', '&#34;', $str);
$str = str_replace('–', '&ndash;', $str);

return $str;
}
