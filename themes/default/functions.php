<?php
declare(strict_types=1);

/**
 * 默认主题 - 主题内辅助函数
 * 此文件在每次渲染主题时自动加载，可以在此定义可重用的模板函数
 * 可以访问数据库模型和助手函数
 * 
 * 核心功能函数现在通过 app/Helpers/Helpers.php 统一提供，包括:
 * - get_products()
 * - get_posts()
 * - get_cases()
 * - get_product_categories()
 * - get_post_categories()
 * - get_stylesheet_directory()
 * - get_stylesheet_directory_uri()
 */

if (!function_exists('default_theme_no_image_url')) {
    function default_theme_no_image_url(): string {
        return asset_url('assets/images/no-image.webp');
    }
}

if (!function_exists('get_image_url')) {
    function get_image_url(?string $image, int $width = 800, int $height = 300, string $text = ''): string {
        $image = trim((string)$image);
        if ($image !== '') {
            return asset_url($image);
        }

        return default_theme_no_image_url();
    }
}

if (!function_exists('default_theme_absolute_url')) {
    function default_theme_absolute_url(?string $path): string {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https:' : 'http:';
            return $scheme . $path;
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $relative = asset_url($path);
        $basePath = base_path();
        if ($basePath !== '' && str_starts_with($relative, $basePath . '/')) {
            $relative = substr($relative, strlen($basePath));
        }

        return rtrim(base_url(), '/') . '/' . ltrim($relative, '/');
    }
}

if (!function_exists('default_theme_meta_image')) {
    function default_theme_meta_image(array $site = [], array $seo = [], array $context = []): string {
        $candidates = [
            $seo['image'] ?? '',
            $context['cover'] ?? '',
            $context['image'] ?? '',
            $site['og_image'] ?? '',
            $site['logo'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            if (trim((string)$candidate) !== '') {
                return default_theme_absolute_url((string)$candidate);
            }
        }

        return '';
    }
}

if (!function_exists('default_theme_meta_description')) {
    function default_theme_meta_description(string $value): string {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?: '');
        return mb_substr($value, 0, 160);
    }
}

if (!function_exists('default_theme_contact_channel_href')) {
    function default_theme_contact_channel_href(string $channel, ?string $value): string {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $handle = ltrim($value, '@');
        return match ($channel) {
            'telegram' => 'https://t.me/' . rawurlencode($handle),
            'line' => 'https://line.me/ti/p/' . rawurlencode($handle),
            'vk' => 'https://vk.com/' . rawurlencode($handle),
            default => $value,
        };
    }
}

if (!function_exists('default_theme_hex_adjust')) {
    function default_theme_hex_adjust(string $hex, float $factor, bool $lighten = true): string {
        $hex = ltrim(trim($hex), '#');
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '0ea5e9';
        }

        $channels = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];

        foreach ($channels as &$channel) {
            $channel = $lighten
                ? (int)round($channel + (255 - $channel) * $factor)
                : (int)round($channel * (1 - $factor));
            $channel = max(0, min(255, $channel));
        }

        return sprintf('#%02x%02x%02x', $channels[0], $channels[1], $channels[2]);
    }
}

if (!function_exists('default_theme_schema_graph')) {
    function default_theme_schema_graph(array $site = [], array $seo = [], array $context = []): array {
        $canonical = (string)($seo['canonical'] ?? base_url());
        $siteName = (string)($site['name'] ?? '');
        $description = default_theme_meta_description((string)($seo['description'] ?? ($site['tagline'] ?? '')));
        $logo = default_theme_absolute_url((string)($site['logo'] ?? ''));
        $image = default_theme_meta_image($site, $seo, $context);
        $organizationId = rtrim(base_url(), '/') . '#organization';
        $websiteId = rtrim(base_url(), '/') . '#website';

        $sameAs = [];
        foreach (['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'reddit', 'telegram', 'discord', 'quora'] as $key) {
            if (!empty($site[$key]) && str_starts_with((string)$site[$key], 'http')) {
                $sameAs[] = (string)$site[$key];
            }
        }

        $graph = [
            [
                '@type' => 'Organization',
                '@id' => $organizationId,
                'name' => $siteName,
                'url' => rtrim(base_url(), '/') . '/',
            ],
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'url' => rtrim(base_url(), '/') . '/',
                'name' => $siteName,
                'publisher' => ['@id' => $organizationId],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => rtrim(base_url(), '/') . '/search?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $canonical . '#webpage',
                'url' => $canonical,
                'name' => (string)($seo['title'] ?? $siteName),
                'description' => $description,
                'isPartOf' => ['@id' => $websiteId],
                'about' => ['@id' => $organizationId],
            ],
        ];

        if ($logo !== '') {
            $graph[0]['logo'] = ['@type' => 'ImageObject', 'url' => $logo];
        }
        if ($image !== '' && !str_starts_with($image, 'data:')) {
            $graph[2]['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $image];
        }
        if ($sameAs !== []) {
            $graph[0]['sameAs'] = $sameAs;
        }
        if (!empty($site['company_phone']) || !empty($site['company_email'])) {
            $graph[0]['contactPoint'] = [[
                '@type' => 'ContactPoint',
                'telephone' => (string)($site['company_phone'] ?? ''),
                'email' => (string)($site['company_email'] ?? ''),
                'contactType' => 'sales',
                'availableLanguage' => ['English'],
            ]];
        }

        if (($context['type'] ?? '') === 'product' && !empty($context['item'])) {
            $item = $context['item'];
            $productImages = [];
            foreach ((array)($context['images'] ?? []) as $img) {
                $absolute = default_theme_absolute_url((string)$img);
                if ($absolute !== '' && !str_starts_with($absolute, 'data:')) {
                    $productImages[] = $absolute;
                }
            }
            if ($productImages === [] && !empty($item['cover'])) {
                $productImages[] = default_theme_absolute_url((string)$item['cover']);
            }

            $product = [
                '@type' => 'Product',
                '@id' => $canonical . '#product',
                'name' => (string)($item['title'] ?? ''),
                'url' => $canonical,
                'brand' => ['@id' => $organizationId],
            ];
            if (!empty($item['id'])) {
                $product['sku'] = (string)$item['id'];
            }
            if (!empty($item['category_name'])) {
                $product['category'] = (string)$item['category_name'];
            }
            if ($productImages !== []) {
                $product['image'] = array_values(array_unique($productImages));
            }

            $productDescription = default_theme_meta_description((string)($item['seo_description'] ?? ''));
            if ($productDescription === '') {
                $productDescription = default_theme_meta_description((string)($item['summary'] ?? ''));
            }
            if ($productDescription === '') {
                $productDescription = $description;
            }
            $product['description'] = $productDescription;

            $priceMode = normalize_product_price_mode((string)($context['price_mode'] ?? $item['price_mode'] ?? 'tier'));
            $currency = normalize_currency_code((string)($site['currency'] ?? 'USD'), 'USD');
            if ($priceMode === 'range') {
                $rangeMin = isset($item['price_range_min']) ? (float)$item['price_range_min'] : 0.0;
                $rangeMax = isset($item['price_range_max']) ? (float)$item['price_range_max'] : 0.0;
                if ($rangeMin > 0 && $rangeMax > 0) {
                    if ($rangeMax < $rangeMin) {
                        [$rangeMin, $rangeMax] = [$rangeMax, $rangeMin];
                    }
                    $product['offers'] = [
                        '@type' => 'AggregateOffer',
                        'priceCurrency' => $currency,
                        'availability' => 'https://schema.org/InStock',
                        'url' => $canonical,
                        'lowPrice' => (string)$rangeMin,
                        'highPrice' => (string)$rangeMax,
                        'offerCount' => 1,
                    ];
                }
            } elseif ($priceMode !== 'negotiable') {
                $priceSource = $priceMode === 'sku' ? ($context['product_skus'] ?? []) : ($context['price_tiers'] ?? []);
                $prices = array_values(array_filter((array)$priceSource, static fn($price): bool => isset($price['price']) && (float)$price['price'] > 0));
                if ($prices !== []) {
                    $priceValues = array_map(static fn($tier): float => (float)$tier['price'], $prices);
                    $product['offers'] = [
                        '@type' => count($prices) > 1 ? 'AggregateOffer' : 'Offer',
                        'priceCurrency' => $currency,
                        'availability' => 'https://schema.org/InStock',
                        'url' => $canonical,
                    ];
                    if (count($prices) > 1) {
                        $product['offers']['lowPrice'] = (string)min($priceValues);
                        $product['offers']['highPrice'] = (string)max($priceValues);
                        $product['offers']['offerCount'] = count($prices);
                    } else {
                        $product['offers']['price'] = (string)$priceValues[0];
                    }
                }
            }

            $graph[] = $product;
        }

        if (in_array(($context['type'] ?? ''), ['article', 'case', 'page'], true) && !empty($context['item'])) {
            $item = $context['item'];
            $articleImage = default_theme_absolute_url((string)($item['cover'] ?? ''));
            $article = [
                '@type' => ($context['type'] ?? '') === 'article' ? 'BlogPosting' : 'Article',
                '@id' => $canonical . '#article',
                'headline' => (string)($item['title'] ?? ''),
                'description' => default_theme_meta_description((string)($item['seo_description'] ?? $item['summary'] ?? $description)),
                'url' => $canonical,
                'author' => ['@id' => $organizationId],
                'publisher' => ['@id' => $organizationId],
            ];
            if (!empty($item['created_at'])) {
                $article['datePublished'] = date('c', strtotime((string)$item['created_at']));
            }
            if ($articleImage !== '' && !str_starts_with($articleImage, 'data:')) {
                $article['image'] = [$articleImage];
            }
            $graph[] = $article;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}

// 获取轮播产品（性能优化版）
// 优先获取有首图的产品，不足时补充最新产品
if (!function_exists('get_carousel_products')) {
    function get_carousel_products(int $limit = 3, string $sliderSlug = 'home-hero'): array {
        static $cache = null; // 简单静态缓存，避免重复查询
        static $cacheKey = '';
        $requestedKey = $sliderSlug . ':' . $limit;

        if ($cache !== null && $cacheKey === $requestedKey) {
            return $cache;
        }

        // 首先尝试从新的轮播图系统获取
        $sliderItems = get_slider_items($sliderSlug);
        if (!empty($sliderItems)) {
            $cacheKey = $requestedKey;
            $cache = $sliderItems;
            return $sliderItems;
        }

        // 回退到旧的产品轮播逻辑
        $featuredProducts = get_products(['limit' => $limit, 'featured' => true]);

        // 2. 如果不够数量，补充最新产品（排除已选中的产品）
        if (count($featuredProducts) < $limit) {
            $remaining = $limit - count($featuredProducts);
            $excludeIds = array_column($featuredProducts, 'id');

            $latestProducts = get_products(['limit' => $remaining + 10]); // 多取一些用于过滤

            foreach ($latestProducts as $product) {
                if (count($featuredProducts) >= $limit) break;
                if (!in_array($product['id'], $excludeIds)) {
                    $featuredProducts[] = $product;
                }
            }
        }

        // 3. 确保不超过限制数量
        $featuredProducts = array_slice($featuredProducts, 0, $limit);

        // 4. 格式化数据为轮播所需结构
        $carouselProducts = [];
        foreach ($featuredProducts as $product) {
            $carouselProducts[] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'summary' => $product['summary'],
                'url' => url('/product/' . $product['slug']),
                'image' => $product['cover'],
            ];
        }

        $cacheKey = $requestedKey;
        $cache = $carouselProducts;
        return $carouselProducts;
    }
}

/**
 * 获取轮播图项目（新版轮播图系统）
 * @param string $sliderSlug 轮播图标识符
 * @return array 轮播图片数组
 */
if (!function_exists('get_slider_items')) {
    function get_slider_items(string $sliderSlug): array {
        try {
            $sliderModel = new \App\Models\Slider();
            $slider = $sliderModel->getBySlugWithItems($sliderSlug);
            
            if (!$slider || $slider['status'] !== 'active') {
                return [];
            }
            
            $items = [];
            foreach ($slider['items'] as $item) {
                $items[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'url' => $item['link_url'] ?: '#',
                    'link_text' => $item['link_text'] ?: 'View Details',
                    'image' => $item['image'],
                ];
            }
            
            return $items;
        } catch (\Exception $e) {
            // 如果发生错误，返回空数组
            return [];
        }
    }
}

/**
 * 获取菜单（新版菜单系统）
 * @param string $menuSlug 菜单标识符
 * @return array 菜单项数组
 */
if (!function_exists('get_menu_items')) {
    function get_menu_items(string $menuSlug = 'main-nav'): array {
        static $cache = [];
        
        if (isset($cache[$menuSlug])) {
            return $cache[$menuSlug];
        }
        
        try {
            $menuModel = new \App\Models\Menu();
            $menu = $menuModel->getBySlugWithItems($menuSlug, true);
            
            if (!$menu || $menu['status'] !== 'active') {
                $cache[$menuSlug] = [];
                return [];
            }
            
            $items = $menu['items'] ?? [];
            
            // 格式化菜单项
            $formatItems = function($items) use (&$formatItems) {
                $result = [];
                foreach ($items as $item) {
                    $formatted = [
                        'id' => $item['id'],
                        'title' => $item['title'],
                        'url' => $item['url'],
                        'target' => $item['target'] ?? '_self',
                        'css_class' => $item['css_class'] ?? '',
                        'children' => []
                    ];
                    
                    if (!empty($item['children'])) {
                        $formatted['children'] = $formatItems($item['children']);
                    }
                    
                    $result[] = $formatted;
                }
                return $result;
            };
            
            $formattedItems = $formatItems($items);
            $cache[$menuSlug] = $formattedItems;
            return $formattedItems;
            
        } catch (\Exception $e) {
            $cache[$menuSlug] = [];
            return [];
        }
    }
}

/**
 * 渲染菜单项
 * @param array $item 菜单项
 * @param bool $isMobile 是否为移动端
 * @return void
 */
if (!function_exists('render_menu_item')) {
    function render_menu_item(array $item, bool $isMobile = false): void {
        $hasChildren = !empty($item['children']);
        $baseClass = $isMobile 
            ? 'block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-brand-600 font-medium rounded-lg transition-colors'
            : 'px-4 py-2 text-gray-700 hover:text-brand-600 font-medium transition-colors';
        
        if (!empty($item['css_class'])) {
            $baseClass .= ' ' . $item['css_class'];
        }
        
        $target = $item['target'] ?? '_self';
        $targetAttr = $target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
        
        // 处理 URL
        $url = $item['url'];
        if (!str_starts_with($url, 'http') && !str_starts_with($url, '#') && !str_starts_with($url, 'mailto:')) {
            $url = url($url);
        }
        
        if ($hasChildren && !$isMobile) {
            // 桌面端下拉菜单
            ?>
            <div class="relative group">
                <button type="button" class="<?= h($baseClass) ?> inline-flex items-center gap-1">
                    <?= h($item['title']) ?>
                    <i class="fas fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                </button>
                <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="py-2">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php 
                            $childUrl = $child['url'];
                            if (!str_starts_with($childUrl, 'http') && !str_starts_with($childUrl, '#') && !str_starts_with($childUrl, 'mailto:')) {
                                $childUrl = url($childUrl);
                            }
                            $childTarget = $child['target'] ?? '_self';
                            $childTargetAttr = $childTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                            ?>
                            <a href="<?= h($childUrl) ?>"<?= $childTargetAttr ?> class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <?= h($child['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        } elseif ($hasChildren && $isMobile) {
            // 移动端子菜单
            ?>
            <div>
                <div class="px-4 py-2 text-gray-700 font-medium"><?= h($item['title']) ?></div>
                <div class="pl-4 space-y-1">
                    <?php foreach ($item['children'] as $child): ?>
                        <?php 
                        $childUrl = $child['url'];
                        if (!str_starts_with($childUrl, 'http') && !str_starts_with($childUrl, '#') && !str_starts_with($childUrl, 'mailto:')) {
                            $childUrl = url($childUrl);
                        }
                        $childTarget = $child['target'] ?? '_self';
                        $childTargetAttr = $childTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                        <a href="<?= h($childUrl) ?>"<?= $childTargetAttr ?> class="block px-4 py-2 text-gray-600 hover:bg-gray-50 hover:text-brand-600 font-medium rounded-lg">
                            <?= h($child['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        } else {
            // 普通菜单项
            ?>
            <a href="<?= h($url) ?>"<?= $targetAttr ?> class="<?= h($baseClass) ?>">
                <?= h($item['title']) ?>
            </a>
            <?php
        }
    }
}

/**
 * 渲染完整菜单
 * @param string $menuSlug 菜单标识符
 * @param bool $isMobile 是否为移动端
 * @return void
 */
if (!function_exists('render_menu')) {
    function render_menu(string $menuSlug = 'main-nav', bool $isMobile = false): void {
        $items = get_menu_items($menuSlug);
        
        if (empty($items)) {
            // 使用默认菜单
            $defaultItems = [
                ['title' => 'Home', 'url' => '/', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Products', 'url' => '/products', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Cases', 'url' => '/cases', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'css_class' => '', 'children' => []],
                ['title' => 'About Us', 'url' => '/about', 'target' => '_self', 'css_class' => '', 'children' => []],
            ];
            $items = $defaultItems;
        }
        
        foreach ($items as $item) {
            render_menu_item($item, $isMobile);
        }
    }
}
