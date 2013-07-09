<?php

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="conventions.txt"');
header('Content-Length: '. filesize(__DIR__ . '/../../../conventions.txt'));
readfile(__DIR__ . '/../../../conventions.txt');
