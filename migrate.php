<?php

$test = shell_exec('php artisan migrate');

throw new Exception($test);