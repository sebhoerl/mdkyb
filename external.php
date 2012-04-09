<?php

shell_exec('cp -rf external/forum/* web/forum');
$append = file_get_contents('external/wiki/LocalSettings.append.php');
$original = file_get_contents('web/wiki/LocalSettings.php');
list($updated) = explode('// EXTERNAL MDKYB //', $original);
$updated .= $append;
file_put_contents('web/wiki/LocalSettings.php', $updated);
