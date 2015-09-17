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
    'webmaster' => 'user-secret',

    'backend' => 'server',
    'frontend' => 'globe',
    'PHP' => 'code',
    'HTML5' => 'html5',
    'CSS3' => 'css3',

    'NPM' => 'archive',
    'composer' => 'archive',

    'Grunt' => 'cogs',
    'GULP' => 'cogs',

    'Symfony' => 'code',
    'Wordpress' => 'wordpress',

    'newsletter' => 'envelope',
    'mailing' => 'envelope',
    'events' => 'calendar',

    'pl' => 'flag',
    'mobile' => 'mobile',
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
    'repo' => (object)[
        'icons' => ['fork'],
        'title' => 'Repository manager',
        'name' => 'GIT',
    ],
    'pma' => (object)[
        'icons' => ['database'],
        'title' => 'Database manager',
        'name' => 'SQL',
    ],
    'crm' => (object)[
        'icons' => ['user-secret'],
        'title' => 'CRM project',
        'name' => 'CRM',
    ],
    'task' => (object)[
        'icons' => ['tasks'],
        'title' => 'Redmine project',
        'name' => 'Redmine',
    ],
    'wiki' => (object)[
        'icons' => ['wikipedia'],
        'title' => 'Wiki info',
        'name' => 'wiki',
    ],
    'info' => (object)[
        'icons' => ['info-circle'],
        'title' => 'Info site',
        'name' => 'info',
    ],
    'support' => (object)[
        'icons' => ['life-ring'],
        'title' => 'Support site',
        'name' => 'support',
    ],
    'event' => (object)[
        'icons' => ['calendar'],
        'title' => 'Support site',
        'name' => 'support',
    ],
    'CMS' => (object)[
        'icons' => ['pencil-square'],
        'title' => 'CMS site',
        'name' => 'CMS',
    ],
];

$infoList = [

];

/**
 * methods
 */
function toolMenu($key, $url)
{
    global $toolList;
    if (isset($toolList[$key])) {
        $temp = clone $toolList[$key];
    } else {
        $temp = (object)array(
            'title' => $key,
            'name' => $key,
            'url' => NULL,
            'icons' => array(),
        );
    }
    if (preg_match("'event'", $key)) {
        $temp->icons[] = 'calendar';
        $temp->name = preg_replace("'event'", '', $temp->name);
    }
    if (preg_match("'^(PL|EN)'", $key)) {
        $temp->icons[] = 'flag';
    }
    if (preg_match("'pay'", $key)) {
        $temp->icons[] = 'money';
    }
    if (preg_match("'skin'", $key)) {
        $temp->icons[] = 'eye';
    }
    if (preg_match("'link'", $key)) {
        $temp->icons[] = 'link';
    }
    $temp->url = $url;
    $temp->code = md5($temp->title);
    return $temp;
}

function infoMenu($key, $val)
{
    global $infoList;
    if (isset($infoList[$key])) {
        $temp = clone $infoList[$key];
    } else {
        $temp = (object)array(
            'title' => $key,
            'code' => NULL,
            'name' => NULL,
            'value' => NULL,
            'valueReal' => NULL,
            'icons' => array(),
        );
    }
    $temp->name = $temp->title;
    $temp->value = $temp->valueReal = $val;
    $temp->code = md5($temp->title);

    if (preg_match("'ssh'", $key)) {
        $temp->icons[] = 'terminal';
        $temp->name = preg_replace("'ssh'", '', $temp->name);
    }
    if (preg_match("'sql'", $key)) {
        $temp->icons[] = 'database';
        $temp->name = preg_replace("'sql'", '', $temp->name);
    }
    if (preg_match("'event'", $key)) {
        $temp->icons[] = 'calendar';
        $temp->name = preg_replace("'event'", '', $temp->name);
    }
    if (preg_match("'^(PL|EN)'", $key)) {
        $temp->icons[] = 'flag';
    }
    if (preg_match("'pass'", $key)) {
        $temp->value = '***';
    }
    return $temp;
}

function generateListLink($href, array $params = [], $icons = [], $text = false)
{
    $link = generateLink($href, $params, $icons, $text);
    if (empty($link)) {
        return NULL;
    }
    return '<li>' . $link . '</li>';
}

function generateLink($href, array $params = [], $icons = [], $text = false)
{
    if (empty($href)) {
        return NULL;
    }
    if ($href === 'dropdown') {
        $href = 'javascript://undefined';
        $params['data-toggle'] = "dropdown";
        $params['aria-haspopup'] = "true";
        $params['aria-expanded'] = "false";
        $params['class'] .= " dropdown-toggle";
    }
    $link = '<a';
    $params['href'] = $href;
    foreach ($params as $k => $v) {
        $link .= ' ' . $k . '="' . $v . '"';
    }
    $link .= '>';
    if (!empty($icons)) {
        if (!is_array($icons)) {
            $icons = [$icons];
        }
        foreach ($icons as $icon) {
            $link .= '<em class="fa fa-fw fa-' . $icon . '"></em>';
        }
    }
    $link .= $text;
    $link .= '</a>';
    return $link;
}