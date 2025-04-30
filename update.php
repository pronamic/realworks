#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../wp-load.php';

$result = Realworks::make('updater')->update();

exit($result ? 0 : 1);
