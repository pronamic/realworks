<?php

use CustomPost\Plugin\Admin\SaveController;
use CustomPost\Plugin\Admin\CacheController;
use CustomPost\Plugin\Admin\LicenseController;
use CustomPost\Plugin\Admin\DashboardController;
use CustomPost\Plugin\Admin\Updater\DateController;
use CustomPost\Plugin\Admin\Updater\BatchController;
use CustomPost\Plugin\Admin\Updater\UpdateController;
use CustomPost\Plugin\Admin\Updater\ArchiveController;

$ajax->route('admin/dashboard', DashboardController::to('index'));

$ajax->route('admin/save', SaveController::to('save'));

$ajax->route('admin/logs/dates', DateController::to('index'));

$ajax->route('admin/logs/batches', BatchController::to('index'));
$ajax->route('admin/logs/batch', BatchController::to('show'));
$ajax->route('admin/logs/batch/destroy', BatchController::to('destroy'));
$ajax->route('admin/logs/batch/retry', BatchController::to('retry'));

$ajax->route('admin/logs/archive', ArchiveController::to('show'));
$ajax->route('admin/logs/archive/retry', ArchiveController::to('retry'));

$ajax->route('admin/update', UpdateController::to('update'));
$ajax->route('admin/update/upload', UpdateController::to('upload'));
$ajax->route('admin/update/cancel', UpdateController::to('cancel'));

$ajax->route('admin/license', LicenseController::to('verify'));

$ajax->route('admin/cache/flush', CacheController::to('flush'));
