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
//die('asd');
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
        if(file_exists($symlinkPath . '/web/app_dev.php')){
            $baseurl = '/app_dev.php';
        }elseif(file_exists($symlinkPath . '/web/wp-config.php')){
            $baseurl = ':81';
        }elseif(file_exists($symlinkPath . '/web/')){
            $baseurl = '/';
        }else{
            $baseurl = ':81';
        }

        # last modification time from repo
        $mtime = file_exists($symlinkPath . '/.git/') ? filemtime($symlinkPath . '/.git/') : filemtime($symlinkPath);

        # meta data
        $data['name'] = $externalDomain;
        $data['code'] = basename(dirname($realPath));
        $data['client_id'] = preg_match("'^([0-9]{3})_(.*)$'", $data['code']) ? preg_replace("'^([0-9]{3})_(.*)$'", "$1.", $data['code']) : null;
        $data['job_id'] = preg_match("'^([0-9]{3})_([0-9]{3})_(.*)$'", $data['code']) ? preg_replace("'^([0-9]{3})_([0-9]{3})_(.*)$'", "$1.$2", $data['code']) : null;
        $data['date'] = new \DateTime('@' . $mtime);
        $data['current'] = ($data['date']->diff(new \DateTime())->days > 7) ? false : true;

        $CLIENTS[] = $data['client_id'];

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
            dirname($realPath) . '/DESCRIPTION',
            dirname($realPath) . '/DESCRIPTION-' . $externalDomain,
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
                    unset($config['tools']);
                }
                if (isset($config['code'])) {
                    $data['code'] = $config['code'];
                }
                if (isset($config['tools'])) {
                    foreach($config['tools'] as $key => $url){
                        $data['tools'][$key] = (object)[];
                        if(isset($toolList[$key])){
                           $data['tools'][$key] = clone $toolList[$key];
                        }else{
                            $data['tools'][$key] = (object)array(
                                'title'=>$key,
                                'name'=>$key,
                            );
                        }
                       $data['tools'][$key]->url = $url;
                    }
                    sort($data['tools']);
                }
                if (isset($config['tags'])) {
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
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/<?= $fontawesomeVersion ?>/css/font-awesome.min.css">
        <style type="text/css">
            .bootstrap-table .search .close {
                position: absolute;
                top: 10px;
                right: 10px;
            }

            .navbar .nav li.dropdown {
                position: relative;
            }

            .navbar .nav li.dropdown .dropdown-menu {
                max-height: calc(100vh - 50px);
                overflow: auto;
            }

            a[data-copy] {
                cursor: copy;
            }
            .input input {
                position:absolute;
                top:-10000px;
            }

            .tags a:not(:last-child):after {
                content: ',';
            }
            .tags {
                font-size: 0.8em;
            }

            .date {
                font-size: 0.8em;
                width: 100px;
            }
            .links {
                width: 273px;
            }

            .fixed-table-body {
                overflow: unset;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/">
                        <span class="glyphicon glyphicon-fire"></span>
                        VServer
                    </a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="fa fa-fw fa-bars"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <? if (!empty($CLIENTS) || !empty($TAGS)): ?>
                        <ul class="nav navbar-nav">
                            <? if (!empty($CLIENTS)): ?>
                                <li class="dropdown">
                                    <a href="javascript://undefined" title="clients list" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <span class="fa fa-fw fa-building-o"></span>
                                        <span class="text">Clients</span>
                                    </a>
                                    <ul class="dropdown-menu tags">
                                        <? foreach ($CLIENTS as $clientId): ?>
                                            <li>
                                                <a data-value="<?= $clientId ?>" href="javascript://undefined">
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
                                        <span class="fa fa-fw fa-tags"></span>
                                        <span class="text">Tags</span>
                                    </a>
                                    <ul class="dropdown-menu tags">
                                        <? foreach ($TAGS as $tag): ?>
                                            <li>
                                                <a data-value="<?= $tag ?>" href="javascript://undefined">
                                                    <?= $tag ?>
                                                    <? if (isset($tagIcons[$tag])): ?>
                                                        <em class="fa fa-fw fa-<?= $tagIcons[$tag] ?>"></em>
                                                    <? endif; ?>
                                                </a>
                                            </li>
                                        <? endforeach; ?>
                                    </ul>
                                </li>
                            <? endif; ?>
                        </ul>
                    <? endif; ?>
                    <ul class="nav navbar-nav navbar-right">
                        <? if (!empty($phpMyAdminURL)): ?>
                            <li>
                                <a href="<?= $phpMyAdminURL ?>" title="phpMyAdmin">
                                    <em class="fa fa-fw fa-database"></em>
                                        <span class="hidden-sm hidden-md hidden-lg">
                                            phpMyAdmin
                                            <small class="fa fa-external-link"></small>
                                        </span>
                                </a>
                            </li>
                        <? endif; ?>
                        <li>
                            <a data-toggle="modal" href="#howto" title="Howto info">
                                <span class="fa fa-fw fa-info-circle"></span>
                                <span class="hidden-sm hidden-md hidden-lg">Howto</span>
                            </a>
                        </li>
                        <? if (!empty($DOMAINS)): ?>
                            <li>
                                <a data-toggle="modal" href="#hostsConfig" title="Hosts config info">
                                    <span class="fa fa-fw fa-cog"></span>
                                    <span class="hidden-sm hidden-md hidden-lg">Hosts</span>
                                </a>
                            </li>
                        <? endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <section class="container">
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
                                    <th data-field="name" data-sortable="true">
                                        <em class="fa fa-file-o"></em> Name
                                    </th>
                                    <? if (isset($_GET['action'])): ?>
                                        <th>
                                            <em class="fa fa-fw fa-terminal"></em> Command
                                        </th>
                                    <? else: ?>
                                        <th style="width:140px;">
                                            <em class="fa fa-fw fa-tags"></em> Tags
                                        </th>
                                        <th data-field="date" data-sortable="true" style="width:140px;">
                                            <em class="fa fa-fw fa-calendar"></em> Date
                                        </th>
                                        <th style="width:140px;">
                                            <em class="fa fa-fw fa-link"></em> Links
                                        </th>
                                    <? endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <? foreach ($DOMAINS as $i => $domain): ?>
                                    <tr id="tr-id-<?= $i ?>" class="tr-class-<?= $i ?>">
                                        <td class="<?= $domain->current ? 'lead' : '' ?>">
                                            <a href="<?= $domain->url ?>">
                                                <?= $domain->name ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="tags">
                                                <a data-copy="#domain-<?= $i ?>-code" href="javascript://undefined">[<?= $domain->code ?>]</a>
                                                <? if (!empty($domain->client_id) && !empty($domain->job_id)): ?>
                                                    <? if (isset($clientNames[$domain->client_id])): ?>
                                                        <a data-value="<?= $domain->client_id ?>" href="javascript://undefined"><strong><?= $clientNames[$domain->client_id] ?></strong> (#<?= $domain->job_id ?>)</a>
                                                    <? else: ?>
                                                        <a data-value="<?= $domain->client_id ?>" href="javascript://undefined"></a>
                                                    <? endif; ?>
                                                <? endif; ?>
                                                <? if (!empty($domain->tags)): ?>
                                                    <? foreach ($domain->tags as $tag): ?>
                                                        <a data-value="<?= $tag ?>" href="javascript://undefined"><?= $tag ?></a>
                                                    <? endforeach; ?>
                                                <? endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date">
                                                <span class="hidden"><?= $domain->date->format('Y-m-d H:i:s') ?></span>
                                                <?= $idf->format($domain->date) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group links">
                                                <a class="btn btn-primary btn-xs" href="<?= $domain->url ?>" title="Local development page">
                                                    <em class="fa fa-fw fa-code"></em>
                                                </a>
                                                <? if (!empty($domain->devUrl)): ?>
                                                    <a class="btn btn-warning btn-xs" href="<?= $domain->devUrl ?>" title="Development page">
                                                        <em class="fa fa-globe"> Dev</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->stageUrl)): ?>
                                                    <a class="btn btn-warning btn-xs" href="<?= $domain->stageUrl ?>" title="Stage page">
                                                        <em class="fa fa-globe"> Stage</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->liveUrl)): ?>
                                                    <a class="btn btn-success btn-xs" href="<?= $domain->liveUrl ?>" title="Live page">
                                                        <em class="fa fa-globe"> Live</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->tools)): ?>
                                                    <div class="btn-group" role="group">
                                                        <a href="javascript://undefined" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="External tools">
                                                            <em class="fa fa-wrench"> Tools</em>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                             <? if (!empty($domain->repoUrl)): ?>
                                                                <li>
                                                                    <a href="<?= $domain->repoUrl ?>" title="GIT repository">
                                                                        <em class="fa fa-fw fa-code-fork"></em>
                                                                        GIT
                                                                    </a>
                                                                </li>
                                                            <? endif; ?>
                                                            <? foreach ($domain->tools as $tool): ?>
                                                                <li>
                                                                    <a href="<?= $tool->url ?>" title="<?= $tool->title?>">
                                                                        <? if(!empty($tool->icon)): ?>
                                                                            <em class="fa fa-fw fa-<?= $tool->icon?>"></em>
                                                                        <? endif; ?>
                                                                        <?= $tool->name?>
                                                                    </a>
                                                                <li>
                                                            <? endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <? endif; ?>
                                                <div class="btn-group" role="group">
                                                    <a href="javascript://undefined" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="External tools">
                                                        <em class="fa fa-copy"> Copy</em>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a href="javascript://undefined" data-copy="#domain-<?= $i ?>-code" title="Copy project code">
                                                                <em class="fa fa-fw fa-barcode"></em> Code
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript://undefined" data-copy="#domain-<?= $i ?>-realpath" title="Copy real path">
                                                                <em class="fa fa-fw fa-folder"></em> Real path
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript://undefined" data-copy="#domain-<?= $i ?>-symlink" title="Copy symlink path">
                                                                <em class="fa fa-fw fa-folder-o"></em> Symlink path
                                                            </a>
                                                        </li>
                                                        <? if(!empty($domain->path['repo'])): ?>
                                                        <li>
                                                            <a href="javascript://undefined" data-copy="#domain-<?= $i ?>-repo" title="Copy repo path">
                                                                <em class="fa fa-fw fa-code-fork"></em> Repo path
                                                            </a>
                                                        </li>
                                                        <? endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <span class="input">
                                                <input id="domain-<?= $i ?>-code" value="s <?= $domain->code ?>">
                                                <input id="domain-<?= $i ?>-realpath" value="<?= $domain->path['real']?>">
                                                <input id="domain-<?= $i ?>-symlink" value="<?= $domain->path['link']?>">
                                                <? if(!empty($domain->path['repo'])): ?>
                                                    <input id="domain-<?= $i ?>-repo" value="git clone <?= $domain->path['repo']?> .">
                                                <? endif; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <? endforeach ?>
                            </tbody>
                        </table>
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
                                                <pre>
code            =   project code
tags            =   tag1,tag2,tag3

[domains]
local           =   
dev             =   
stage           =   
prod            =   

[tools]
repo            =   
pma             =   
crm             =   
task            =   
                                                </pre>
                                            </li>
                                            <li>Reload this page, click <em class="fa fa-cog"> Hosts config</em> button, copy data and paste to Your local <code>/etc/hosts</code> file.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                        Copy following code and paste into <code>/etc/hosts</code> file.
                                        <div class="form-control" style="width: 100%;height: 400px; resize: none"><?= $_SERVER['SERVER_ADDR'] . "\t" . implode("\t", $HOSTS) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </section>
        <script src="//code.jquery.com/jquery-<?= $jqueryVersion ?>.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/<?= $bootstrapVersion ?>/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-growl/1.0.0/jquery.bootstrap-growl.min.js"></script>
        <script type="text/javascript">
            function tagFilter(tag) {
                $('.bootstrap-table .search input').val(tag).trigger('drop');
            }
            var tag = decodeURIComponent(window.location.hash.replace('#', '').trim());
            $(function () {
                if (tag) {
                    tagFilter(tag);
                }
            });
            $(document)
                .on('click', 'a[data-copy]', function (e) {
                    element = $(this).data('copy');
                    $element = $(element);
                    $element.focus().select();
                    var msg, type;
                    try {
                        var successful = document.execCommand('copy');
                        msg = (successful ? ('Copy data success: <b>' + $element.val() + '</b>') : 'Sorry, can\'t copy data.');
                        type = successful ? 'success' : 'danger';
                    } catch (err) {
                        msg = 'Oops, unable to copy.';
                        type = 'info';
                    }
                    $.bootstrapGrowl(msg, {
                        type: type
                    });
                })
                .on('click', '.tags a[data-value]', function () {
                    var tag = $(this).data('value');
                    tagFilter(tag);
                })
                .on('click', '.bootstrap-table .search .close', function () {
                    tagFilter('');
                })
                .on('keyup drop', '.bootstrap-table .search input', function () {
                    var tag = $(this).val().trim();
                    $('li.active').removeClass('active');
                    if (tag) {
                        $('.tags li [data-value="' + tag + '"]').closest('.tags').closest('li').addClass('active');
                        $('.tags li [data-value="' + tag + '"]').closest('li').addClass('active');
                        window.location.hash = tag;
                        if ($('.bootstrap-table .search .close').length === 0) {
                            $('.bootstrap-table .search input').after($('<a>').addClass('fa fa-times close').attr('href', 'javascript://undefined'));
                        }
                    } else if (!tag) {
                        window.location.hash = "";
                        $('.bootstrap-table .search .close').remove();
                    }
                });
        </script>
    </body>
</html>
