<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title><?=str_last($ex->class)?> | <?php echo frameworkName() ?> - <?php echo frameworkVersion() ?></title>
	<meta name="description" content="A Rich Featured LightWeight PHP MVC Framework">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" href="/favicon.svg"/>

	<!-- STYLES -->
    <style>
        <?php echo ouch_assets('css/spectre.min.css')?>
        <?php echo ouch_assets('css/enlighterjs.min.css')?>
        <?php echo ouch_assets('css/custom.min.css')?>
    </style>
    <script>
        <?php echo ouch_assets('js/enlighterjs.min.js')?>
    </script>
</head>
<body>
    <!-- HEADER: MENU + HEROE SECTION -->
    <header>

        <div class="manu">
            <ul>
                <li class="logo">
                    <a href="/" >
                        <img height="44" title="logo" src="/favicon.svg" />
                    </a>
                </li>
                <li class="manu-toggle">
                    <button onclick="toggleMenu();">&#9776;</button>
                </li>
                <li class="manu-item hidden"><a href="#">Home</a></li>
                <li class="manu-item hidden"><a href="https://github.io/faustfizz/footup" target="_blank">Github</a>
            </ul>
        </div>

        <div class="error">
            <div class="panel mb-10 bg-primary" style="padding-top:15px">
                <div class="panel-body" style="max-width:940px;margin:auto;width:100%;text-align:left">
                    <p class="exp-exp"><?=str_last($ex->class)?> <span style="color:black">| <?= frameworkName() .' - '. frameworkVersion() ?></span></p> 
                    <p><li style="display:list-item"><?=$ex->message?> <span class="copy" data-copy="<?= htmlentities($ex->message) ?>"  onclick="copy(this)">copy</span></li></p>
                </div>
            </div>
        </div>

    </header>

    <div class="container">
        <div class="columns">

            <!-- Exceptions column -->
            <div class="column col-xs-12 col-sm-12 col-md-3 mt-10 exp-column pr-10" id="exp-column">

                <div class="panel exp-panel active" id="exp-0">
                    <div class="panel-body">
                        <code><?= htmlentities(readErrorLine($ex->file, $ex->line), ENT_QUOTES, 'UTF-8') ?></code>
                        <p><?= strtr($ex->file, [ROOT_PATH => basename(dirname(BASE_PATH))."/"]) ?></p>
                    </div>
                </div>

                <?php $i = 1; foreach ($ex->trace as $tr) { ?>

                    <?php if (array_key_exists('file', $tr)): ?>
                        <div class="panel exp-panel" id="exp-<?=$i?>">
                            <div class="panel-body">
                                <code><?= htmlentities(readErrorLine($tr['file'], $tr['line']), ENT_QUOTES, 'UTF-8') ?></code>
                                <p><?= strtr($tr['file'], [ROOT_PATH => basename(dirname(BASE_PATH))."/"])?></p>
                            </div>
                        </div>
                    <?php endif;?>

                <?php $i++; } ?>

            </div>

            <!-- editor's column -->
            <div class="column col-xs-12 col-sm-12 col-md-6 mt-10">
                <div class="docs-demo columns">
                    <div class="column">
                        <!-- error title was here -->

                        <!-- editor -->
                        <div class="hero hero-sm show-hero" id="hero-exp-0">
                            <div class="hero-body">

                                <span class="line"> <?= strtr($ex->file, [ROOT_PATH => basename(dirname(BASE_PATH))."/"]) ?> <span><?=$ex->line?></span></span>

                                <pre data-enlighter-language="php" data-enlighter-highlight="<?=$ex->line?>"

                                data-enlighter-lineoffset="<?= $ex->line > 6 ? $ex->line - 6 : $ex->line ?>">
                                <?= readErrorFile($ex->file, $ex->line) ?>
                                </pre>

                                <!-- debugging helpers -->
                                <div class="help-links">
                                    <a href='https://stackoverflow.com/search?q=PHP <?=$ex->message?>' target="_blank" title="open in stackoverflow">
                                        <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Stackoverflow</title><path d='M392 440V320h40v160H64V320h40v120z'/><path d='M149.1 308.77l198.57 40.87 8.4-39.32-198.57-40.87zm26.27-93.12L359.22 300 376 263.76l-183.82-84.84zm50.95-89l156 127.78 25.74-30.52-156-127.78zM328 32l-33.39 23.8 120.82 160.37L448 192zM144 400h204v-40H144z'/></svg>
                                    </a>
                                    <a href='https://www.reddit.com/search?q=PHP <?=$ex->message?>' target="_blank" title="open in reddit">
                                        <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Reddit</title><path d='M324 256a36 36 0 1036 36 36 36 0 00-36-36z'/><circle cx='188' cy='292' r='36' transform='rotate(-22.5 187.997 291.992)'/><path d='M496 253.77c0-31.19-25.14-56.56-56-56.56a55.72 55.72 0 00-35.61 12.86c-35-23.77-80.78-38.32-129.65-41.27l22-79 66.41 13.2c1.9 26.48 24 47.49 50.65 47.49 28 0 50.78-23 50.78-51.21S441 48 413 48c-19.53 0-36.31 11.19-44.85 28.77l-90-17.89-31.1 109.52-4.63.13c-50.63 2.21-98.34 16.93-134.77 41.53A55.38 55.38 0 0072 197.21c-30.89 0-56 25.37-56 56.56a56.43 56.43 0 0028.11 49.06 98.65 98.65 0 00-.89 13.34c.11 39.74 22.49 77 63 105C146.36 448.77 199.51 464 256 464s109.76-15.23 149.83-42.89c40.53-28 62.85-65.27 62.85-105.06a109.32 109.32 0 00-.84-13.3A56.32 56.32 0 00496 253.77zM414 75a24 24 0 11-24 24 24 24 0 0124-24zM42.72 253.77a29.6 29.6 0 0129.42-29.71 29 29 0 0113.62 3.43c-15.5 14.41-26.93 30.41-34.07 47.68a30.23 30.23 0 01-8.97-21.4zM390.82 399c-35.74 24.59-83.6 38.14-134.77 38.14S157 423.61 121.29 399c-33-22.79-51.24-52.26-51.24-83A78.5 78.5 0 0175 288.72c5.68-15.74 16.16-30.48 31.15-43.79a155.17 155.17 0 0114.76-11.53l.3-.21.24-.17c35.72-24.52 83.52-38 134.61-38s98.9 13.51 134.62 38l.23.17.34.25A156.57 156.57 0 01406 244.92c15 13.32 25.48 28.05 31.16 43.81a85.44 85.44 0 014.31 17.67 77.29 77.29 0 01.6 9.65c-.01 30.72-18.21 60.19-51.25 82.95zm69.6-123.92c-7.13-17.28-18.56-33.29-34.07-47.72A29.09 29.09 0 01440 224a29.59 29.59 0 0129.41 29.71 30.07 30.07 0 01-8.99 21.39z'/><path d='M323.23 362.22c-.25.25-25.56 26.07-67.15 26.27-42-.2-66.28-25.23-67.31-26.27a4.14 4.14 0 00-5.83 0l-13.7 13.47a4.15 4.15 0 000 5.89c3.4 3.4 34.7 34.23 86.78 34.45 51.94-.22 83.38-31.05 86.78-34.45a4.16 4.16 0 000-5.9l-13.71-13.47a4.13 4.13 0 00-5.81 0z'/></svg>
                                    </a>
                                    <a href='https://www.google.dz/search?q=PHP <?=$ex->message?>' target="_blank" title="open in google">
                                        <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Google</title><path d='M473.16 221.48l-2.26-9.59H262.46v88.22H387c-12.93 61.4-72.93 93.72-121.94 93.72-35.66 0-73.25-15-98.13-39.11a140.08 140.08 0 01-41.8-98.88c0-37.16 16.7-74.33 41-98.78s61-38.13 97.49-38.13c41.79 0 71.74 22.19 82.94 32.31l62.69-62.36C390.86 72.72 340.34 32 261.6 32c-60.75 0-119 23.27-161.58 65.71C58 139.5 36.25 199.93 36.25 256s20.58 113.48 61.3 155.6c43.51 44.92 105.13 68.4 168.58 68.4 57.73 0 112.45-22.62 151.45-63.66 38.34-40.4 58.17-96.3 58.17-154.9 0-24.67-2.48-39.32-2.59-39.96z'/></svg>
                                    </a>
                                </div>
                                <div class="clear-fix"></div>
                            </div>
                        </div>

                        <?php $i = 1; foreach ($ex->trace as $tr) { ?>

                            <?php if (array_key_exists('file', $tr)) { ?>
                                <!-- editor -->
                                <div class="hero hero-sm" id="hero-exp-<?=$i?>">
                                    <div class="hero-body">
                                        <span class="line"> <?=$tr['file']?> <span><?=$tr['line']?></span></span>
                                        <pre data-enlighter-language="php" data-enlighter-highlight="<?=$tr['line']?>"
                                        data-enlighter-lineoffset="<?= $tr['line'] > 6 ? $tr['line'] - 6 : $tr['line'] ?>"><?=readErrorFile($tr['file'], $tr['line'])?></pre>
                                    </div>

                                    <!-- debugging helpers -->
                                    <div class="help-links">
                                        <a href='https://stackoverflow.com/search?q=PHP <?=$ex->message?>' target="_blank" title="open in stackoverflow">
                                            <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Stackoverflow</title><path d='M392 440V320h40v160H64V320h40v120z'/><path d='M149.1 308.77l198.57 40.87 8.4-39.32-198.57-40.87zm26.27-93.12L359.22 300 376 263.76l-183.82-84.84zm50.95-89l156 127.78 25.74-30.52-156-127.78zM328 32l-33.39 23.8 120.82 160.37L448 192zM144 400h204v-40H144z'/></svg>
                                        </a>
                                        <a href='https://www.reddit.com/search?q=PHP <?=$ex->message?>' target="_blank" title="open in reddit">
                                            <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Reddit</title><path d='M324 256a36 36 0 1036 36 36 36 0 00-36-36z'/><circle cx='188' cy='292' r='36' transform='rotate(-22.5 187.997 291.992)'/><path d='M496 253.77c0-31.19-25.14-56.56-56-56.56a55.72 55.72 0 00-35.61 12.86c-35-23.77-80.78-38.32-129.65-41.27l22-79 66.41 13.2c1.9 26.48 24 47.49 50.65 47.49 28 0 50.78-23 50.78-51.21S441 48 413 48c-19.53 0-36.31 11.19-44.85 28.77l-90-17.89-31.1 109.52-4.63.13c-50.63 2.21-98.34 16.93-134.77 41.53A55.38 55.38 0 0072 197.21c-30.89 0-56 25.37-56 56.56a56.43 56.43 0 0028.11 49.06 98.65 98.65 0 00-.89 13.34c.11 39.74 22.49 77 63 105C146.36 448.77 199.51 464 256 464s109.76-15.23 149.83-42.89c40.53-28 62.85-65.27 62.85-105.06a109.32 109.32 0 00-.84-13.3A56.32 56.32 0 00496 253.77zM414 75a24 24 0 11-24 24 24 24 0 0124-24zM42.72 253.77a29.6 29.6 0 0129.42-29.71 29 29 0 0113.62 3.43c-15.5 14.41-26.93 30.41-34.07 47.68a30.23 30.23 0 01-8.97-21.4zM390.82 399c-35.74 24.59-83.6 38.14-134.77 38.14S157 423.61 121.29 399c-33-22.79-51.24-52.26-51.24-83A78.5 78.5 0 0175 288.72c5.68-15.74 16.16-30.48 31.15-43.79a155.17 155.17 0 0114.76-11.53l.3-.21.24-.17c35.72-24.52 83.52-38 134.61-38s98.9 13.51 134.62 38l.23.17.34.25A156.57 156.57 0 01406 244.92c15 13.32 25.48 28.05 31.16 43.81a85.44 85.44 0 014.31 17.67 77.29 77.29 0 01.6 9.65c-.01 30.72-18.21 60.19-51.25 82.95zm69.6-123.92c-7.13-17.28-18.56-33.29-34.07-47.72A29.09 29.09 0 01440 224a29.59 29.59 0 0129.41 29.71 30.07 30.07 0 01-8.99 21.39z'/><path d='M323.23 362.22c-.25.25-25.56 26.07-67.15 26.27-42-.2-66.28-25.23-67.31-26.27a4.14 4.14 0 00-5.83 0l-13.7 13.47a4.15 4.15 0 000 5.89c3.4 3.4 34.7 34.23 86.78 34.45 51.94-.22 83.38-31.05 86.78-34.45a4.16 4.16 0 000-5.9l-13.71-13.47a4.13 4.13 0 00-5.81 0z'/></svg>
                                        </a>
                                        <a href='https://www.google.dz/search?q=PHP <?=$ex->message?>' target="_blank" title="open in google">
                                            <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" fill="#e36058" width="26px" height="26px" viewBox='0 0 512 512'><title>Logo Google</title><path d='M473.16 221.48l-2.26-9.59H262.46v88.22H387c-12.93 61.4-72.93 93.72-121.94 93.72-35.66 0-73.25-15-98.13-39.11a140.08 140.08 0 01-41.8-98.88c0-37.16 16.7-74.33 41-98.78s61-38.13 97.49-38.13c41.79 0 71.74 22.19 82.94 32.31l62.69-62.36C390.86 72.72 340.34 32 261.6 32c-60.75 0-119 23.27-161.58 65.71C58 139.5 36.25 199.93 36.25 256s20.58 113.48 61.3 155.6c43.51 44.92 105.13 68.4 168.58 68.4 57.73 0 112.45-22.62 151.45-63.66 38.34-40.4 58.17-96.3 58.17-154.9 0-24.67-2.48-39.32-2.59-39.96z'/></svg>
                                        </a>
                                    </div>
                                    <div class="clear-fix"></div>
                                </div>
                            <?php }?>
                        <?php $i++; }?>

                    </div>
                </div>
            </div>


            <!-- data column -->
            <div class="column col-xs-12 col-sm-12 col-md-3 mt-10" id="server">

                <ul class="tab tab-block" id="tab">
                    <li id="tab-1" class="tab-item active">
                        <a href="#" class="badge" data-badge="<?=count(getallheaders())?>"><svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" width="26px" height="26px" viewBox='0 0 512 512'><title>Globe</title><path d='M256 48C141.13 48 48 141.13 48 256s93.13 208 208 208 208-93.13 208-208S370.87 48 256 48z' stroke-miterlimit='10' stroke-width='24' stroke="currentColor" fill="none"/><path d='M256 48c-58.07 0-112.67 93.13-112.67 208S197.93 464 256 464s112.67-93.13 112.67-208S314.07 48 256 48z' stroke-miterlimit='10' stroke-width='24' stroke="currentColor" fill="none"/><path d='M117.33 121.33c38.24 27.15 86.38 43.34 138.67 43.34s100.43-16.19 138.67-43.34M394.67 390.67c-38.24-27.15-86.38-43.34-138.67-43.34s-100.43 16.19-138.67 43.34' stroke-linecap='round' stroke-linejoin='round' stroke-width='24' stroke="currentColor" fill="none"/><path stroke-miterlimit='10' stroke-width='24' stroke="currentColor" d='M256 48v416M464 256H48' fill="none"/></svg> Request </a>
                    </li>
                    <li id="tab-2" class="tab-item">
                        <a href="#" class="badge" data-badge="<?=count($_SERVER)?>"><svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" width="26px" height="26px" viewBox='0 0 512 512'><title>Server</title><ellipse cx='256' cy='128' rx='192' ry='80' stroke-linecap='round' stroke-miterlimit='10' fill="none" stroke-width="15" stroke="currentColor" /><path d='M448 214c0 44.18-86 80-192 80S64 258.18 64 214M448 300c0 44.18-86 80-192 80S64 344.18 64 300' stroke-linecap='round' stroke-miterlimit='10' fill="none" stroke-width="15" stroke="currentColor" /><path d='M64 127.24v257.52C64 428.52 150 464 256 464s192-35.48 192-79.24V127.24' stroke-linecap='round' stroke-miterlimit='10' fill="none" stroke-width="15" stroke="currentColor" /></svg> Server  </a>
                    </li>
                    <li id="tab-3" class="tab-item">
                        <a href="#"> <svg xmlns='http://www.w3.org/2000/svg' style="vertical-align: bottom" width="26px" height="26px" fill="currentColor" viewBox='0 0 512 512'><title>Person</title><path d='M332.64 64.58C313.18 43.57 286 32 256 32c-30.16 0-57.43 11.5-76.8 32.38-19.58 21.11-29.12 49.8-26.88 80.78C156.76 206.28 203.27 256 256 256s99.16-49.71 103.67-110.82c2.27-30.7-7.33-59.33-27.03-80.6zM432 480H80a31 31 0 01-24.2-11.13c-6.5-7.77-9.12-18.38-7.18-29.11C57.06 392.94 83.4 353.61 124.8 326c36.78-24.51 83.37-38 131.2-38s94.42 13.5 131.2 38c41.4 27.6 67.74 66.93 76.18 113.75 1.94 10.73-.68 21.34-7.18 29.11A31 31 0 01432 480z'/></svg> User</a>
                    </li>
                </ul>

                <!-- tab one -->
                <div id="tab-1-block" class="tab-content show">

                    <!-- divider element with text -->
                    <?php if (!empty($_GET)): ?>
                        <div class="divider text-center" data-content="GET"></div>
                        <div class="dumper">
                            <?php print_r($_GET); ?>
                        </div>
                    <?php endif ?>

                    <!-- divider element with text -->
                    <?php if (!empty($_POST)): ?>
                        <div class="divider text-center" data-content="POST"></div>
                        <div class="dumper">
                            <?php print_r($_POST); ?>
                        </div>
                    <?php endif ?>

                    <!-- divider element with text -->
                    <?php if (!empty($_SESSION)): ?>
                        <div class="divider text-center" data-content="SESSION"></div>
                        <div class="dumper">
                            <?php print_r($_SESSION); ?>
                        </div>
                    <?php endif ?>

                    <!-- divider element with text -->
                    <?php if (!empty($_COOKIE)): ?>
                        <div class="divider text-center" data-content="COOKIES"></div>
                        <div class="dumper">
                            <?php print_r($_COOKIE); ?>
                        </div>
                    <?php endif ?>


                    <!-- divider element with text -->
                    <div class="divider text-center" data-content="HEADERS"></div>
                    <table class="table table-striped table-hover">
                        <tbody>
                            <?php foreach (getallheaders() as $key => $val) { ?>
                                <tr>
                                    <!-- adding tooltip for log keys -->
                                    <?php if (strlen($key) > 15) { ?>
                                        <td><b class="tooltip" data-tooltip="<?=$key?>"><?=substr($key, 0, 15)?>...</b></td>
                                        <td><span><?=$val?></span></td>
                                    <?php continue; }?>

                                    <td><b><?=$key?></b></td>
                                    <td><span><?=$val?></span></td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>

                </div>
                <!-- end tab one -->

                <!--  tab two -->
                <div id="tab-2-block" class="tab-content">
                    <!-- divider element with text -->
                    <div class="divider text-center" data-content="SERVER DATA"></div>
                    <table class="table table-striped table-hover">
                        <tbody>

                        <?php foreach ($_SERVER as $key => $val) { ?>
                            <tr>
                                <!-- adding tooltip for log keys -->
                                <?php if (strlen($key) > 15) { ?>
                                    <td><b class="tooltip" data-tooltip="<?=$key?>"><?=substr($key, 0, 15)?>...</b></td>
                                    <td><span><?=$val?></span></td>
                                <?php continue; }?>
                                <td><b><?=$key?></b></td>
                                <td><span><?=$val?></span></td>
                            </tr>
                        <?php }?>
                        </tbody>
                    </table>

                    <!-- divider element with text -->
                    <div class="divider text-center" data-content="ARGS"></div>
                    <div class="dumper">
                        <?php global $argv; !empty($argv) ? var_dump($argv ?? []) : print("--- EMPTY ---"); ?>
                    </div>
                </div>
                <!-- end tab two -->
                
                <!--  tab three -->
                <div id="tab-3-block" class="tab-content">
                    
                </div>
                <!-- end tab two -->

            </div>
        </div>
    </div>
    
    <!-- FOOTER: DEBUG INFO + COPYRIGHTS -->
    <footer>
        <div class="environment">

            <p>Boot + Render time <b><?php echo request()->env('delayed_time') ?></b> seconds</p>

            <p>Environment: <b><?php echo ENVIRONMENT ?></b> - <?php echo "PHP ". PHP_VERSION ?></p>

        </div>

        <div class="copyright">
            <p>&copy; <?php echo date('Y') ?> <b><?php echo frameworkName() ?></b>. FootUp is open source project released under the BSD-3 open source licence.</p>
        </div>

    </footer>

    <script>
        EnlighterJS.init('pre', "code", {
            language : 'php',
            theme : 'beyond',
            title: <?= json_encode($ex->message) ?>
        });
        <?php echo ouch_assets('js/custom.min.js')?>
    </script>

</body>
</html>