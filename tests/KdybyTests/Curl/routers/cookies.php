<?php

header('Content-Type: text/plain; charset=utf-8');

setcookie('kdyby', 'is awesome', time() + 3600);
setcookie('nette', 'is awesome', time() + 3600);

setcookie('array[one]', 'Lister', time() + 3600, '/', '', TRUE);
setcookie('array[two]', 'Rimmer', time() + 3600, '/', '', TRUE, TRUE);

setcookie('spešl ký', 'wif=spešl;vajlů', time() + 3600);

echo 'pong';
