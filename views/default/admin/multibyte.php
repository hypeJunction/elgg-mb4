<div class="alert alert-warning">
    <p>In order to enable mutlibyte support, you have to run the following queries on your database. You can attempt to
       run them automatically via Elgg's Database driver, but it is likely that it will fail due insufficient
       privileges. Once you have run these queries, click Manual Upgrade to mark upgrade as complete.
    </p>
</div>

<hr />

<?php
$upgrade = new \Elgg\Upgrades\AlterDatabaseToMultiByteCharset();

echo elgg_view_field([
	'#type' => 'longtext',
	'rows' => 50,
	'value' => implode("\r\n", $upgrade->generateQueries()),
]);
?>

<hr />

<div>
	<?php
	echo elgg_view_menu('multibyte', [
        'items' => [
            [
                'name' => 'automatic',
                'href' => 'action/multibyte/automatic',
				'text' => 'Automatic Upgrade',
				'is_action' => true,
                'class' => 'elgg-button elgg-button-action',
            ],
			[
				'name' => 'manual',
				'href' => 'action/multibyte/manual',
				'text' => 'Manual Upgrade',
				'is_action' => true,
				'class' => 'elgg-button elgg-button-action',
			],
        ],
        'class' => 'elgg-menu-hz',
	]);
	?>
</div>