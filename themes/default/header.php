<?php

/**
 * 模板片段：站点头部
 * 作用：输出 HTML 头部、SEO/OG 元信息、全站导航与语言切换入口。
 * 变量：$site（站点设置）、$seo（SEO信息）、$currentTheme（主题名）、$languages/$lang（语言配置）。
 * 注意：由布局模板自动引入，不应独立渲染。
 */

// 获取菜单项（如果没有设置，将使用默认菜单）
$menuItems = get_menu_items('main-nav');
$hasMenu = !empty($menuItems);
$seoTitle = trim((string)($seo['title'] ?? $site['name'] ?? ''));
$seoDescription = default_theme_meta_description((string)($seo['description'] ?? $site['tagline'] ?? ''));
$canonicalUrl = (string)($seo['canonical'] ?? base_url());
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = base_path();
if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}
$headerSearchQuery = $currentPath === '/search'
    ? mb_substr(trim((string)($_GET['q'] ?? '')), 0, 120)
    : '';
$searchPlaceholder = block('header', 'search_placeholder', 'Search products, articles and cases');

$seoContext = [
    'item' => $item ?? null,
    'images' => $images ?? [],
    'price_mode' => $price_mode ?? ($item['price_mode'] ?? 'tier'),
    'price_tiers' => $price_tiers ?? [],
    'product_skus' => $product_skus ?? [],
    'cover' => is_array($item ?? null) ? (string)($item['cover'] ?? '') : '',
];
if (str_starts_with($currentPath, '/product/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'product';
} elseif (str_starts_with($currentPath, '/blog/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'article';
} elseif (str_starts_with($currentPath, '/case/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'case';
} elseif (str_starts_with($currentPath, '/page/') && !empty($seoContext['item'])) {
    $seoContext['type'] = 'page';
} else {
    $seoContext['type'] = 'website';
}
$metaImage = default_theme_meta_image($site, $seo, $seoContext);
$ogType = match ($seoContext['type']) {
    'product' => 'product',
    'article', 'case', 'page' => 'article',
    default => 'website',
};
$schemaGraph = default_theme_schema_graph($site, [
    'title' => $seoTitle,
    'description' => $seoDescription,
    'canonical' => $canonicalUrl,
    'image' => $metaImage,
], $seoContext);
?>
<!DOCTYPE html>
<html lang="<?= h($lang ?? 'en') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= h($seoTitle) ?></title>
    <meta name="description" content="<?= h($seoDescription) ?>">
    <meta name="robots" content="<?= h((string)($seo['robots'] ?? 'index,follow')) ?>">
    <meta name="theme-color" content="<?= h(block('brand_colors', 'primary')) ?>">
    <?php if (!empty($seo['keywords'])): ?>
        <meta name="keywords" content="<?= h($seo['keywords']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">
    <meta property="og:title" content="<?= h($seoTitle) ?>">
    <meta property="og:description" content="<?= h($seoDescription) ?>">
    <meta property="og:type" content="<?= h($ogType) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= h($site['name'] ?? '') ?>">
    <meta property="og:locale" content="<?= h(str_replace('-', '_', (string)($lang ?? 'en'))) ?>">
    <?php if ($metaImage !== '' && !str_starts_with($metaImage, 'data:')): ?>
        <meta property="og:image" content="<?= h($metaImage) ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="<?= h($metaImage) ?>">
    <?php else: ?>
        <meta name="twitter:card" content="summary">
    <?php endif; ?>
    <meta name="twitter:title" content="<?= h($seoTitle) ?>">
    <meta name="twitter:description" content="<?= h($seoDescription) ?>">
    <?php if (!empty($site['favicon'])): ?>
        <link rel="icon" type="image/x-icon" href="<?= h(asset_url((string)$site['favicon'])) ?>">
        <link rel="shortcut icon" href="<?= h(asset_url((string)$site['favicon'])) ?>">
    <?php endif; ?>
    <script type="application/ld+json"><?= json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com/3.4.17?plugins=typography" referrerpolicy="no-referrer"></script>
    <?php
        $brandPrimary     = block('brand_colors', 'primary');
        $brandPrimaryDark = block('brand_colors', 'primary_dark');
        $brandInk         = block('brand_colors', 'ink');
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '<?= h(default_theme_hex_adjust($brandPrimary, 0.94)) ?>',
                            100: '<?= h(default_theme_hex_adjust($brandPrimary, 0.88)) ?>',
                            200: '<?= h(default_theme_hex_adjust($brandPrimary, 0.73)) ?>',
                            300: '<?= h(default_theme_hex_adjust($brandPrimary, 0.49)) ?>',
                            400: '<?= h(default_theme_hex_adjust($brandPrimary, 0.22)) ?>',
                            500: '<?= h($brandPrimary) ?>',
                            600: '<?= h($brandPrimaryDark) ?>',
                            700: '<?= h(default_theme_hex_adjust($brandPrimaryDark, 0.15, false)) ?>',
                            800: '<?= h(default_theme_hex_adjust($brandPrimaryDark, 0.35, false)) ?>',
                            900: '<?= h($brandInk) ?>',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php
        $themeStylePath = get_stylesheet_directory() . '/style.css';
        $themeStyleVersion = is_file($themeStylePath) ? (string)filemtime($themeStylePath) : '1';
        $richContentStylePath = APP_ROOT . '/assets/admin/rich-content.css';
        $richContentStyleVersion = is_file($richContentStylePath) ? (string)filemtime($richContentStylePath) : '1';
    ?>
    <link rel="stylesheet" href="<?= get_stylesheet_directory_uri() ?>/style.css?v=<?= h($themeStyleVersion) ?>">
    <link rel="stylesheet" href="<?= url('/assets/admin/rich-content.css') ?>?v=<?= h($richContentStyleVersion) ?>">
    <style>
        :root {
            --brand-ink: <?= h(block('brand_colors', 'ink')) ?>;
            --brand-muted: <?= h(block('brand_colors', 'muted')) ?>;
            --brand-surface: <?= h(block('brand_colors', 'surface')) ?>;
            --brand-border: <?= h(block('brand_colors', 'border')) ?>;
            --brand-accent: <?= h($brandPrimary) ?>;
        }
    </style>
    <?= get_head_code() ?>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 shadow-sm backdrop-blur" aria-label="Main navigation">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Logo -->
                <a href="<?= url('/') ?>" class="flex min-w-0 items-center">
                    <?php if (!empty($site['logo'])): ?>
                        <img src="<?= h(asset_url((string)$site['logo'])) ?>" alt="<?= h($site['name']) ?>" class="h-9 max-w-[150px] object-contain sm:max-w-[190px] lg:h-14 lg:max-w-[240px]">
                    <?php else: ?>
                        <div class="min-w-0">
                            <div class="max-w-[210px] truncate text-lg font-bold text-gray-900 sm:max-w-none lg:text-xl"><?= h($site['name']) ?></div>
                            <div class="hidden truncate text-sm text-gray-500 sm:block"><?= h($site['tagline']) ?></div>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <?php render_menu('main-nav', false); ?>
                    <button id="desktop-search-btn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 transition-colors hover:bg-gray-100 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500" aria-label="Open site search" aria-controls="desktop-search-panel" aria-expanded="false">
                        <i class="fas fa-search" aria-hidden="true"></i>
                    </button>
                    <?= get_google_translate_widget($site, 'px-4 py-2 text-gray-700') ?>
                    <a href="<?= url(block('header', 'cta_url', '/contact')) ?>" class="ml-4 px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                        <?= h(block('header', 'cta_text')) ?>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" type="button" class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 hover:text-brand-600 focus:outline-none" aria-label="Open navigation menu" aria-controls="mobile-menu" aria-expanded="false">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Desktop Search -->
            <div id="desktop-search-panel" class="absolute inset-x-0 top-full hidden border-t border-gray-100 bg-white shadow-lg" aria-hidden="true">
                <div class="container mx-auto px-4 py-5 lg:px-8">
                    <form action="<?= url('/search') ?>" method="get" role="search" class="mx-auto flex max-w-3xl items-center gap-3">
                        <label for="desktop-search-input" class="sr-only">Search the website</label>
                        <div class="relative flex-1">
                            <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true"></i>
                            <input id="desktop-search-input" type="search" name="q" value="<?= h($headerSearchQuery) ?>" maxlength="120" required autocomplete="off" placeholder="<?= h($searchPlaceholder) ?>" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-gray-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-brand-700">Search</button>
                        <button id="desktop-search-close" type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-900" aria-label="Close site search">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white max-h-[calc(100svh-4rem)] overflow-y-auto">
                <div class="py-4 space-y-2">
                    <?php render_menu('main-nav', true); ?>
                    <form action="<?= url('/search') ?>" method="get" role="search" class="px-4 pt-2">
                        <label for="mobile-search-input" class="sr-only">Search the website</label>
                        <div class="flex items-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100">
                            <i class="fas fa-search ml-4 text-gray-400" aria-hidden="true"></i>
                            <input id="mobile-search-input" type="search" name="q" value="<?= h($headerSearchQuery) ?>" maxlength="120" required autocomplete="off" placeholder="<?= h($searchPlaceholder) ?>" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-sm text-gray-900 outline-none">
                            <button type="submit" class="self-stretch bg-brand-600 px-4 text-sm font-semibold text-white hover:bg-brand-700">Search</button>
                        </div>
                    </form>
                    <div class="px-4 pt-2">
                        <?= get_google_translate_widget($site, 'block w-full rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-50') ?>
                    </div>
                    <div class="px-4 pt-2">
                        <a href="<?= url(block('header', 'cta_url', '/contact')) ?>" class="block w-full text-center px-6 py-2.5 bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 transition-colors">
                            <?= h(block('header', 'cta_text')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (button && menu) {
                button.addEventListener('click', function () {
                    const isOpen = !menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', isOpen);
                    button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    document.body.classList.toggle('overflow-hidden', !isOpen);
                });

                menu.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        menu.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('overflow-hidden');
                    });
                });
            }

            const searchButton = document.getElementById('desktop-search-btn');
            const searchPanel = document.getElementById('desktop-search-panel');
            const searchClose = document.getElementById('desktop-search-close');
            const searchInput = document.getElementById('desktop-search-input');

            function closeSearch() {
                if (!searchButton || !searchPanel) return;
                searchPanel.classList.add('hidden');
                searchPanel.setAttribute('aria-hidden', 'true');
                searchButton.setAttribute('aria-expanded', 'false');
            }

            if (searchButton && searchPanel) {
                searchButton.addEventListener('click', function () {
                    const willOpen = searchPanel.classList.contains('hidden');
                    searchPanel.classList.toggle('hidden', !willOpen);
                    searchPanel.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
                    searchButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen && searchInput) {
                        window.setTimeout(function () { searchInput.focus(); }, 0);
                    }
                });
                if (searchClose) searchClose.addEventListener('click', closeSearch);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !searchPanel.classList.contains('hidden')) {
                        closeSearch();
                        searchButton.focus();
                    }
                });
            }

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    if (menu) menu.classList.add('hidden');
                    if (button) button.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    closeSearch();
                }
            });
        });
    </script>
    <main>
