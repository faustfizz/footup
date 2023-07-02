<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Bienvenu | <?= frameworkName() ?> - <?= frameworkVersion() ?></title>
	<meta name="description" content="A Rich Featured LightWeight PHP MVC Framework">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/png" href="/favicon.svg"/>

	<!-- STYLES -->
	<?= css("style") ?>

</head>
<body>

<!-- HEADER: MENU + HEROE SECTION -->
<header>

	<div class="menu">
		<ul>
			<li class="logo">
				<a href="/" >
					<img height="44" title="logo" src="/favicon.svg" />
				</a>
			</li>
			<li class="menu-toggle">
				<button onclick="toggleMenu();">&#9776;</button>
			</li>
			<li class="menu-item hidden"><a href="<?= base_url('/') ?>">Home</a></li>
			<li class="menu-item hidden"><a href="<?= base_url('/docs') ?>">Docs API</a></li>
			<li class="menu-item hidden"><a href="https://github.io/faustfizz/footup" target="_blank">Github</a>
		</ul>
	</div>

	<div class="heroe">

		<h1><b><?= frameworkName() ?></b> - <b><?= frameworkVersion() ?></b></h1>

		<h2>A Rich Featured LightWeight PHP MVC Framework</h2>

	</div>

</header>

<!-- CONTENT -->

<section class="about">
	<div class="col">
		<h1>About this page</h1>
		<p>The page you are looking at is being generated dynamically by FootUp.</p>
	
		<p>If you would like to edit this page you will find it located at:</p>
	
		<pre><code>app/View/home.php</code></pre>
	
		<p>The corresponding controller for this page can be found at:</p>
	
		<pre><code><?= lcfirst(strtr(calledController(), ["\\" => DS])) ?>.php</code></pre>
	</div>
	<div class="col board">
		<p>You can create a controller like this using the command below :</p>

		<pre><code><span class="path">project_dir</span><span class="dollar">$</span> <span class="command">php footup make:controller ControllerName</span></code></pre>
	
		<p>To see all availables commands, use :</p>
	
		<pre><code><span class="path">project_dir</span><span class="dollar">$</span> <span class="command">php footup</span></code></pre>
	</div>

</section>

<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

<footer>
	<div class="environment">

		<p>Boot + Render time <b><?= request()->env('delayed_time') ?></b> seconds</p>

		<p>Environment: <b><?= ENVIRONMENT ?></b> - <?= "PHP ". PHP_VERSION ?></p>

	</div>

	<div class="copyrights">
		<p>&copy; <?= date('Y') ?> <b><?= frameworkName() ?></b>. FootUp is open source project released under the BSD-3 open source licence.</p>
	</div>

</footer>

<!-- SCRIPTS -->
<?= js("script") ?>

<!-- -->

</body>
</html>
