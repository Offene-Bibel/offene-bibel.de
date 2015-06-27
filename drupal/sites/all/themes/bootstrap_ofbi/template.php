<?php

/**
 * @file
 * template.php
 */
drupal_add_css('/static/css/lib/bootstrap.css', array('group' => CSS_THEME, 'type' => 'external'));
drupal_add_css(drupal_get_path('theme', 'bootstrap_ofbi') . '/css/overrides.css', array('group' => CSS_THEME, 'type' => 'file'));

drupal_add_css('/static/css/header_footer.css', array('group' => CSS_THEME, 'type' => 'external'));
drupal_add_css('http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic&subset=latin,greek-ext,greek,latin-ext', array('group' => CSS_THEME, 'type' => 'external'));
drupal_add_css(drupal_get_path('theme', 'bootstrap_ofbi') . '/css/ofbi-style.css', array('group' => CSS_THEME, 'type' => 'file'));
