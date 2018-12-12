<?php
require '_conf.php';
spl_autoload_register(function ($class_name) {include "$class_name.php";});