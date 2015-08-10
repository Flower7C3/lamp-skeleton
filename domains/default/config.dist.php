<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

\Locale::setDefault('pl_PL');

$phpMyAdminURL = 'http://192.168.33.99/phpmyadmin/';
$suffix = "\.(dev|local)";
$dir = "/vagrant/domains/";
$itemsPerPage = 10;
$clientNames = [
//    'key' => 'name',
];
$tagIcons = [
    'PHP' => 'code',
    'Symfony' => 'code',
    'webmaster' => 'user-secret',
    'newsletter' => 'envelope',
    'mailing' => 'envelope',
    'backend' => 'server',
    'frontend' => 'globe',
    'HTML5' => 'html5',
    'CSS3' => 'css3',
    'pl' => 'flag',
    'NPM' => 'archive',
    'composer' => 'archive',
    'Grunt' => 'cogs',
    'GULP' => 'cogs',
    'mobile' => 'mobile',
    'Wordpress' => 'wordpress',
];
$filesAsTags = [
    'composer.json' => ['composer', 'backend'],
    'package.json' => ['NPM'],
    'Gruntfile.js' => ['Grunt', 'frontend'],
    'gulpfile.js' => ['GULP', 'frontend'],
    'wp-config.php' => ['Wordpress', 'PHP'],
    'app/appKernel.php' => ['Symfony', 'PHP'],
];
