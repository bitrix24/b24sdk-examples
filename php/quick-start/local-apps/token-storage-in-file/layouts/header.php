<?php
declare(strict_types=1);

$title = 'Local app with token storage';

$menu = json_decode(json_encode([
	[
		'title' => 'API Docs',
		'href' => 'https://apidocs.bitrix24.com'
	],
	[
		'title' => '@bitrix24/b24phpsdk',
		'href' => 'https://github.com/bitrix24/b24phpsdk'
	],
	[
		'title' => '@bitrix24/b24jssdk',
		'href' => 'https://bitrix24.github.io/b24jssdk/'
	],
	[
		'title' => '@bitrix24/b24style',
		'href' => 'https://bitrix24.github.io/b24style/reference/colors.html'
	],
	[
		'title' => '@bitrix24/b24icons',
		'href' => 'https://bitrix24.github.io/b24icons/guide/icons.html'
	]
]));

?><!doctype html>
<html lang="en" dir="ltr">
<head>
	<meta charset="UTF-8">
	<link rel="icon" href="./css/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="./css/app.css" rel="stylesheet">
	<title>B24PhpSdk Local app with token storage</title>
</head>
<body class="bg-tertiary font-b24-system text-base-900 antialiased">
<div class="flex h-screen flex-col overflow-hidden">
	<header class="relative z-10 border-b border-gray-200 bg-white">
		<div class="px-6 py-4">
			<div class="flex flex-shrink-0 items-center justify-between gap-6">
				<div class="text-h2 text-nowrap truncate"><?=$title?></div>
				<div class="hidden sm:flex sm:flex-row gap-2 text-sm text-base-500 divide-x divide-base-400 ">
					<?php foreach($menu as $key => $menuItem):?>
						<a
							href="<?=$menuItem->href?>"
							target="_blank"
							class="whitespace-nowrap pl-2 hover:text-info-text hover:underline"
						><?=$menuItem->title?></a>
					<?php endforeach;?>
				</div>
			</div>
		</div>
	</header>
	<main class="flex-auto overflow-auto px-6">