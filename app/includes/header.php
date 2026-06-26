<?php
$rootUrl = defined('ROOT_URL') ? ROOT_URL : '../../..';
$webBase = defined('WEB_BASE') ? WEB_BASE : '/library-system';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="University Library Management System — <?= htmlspecialchars($pageTitle ?? 'ULMS') ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'ULMS') ?> | University Library</title>
  <link rel="stylesheet" href="<?= $webBase ?>/public/assets/css/style.css">
</head>
<body>
<div class="layout">
