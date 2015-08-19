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
    'evolution' => 'plus-square-o',
    'support' => 'life-ring',
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
$toolList = [
    'repo'=>(object)[
        'icon'=>'fork',
        'title'=>'Repository manager',
        'name'=>'GIT',
    ],
    'pma'=>(object)[
        'icon'=>'database',
        'title'=>'Database manager',
        'name'=>'SQL',
    ],
    'crm'=>(object)[
        'icon'=>'user-secret',
        'title'=>'CRM project',
        'name'=>'CRM',
    ],
    'task'=>(object)[
        'icon'=>'tasks',
        'title'=>'Redmine project',
        'name'=>'Redmine',
    ],
    'wiki'=>(object)[
        'icon'=>'wikipedia',
        'title'=>'Wiki info',
        'name'=>'wiki',
    ],
    'info'=>(object)[
        'icon'=>'info-circle',
        'title'=>'Info site',
        'name'=>'info',
    ],
    'support'=>(object)[
        'icon'=>'life-ring',
        'title'=>'Support site',
        'name'=>'support',
    ],
];
