<?php

declare(strict_types=1);

/*
 * Front controller para hospedagens em que a raiz pública não pode ser
 * alterada (como o InfinityFree, cuja raiz é sempre htdocs/).
 */
require __DIR__ . '/public/index.php';
