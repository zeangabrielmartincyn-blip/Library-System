<?php
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "mbstring loaded: ";
var_dump(extension_loaded('mbstring'));
echo "<br>mb_split exists: ";
var_dump(function_exists('mb_split'));