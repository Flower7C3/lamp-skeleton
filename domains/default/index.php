<?php

require 'config.dist.php';
require 'config.php';

/**
 * versions
 */
$bootstrapVersion = '3.3.4';
$fontawesomeVersion = '4.4.0';
$jqueryVersion = '2.1.4';

/**
 * read directories
 */
$idf = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
$dh = opendir($dir);
$filenames = [];
$CLIENTS = [];
$TAGS = [];
$DOMAINS = [];
$HOSTS = [];
while (false !== ($filename = readdir($dh))) {

    if (
        preg_match("'" . $suffix . "$'", $filename)
        && (
            (!isset($_GET['project'])) ||
            (isset($_GET['project']) && stripslashes($_GET['project'] . $suffix) == $filename)
        )
    ) {

        $data = [
            'code' => null,
            'name' => null,
            'url' => null,
            'devUrl' => null,
            'stageUrl' => null,
            'liveUrl' => null,
            'repoUrl' => null,
            'date' => null,
            'tools' => [],
            'tags' => [],
        ];

        # domain and paths
        $localDomain = $filename;
        $externalDomain = preg_replace("'" . $suffix . "'", '', $filename);
        $symlinkPath = $dir . $filename;
        $realPath = realpath($dir . (is_link($symlinkPath) ? readlink($symlinkPath) : $symlinkPath));
        if (file_exists($symlinkPath . '/web/app_dev.php')) {
            $baseurl = '/app_dev.php';
        } elseif (file_exists($symlinkPath . '/web/wp-config.php')) {
            $baseurl = ':81';
        } elseif (file_exists($symlinkPath . '/build/production/')) {
            $baseurl = ':82';
        } elseif (file_exists($symlinkPath . '/web/')) {
            $baseurl = '/';
        } else {
            $baseurl = ':81';
        }

        # last modification time from repo
        $mtime = file_exists($symlinkPath . '/.git/') ? filemtime($symlinkPath . '/.git/') : (file_exists($symlinkPath) ? filemtime($symlinkPath) : 0);

        # meta data
        $data['name'] = $externalDomain;
        $data['code'] = basename(dirname($realPath));
        $data['client_id'] = preg_match("'^([0-9]{3})_(.*)$'", $data['code']) ? preg_replace("'^([0-9]{3})_(.*)$'", "$1.", $data['code']) : null;
        $data['job_id'] = preg_match("'^([0-9]{3})_([0-9]{3})_(.*)$'", $data['code']) ? preg_replace("'^([0-9]{3})_([0-9]{3})_(.*)$'", "$1.$2", $data['code']) : null;
        $data['date'] = new \DateTime('@' . $mtime);
        $data['current'] = ($data['date']->diff(new \DateTime())->days > 7) ? false : true;

        # local and live URLs
        $data['url'] = 'http://' . $localDomain . $baseurl;
        if (preg_match("'\.'", $externalDomain)) {
            $data['liveUrl'] = 'http://' . $externalDomain;
        }

        # repo URL
        if (file_exists($symlinkPath . '/.git/config')) {
            $gitConfig = parse_ini_file($symlinkPath . '/.git/config', true);
            if (!empty($gitConfig['remote origin']['url'])) {
                $data['path']['repo'] = $gitConfig['remote origin']['url'];
                $data['repoUrl'] = preg_replace("'^git@(.*):(.*)\.git$'", "https://$1/$2", $gitConfig['remote origin']['url']);
            }
            foreach ($gitConfig as $gName => $gData) {
                if (preg_match("'^branch (.*)$'", $gName)) {
                    $data['branches'][] = preg_replace("'^branch (.*)$'", "$1", $gName);
                }
            }
        }

        # data from description file
        $descriptionFiles = [
            $symlinkPath . '/DESCRIPTION',
            $symlinkPath . '/DESCRIPTION.ini',
            dirname($realPath) . '/DESCRIPTION',
            dirname($realPath) . '/DESCRIPTION.ini',
            dirname($realPath) . '/DESCRIPTION-' . $externalDomain,
            dirname($realPath) . '/DESCRIPTION-' . $externalDomain . '.ini',
        ];
        foreach ($descriptionFiles as $descriptionFile) {
            if (file_exists($descriptionFile)) {
                $config = parse_ini_file($descriptionFile, true);
                if (isset($config['domains']['local'])) {
                    $data['url'] = $config['domains']['local'];
                }
                if (isset($config['domains']['dev'])) {
                    $data['devUrl'] = $config['domains']['dev'];
                }
                if (isset($config['domains']['stage'])) {
                    $data['stageUrl'] = $config['domains']['stage'];
                }
                if (isset($config['domains']['prod'])) {
                    $data['liveUrl'] = $config['domains']['prod'];
                }
                if (isset($config['tools']['repo'])) {
                    $data['repoUrl'] = $config['tools']['repo'];
                    unset($config['tools']['repo']);
                }
                if (isset($config['client_id'])) {
                    $data['client_id'] = $config['client_id'];
                }
                if (isset($config['job_id'])) {
                    $data['job_id'] = $config['job_id'];
                }
                if (isset($config['code'])) {
                    $data['code'] = $config['code'];
                }
                if (!empty($config['tools'])) {
                    foreach ($config['tools'] as $key => $url) {
                        $data['tools'][$key] = toolMenu($key, $url);
                    }
                    ksort($data['tools']);
                }
                if (!empty($config['info'])) {
                    foreach ($config['info'] as $key => $val) {
                        $data['info'][$key] = infoMenu($key, $val);
                    }
                }
                if (!empty($config['note'])) {
                    krsort($config['note']);
                    $data['note'] = $config['note'];
                }
                if (!empty($config['tags'])) {
                    $data['tags'] = explode(',', $config['tags']);
                    $TAGS = array_merge($TAGS, $data['tags']);
                }
            }
        }

        foreach ($filesAsTags as $file => $tag) {
            if (file_exists($symlinkPath . '/' . $file)) {
                $data['tags'] = array_merge($data['tags'], $tag);
                $TAGS = array_merge($TAGS, $tag);
            }
        }

        $data['tags'] = array_unique($data['tags']);
        natcasesort($data['tags']);

        $data['path']['real'] = $realPath;
        $data['path']['link'] = $symlinkPath;

        $CLIENTS[] = $data['client_id'];

        $DOMAINS[] = (object)$data;

        $HOSTS[] = $localDomain;
    }
}

sort($HOSTS);

$CLIENTS = array_unique($CLIENTS);
natcasesort($CLIENTS);

$TAGS = array_unique($TAGS);
natcasesort($TAGS);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>VServer</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/<?= $bootstrapVersion ?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/<?= $bootstrapVersion ?>/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/css/bootstrap-datepicker3.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/<?= $fontawesomeVersion ?>/css/font-awesome.min.css">
        <link rel="stylesheet" href="dist/main.css">
        <link rel='shortcut icon' href='dist/icon.png' />
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/">
                        <span class="fa fa-cloud"></span>
                        VServer
                    </a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="fa fa-fw fa-bars"></span>
                    </button>
                    <button type="button" class="navbar-toggle" href="javascript://undefined">
                        <em class="fa fa-fw fa-clock-o"></em>
                        <strong class="current-time" data-copy="#current-time"></strong>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <? if (!empty($CLIENTS) || !empty($TAGS)): ?>
                        <ul class="nav navbar-nav">
                            <? if (!empty($CLIENTS)): ?>
                                <li class="dropdown">
                                    <a href="javascript://undefined" title="clients list" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <em class="fa fa-fw fa-briefcase"></em>
                                        <span class="text">Clients</span>
                                        <em class="hidden-sm hidden-md hidden-lg fa fa-level-down"></em>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <? foreach ($CLIENTS as $clientId): ?>
                                            <li>
                                                <a data-tag="<?= $clientId ?>" href="javascript://undefined">
                                                    <?= $clientId ?>
                                                    <? if (isset($clientNames[$clientId])): ?>
                                                        <?= $clientNames[$clientId] ?>
                                                    <? endif; ?>
                                                </a>
                                            </li>
                                        <? endforeach; ?>
                                    </ul>
                                </li>
                            <? endif; ?>
                            <? if (!empty($TAGS)): ?>
                                <li class="dropdown">
                                    <a href="javascript://undefined" title="tags list" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <em class="fa fa-fw fa-tags"></em>
                                        <span class="text">Tags</span>
                                        <em class="hidden-sm hidden-md hidden-lg fa fa-level-down"></em>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <? foreach ($TAGS as $tag): ?>
                                            <? if (isset($tagIcons[$tag])): ?>
                                                <li>
                                                    <a data-tag="<?= $tag ?>" href="javascript://undefined">
                                                        <em class="fa fa-fw fa-<?= $tagIcons[$tag] ?>"></em>
                                                        <?= $tag ?>
                                                    </a>
                                                </li>
                                            <? endif; ?>
                                        <? endforeach; ?>
                                        <li class="divider hidden-xs"></li>
                                        <? foreach ($TAGS as $tag): ?>
                                            <? if (!isset($tagIcons[$tag])): ?>
                                                <li>
                                                    <a data-tag="<?= $tag ?>" href="javascript://undefined">
                                                        <?= $tag ?>
                                                    </a>
                                                </li>
                                            <? endif; ?>
                                        <? endforeach; ?>
                                    </ul>
                                </li>
                            <? endif; ?>
                            <li>
                                <a data-tag="latest" href="javascript://undefined">
                                    <em class="fa fa-fw fa-newspaper-o"></em>
                                    Latest
                                </a>
                            </li>
                        </ul>
                    <? endif; ?>
                    <ul class="nav navbar-nav navbar-right">
                        <? foreach ($TOOLS as $key => $url): ?>
                            <li>
                                <a href="<?= $url ?>" title="<?= isset($toolList[$key]) ? $toolList[$key]->title : $key ?>">
                                    <em class="fa fa-fw fa-<?= isset($toolList[$key]) ? $toolList[$key]->icons[0] : 'square' ?>"></em>
                                    <span class="hidden-sm hidden-md hidden-lg">
                                            <?= isset($toolList[$key]) ? $toolList[$key]->name : $key ?>
                                        <small class="fa fa-external-link"></small>
                                </span>
                                </a>
                            </li>
                        <? endforeach; ?>
                        <li>
                            <a data-toggle="modal" href="#howto" title="Howto info">
                                <span class="fa fa-fw fa-info-circle"></span>
                                <span class="hidden-sm hidden-md hidden-lg">Howto</span>
                            </a>
                        </li>
                        <? if (!empty($DOMAINS) && !$isXip): ?>
                            <li>
                                <a data-toggle="modal" href="#hostsConfig" title="Hosts config info">
                                    <span class="fa fa-fw fa-cog"></span>
                                    <span class="hidden-sm hidden-md hidden-lg">Hosts</span>
                                </a>
                            </li>
                        <? endif; ?>
                        <li class="hidden-xs">
                            <a href="javascript://undefined" class="datepicker">
                                <em class="fa fa-fw fa-calendar"></em>
                            </a>
                        </li>
                        <li class="hidden-xs">
                            <a href="javascript://undefined">
                                <em class="fa fa-fw fa-clock-o"></em>
                                <strong class="current-time" data-copy="#current-time"></strong>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <section class="container loading" id="main">
            <div class="row">
                <div class="col-sm-12">
                    <? if (empty($DOMAINS)): ?>
                        <div class="alert alert-info">
                            <em class="fa fa-fw fa-info-circle"></em>
                            No directories configured. Create one with suffix <code><?= stripslashes($suffix) ?></code> in <code><?= $dir ?></code> on virtual machine and add it to hosts file in local machine.
                        </div>
                    <? else: ?>
                        <table id="searchlist"
                               data-classes="table table-hover table-condensed table-striped"
                               data-toggle="table"
                               data-search="true"
                               data-pagination="true"
                               data-page-size="<?= $itemsPerPage ?>"
                               data-sort-name="date"
                               data-sort-order="desc"
                        >
                            <thead>
                                <tr>
                                    <th
                                        data-field="name"
                                        data-sortable="true"
                                    >
                                        <em class="fa fa-file-o"></em> Name
                                    </th>
                                    <th
                                        style="width:140px;">
                                        <em class="fa fa-fw fa-tags"></em> Tags
                                    </th>
                                    <th
                                        data-field="date"
                                        data-sortable="true"
                                        style="width:140px;">
                                        <em class="fa fa-fw fa-calendar"></em> Date
                                    </th>
                                    <th
                                        style="width:140px;">
                                        <em class="fa fa-fw fa-link"></em> Links
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <? foreach ($DOMAINS as $i => $domain): ?>
                                    <tr id="tr-id-<?= $i ?>" class="tr-class-<?= $i ?>" data-id="<?= $i ?>">
                                        <td class="name<?= $domain->current ? ' lead' : '' ?>">
                                            <a href="<?= $domain->url ?>">
                                                <?= $domain->name ?>
                                            </a>
                                        </td>
                                        <td class="tags">
                                            <div class="tags">
                                                <? if (!empty($domain->client_id) && !empty($domain->job_id)): ?>
                                                    <a data-tag="<?= $domain->client_id ?>" href="javascript://undefined"><strong><?= isset($clientNames[$domain->client_id]) ? $clientNames[$domain->client_id] : $domain->client_id ?></strong>
                                                        (#<?= $domain->job_id ?>)</a>
                                                <? elseif (isset($clientNames[$domain->client_id])): ?>
                                                    <a data-tag="<?= $domain->client_id ?>"
                                                       href="javascript://undefined"><strong><?= isset($clientNames[$domain->client_id]) ? $clientNames[$domain->client_id] : $domain->client_id ?></strong><? if (isset($clientNames[$domain->client_id])): ?> (#<?= $domain->client_id ?>)<? endif; ?>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->tags)): ?>
                                                    <? foreach ($domain->tags as $tag): ?>
                                                        <a data-tag="<?= $tag ?>" href="javascript://undefined"><?= $tag ?></a>
                                                    <? endforeach; ?>
                                                <? endif; ?>
                                            </div>
                                            <? if ($domain->current): ?>
                                                <a data-tag="latest" href="javascript://undefined"></a>
                                            <? endif; ?>
                                        </td>
                                        <td class="date">
                                            <div class="date">
                                                <span class="hidden"><?= $domain->date->format('Y-m-d H:i:s') ?></span>
                                                <?= $idf->format($domain->date) ?>
                                            </div>
                                        </td>
                                        <td class="links">
                                            <div class="btn-group pull-right links">
                                                <div class="btn-group" role="group">
                                                    <?= generateLink('dropdown', ['title' => "Domains", 'class' => "btn btn-primary btn-xs",], ['globe']) ?>
                                                    <ul class="dropdown-menu">
                                                        <?= generateListLink($domain->url, ['title' => "Local development page",], ['code'], 'Local') ?>
                                                        <?= generateListLink($domain->devUrl, ['title' => "Development page",], ['globe'], 'Development') ?>
                                                        <?= generateListLink($domain->stageUrl, ['title' => "Pre-production (stage) page",], ['globe'], 'Pre-production (stage)') ?>
                                                        <?= generateListLink($domain->liveUrl, ['title' => "Production (live) page",], ['industry'], 'Production (live)') ?>
                                                    </ul>
                                                </div>
                                                <? if (!empty($domain->tools) || !empty($domain->repoUrl)): ?>
                                                    <div class="btn-group" role="group">
                                                        <?= generateLink('dropdown', ['title' => "External tools", 'class' => "btn btn-primary btn-xs",], ['wrench']) ?>
                                                        <ul class="dropdown-menu">
                                                            <?= generateListLink($domain->repoUrl, ['title' => "GIT repository",], ['code-fork'], 'GIT') ?>
                                                            <? foreach ($domain->tools as $tool): ?>
                                                                <?= generateListLink($tool->url, ['title' => $tool->title], $tool->icons, $tool->name) ?>
                                                            <? endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <? endif; ?>
                                                <? if (!empty($domain->info)): ?>
                                                    <div class="btn-group" role="group">
                                                        <?= generateLink('dropdown', ['title' => "Domain info", 'class' => "btn btn-info btn-xs",], ['info-circle']) ?>
                                                        <ul class="dropdown-menu">
                                                            <? foreach ($domain->info as $info): ?>
                                                                <?= generateListLink("javascript://undefined", ['title' => 'Copy ' . $info->title, 'data-copy' => '#domain-' . $i . '-info-' . $info->code . ''], $info->icons, $info->name . ' <code>' . $info->value . '</code>') ?>
                                                            <? endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <? endif; ?>
                                                <div class="btn-group" role="group">
                                                    <?= generateLink('dropdown', ['title' => "Copy variables", 'class' => "btn btn-info btn-xs",], ['copy']) ?>
                                                    <ul class="dropdown-menu">
                                                        <?= generateListLink("javascript://undefined", ['title' => 'Copy project code', 'data-copy' => '#domain-' . $i . '-code'], ['barcode'], 'Code') ?>
                                                        <?= generateListLink("javascript://undefined", ['title' => 'Copy real path', 'data-copy' => '#domain-' . $i . '-realpath'], ['folder'], 'Real path') ?>
                                                        <?= generateListLink("javascript://undefined", ['title' => 'Copy symlink path', 'data-copy' => '#domain-' . $i . '-symlink'], ['folder-o'], 'Symlink path') ?>
                                                        <? if (!empty($domain->path['repo'])): ?>
                                                            <?= generateListLink("javascript://undefined", ['title' => 'Copy repo path', 'data-copy' => '#domain-' . $i . '-repo'], ['code-fork'], 'Repo path') ?>
                                                        <? endif; ?>
                                                    </ul>
                                                </div>
                                                <? if (!empty($domain->note)): ?>
                                                    <div class="btn-group" role="group">
                                                        <?= generateLink('dropdown', ['title' => "Notes", 'class' => "btn btn-info btn-xs",], ['sticky-note-o']) ?>
                                                        <ul class="dropdown-menu">
                                                            <? foreach ($domain->note as $date => $note): ?>
                                                                <li class="dropdown-header">
                                                                    <span class="text text-default"><?= $note ?></span>
                                                                    <span class="badge"><?= $date ?></span>
                                                                </li>
                                                            <? endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <? endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <? endforeach ?>
                            </tbody>
                        </table>
                    <? endif; ?>
                </div>
            </div>
        </section>
        <? if (!empty($DOMAINS)): ?>
            <? foreach ($DOMAINS as $i => $domain): ?>
                <!--Context menu <?= $i ?>-->
                <div class="context-menu dropdown open" id="context-menu-id-<?= $i ?>">
                    <button class="btn btn-default dropdown-toggle" disabled>
                        <?= $domain->name ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">
                            <em class="fa fa-fw fa-globe"></em>
                            Domains
                        </li>
                        <?= generateListLink($domain->url, ['title' => "Local development page",], ['code'], 'Local') ?>
                        <?= generateListLink($domain->devUrl, ['title' => "Development page",], ['globe'], 'Development') ?>
                        <?= generateListLink($domain->stageUrl, ['title' => "Pre-production (stage) page",], ['globe'], 'Pre-production (stage)') ?>
                        <?= generateListLink($domain->liveUrl, ['title' => "Production (live) page",], ['industry'], 'Production (live)') ?>
                        <? if (!empty($domain->tools) || !empty($domain->repoUrl)): ?>
                            <li role="separator" class="divider"></li>
                            <li class="dropdown-header">
                                <em class="fa fa-fw fa-wrench"></em>
                                Tools
                            </li>
                            <?= generateListLink($domain->repoUrl, ['title' => "GIT repository",], ['code-fork'], 'GIT') ?>
                            <? foreach ($domain->tools as $tool): ?>
                                <?= generateListLink($tool->url, ['title' => $tool->title], $tool->icons, $tool->name) ?>
                            <? endforeach; ?>
                        <? endif; ?>
                        <? if (!empty($domain->info)): ?>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">
                            <em class="fa fa-fw fa-info-circle"></em>
                            Info
                            <? foreach ($domain->info as $info): ?>
                                <?= generateListLink("javascript://undefined", ['title' => 'Copy ' . $info->title, 'data-copy' => '#domain-' . $i . '-info-' . $info->code . ''], $info->icons, $info->name . ' <code>' . $info->value . '</code>') ?>
                            <? endforeach; ?>
                            <? endif; ?>
                        </li>
                    </ul>
                </div>
                <!--Copy input <?= $i ?>-->
                <span class="input">
                    <? if (!empty($domain->code)): ?>
                        <input id="domain-<?= $i ?>-code" value="s <?= $domain->code ?>">
                    <? endif; ?>
                    <input id="domain-<?= $i ?>-realpath" value="<?= $domain->path['real'] ?>">
                    <input id="domain-<?= $i ?>-symlink" value="<?= $domain->path['link'] ?>">
                    <? if (!empty($domain->path['repo'])): ?>
                        <input id="domain-<?= $i ?>-repo" value="git clone <?= $domain->path['repo'] ?> .">
                    <? endif; ?>
                    <? foreach ($domain->tools as $tool): ?>
                        <input id="domain-<?= $i ?>-tool-<?= $tool->code ?>" value="<?= $tool->url ?>">
                    <? endforeach; ?>
                    <? if (!empty($domain->info)): ?>
                        <? foreach ($domain->info as $info): ?>
                            <input id="domain-<?= $i ?>-info-<?= $info->code ?>" value="<?= $info->valueReal ?>">
                        <? endforeach; ?>
                    <? endif; ?>
                </span>
            <? endforeach; ?>
            <!--Copy time-->
            <span class="input">
                        <input id="current-time" value="0">
                    </span>
            <!--Modal howto-->
            <div class="modal fade" id="howto">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">
                                <em class="fa fa-fw fa-info-circle"></em>
                                Howto
                            </h4>
                        </div>
                        <div class="modal-body">
                            <ul type="1">
                                <li>Create project or clone project git repo in <code>./projects/</code> directory, eg: <code>./projects/{PROJECT_ID}/www/</code>.</li>
                                <li>Add project to <code>./symlinks.sh</code> script and run it, eg: <code>[example.com]=000_example/www</code>.</li>
                                <li>Create <code>DESCRIPTION</code> file in <code>./projects/{PROJECT_ID}/</code> directory with config options (buttons links):
                                    <pre><?= file_get_contents('DEFAULT.ini') ?></pre>
                                </li>
                                <? if (!$isXip): ?>
                                    <li>Reload this page, click <em class="fa fa-cog"> Hosts config</em> button, copy data and paste to Your local <code>/etc/hosts</code> file.</li>
                                <? endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <? if (!$isXip): ?>
                <!--Modal hostsConfig-->
                <div class="modal fade" id="hostsConfig">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">
                                    <em class="fa fa-fw fa-cog"></em>
                                    Hosts config
                                </h4>
                            </div>
                            <div class="modal-body">
                                Copy following code and paste into <code>/etc/hosts</code> file at Your local machine.
                                <div class="form-control" style="width: 100%;height: 400px; resize: none"><?= $_SERVER['SERVER_ADDR'] . "\t" . implode("\t", $HOSTS) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <? endif; ?>
        <? endif; ?>
        <script src="//code.jquery.com/jquery-<?= $jqueryVersion ?>.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/<?= $bootstrapVersion ?>/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-growl/1.0.0/jquery.bootstrap-growl.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/js/bootstrap-datepicker.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/locales/bootstrap-datepicker.pl.min.js"></script>
        <script src="dist/main.js"></script>
    </body>
</html>
