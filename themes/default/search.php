<?php
/**
 * 页面模板：统一搜索结果
 * 变量：$query、$items、$counts、$type、$total、$page、$total_pages。
 */
$query = (string)($query ?? '');
$items = $items ?? [];
$counts = $counts ?? ['all' => 0, 'product' => 0, 'post' => 0, 'case' => 0];
$type = (string)($type ?? 'all');
$total = (int)($total ?? 0);
$page = (int)($page ?? 1);
$totalPages = (int)($total_pages ?? 1);
$typeLabels = [
    'all' => 'All',
    'product' => 'Products',
    'post' => 'Articles',
    'case' => 'Cases',
];
$resultLabels = [
    'product' => ['label' => 'Product', 'icon' => 'fa-box', 'classes' => 'bg-brand-50 text-brand-700'],
    'post' => ['label' => 'Article', 'icon' => 'fa-newspaper', 'classes' => 'bg-amber-50 text-amber-700'],
    'case' => ['label' => 'Case', 'icon' => 'fa-briefcase', 'classes' => 'bg-emerald-50 text-emerald-700'],
];
$buildSearchUrl = static function (array $overrides = []) use ($query, $type): string {
    $params = array_merge(['q' => $query, 'type' => $type], $overrides);
    if (($params['type'] ?? 'all') === 'all') {
        unset($params['type']);
    }
    if (($params['page'] ?? 1) <= 1) {
        unset($params['page']);
    }
    return url('/search') . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
};
?>

<section class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-800 text-white">
    <div class="container mx-auto px-4 py-10 sm:py-14 lg:px-8 lg:py-16">
        <nav class="mb-6 text-sm" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-white/70">
                <li><a href="<?= url('/') ?>" class="hover:text-white">Home</a></li>
                <li><i class="fas fa-chevron-right text-[10px]" aria-hidden="true"></i></li>
                <li class="font-medium text-white">Search</li>
            </ol>
        </nav>

        <div class="mx-auto max-w-4xl">
            <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-brand-200">Site Search</p>
            <h1 class="text-3xl font-bold leading-tight sm:text-4xl">Find products, articles and cases</h1>
            <form action="<?= url('/search') ?>" method="get" role="search" class="mt-7 flex flex-col gap-3 sm:flex-row">
                <label for="search-page-input" class="sr-only">Search products, articles and cases</label>
                <div class="relative flex-1">
                    <i class="fas fa-search pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                    <input id="search-page-input" type="search" name="q" value="<?= h($query) ?>" maxlength="120" required autocomplete="off" placeholder="<?= h(block('header', 'search_placeholder', 'Search products, articles and cases')) ?>" class="w-full rounded-xl border border-white/20 bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-xl outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-400/25">
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-8 py-4 font-semibold text-white shadow-xl transition hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/30">
                    <i class="fas fa-search mr-2" aria-hidden="true"></i>
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<section class="py-10 lg:py-14">
    <div class="container mx-auto px-4 lg:px-8">
        <?php if ($query === ''): ?>
            <div class="mx-auto max-w-2xl rounded-2xl border border-gray-100 bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                    <i class="fas fa-search text-2xl" aria-hidden="true"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">What are you looking for?</h2>
                <p class="mt-2 text-gray-500">Enter a keyword to search all published products, articles and cases.</p>
            </div>
        <?php else: ?>
            <div class="mb-8 flex flex-col gap-4 border-b border-gray-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-medium text-brand-600">Search results</p>
                    <h2 class="mt-1 text-2xl font-bold text-gray-900">
                        “<?= h($query) ?>”
                        <span class="ml-1 text-base font-normal text-gray-500"><?= $total ?> <?= $total === 1 ? 'result' : 'results' ?></span>
                    </h2>
                </div>
                <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="Filter search results">
                    <?php foreach ($typeLabels as $filterType => $label): ?>
                        <?php $isActive = $filterType === $type; ?>
                        <a href="<?= h($buildSearchUrl(['type' => $filterType, 'page' => 1])) ?>"
                           class="inline-flex flex-none items-center rounded-full border px-4 py-2 text-sm font-semibold transition <?= $isActive ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-brand-300 hover:text-brand-700' ?>"
                           <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <?= h($label) ?>
                            <span class="ml-2 rounded-full px-2 py-0.5 text-xs <?= $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' ?>"><?= (int)($counts[$filterType] ?? 0) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($items)): ?>
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <i class="fas fa-search-minus text-2xl" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">No matching results</h2>
                    <p class="mt-2 text-gray-500">Try a shorter keyword, check the spelling, or search all content types.</p>
                    <?php if ($type !== 'all'): ?>
                        <a href="<?= h($buildSearchUrl(['type' => 'all', 'page' => 1])) ?>" class="mt-5 inline-flex items-center rounded-lg bg-brand-50 px-5 py-2.5 font-semibold text-brand-700 hover:bg-brand-100">Search all content</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid gap-5 md:grid-cols-2">
                    <?php foreach ($items as $item): ?>
                        <?php $meta = $resultLabels[$item['content_type']] ?? $resultLabels['post']; ?>
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:flex-row">
                            <a href="<?= h($item['url']) ?>" class="block aspect-[16/9] overflow-hidden bg-gray-100 sm:aspect-auto sm:w-44 sm:flex-none" tabindex="-1" aria-hidden="true">
                                <img src="<?= h(get_image_url($item['cover'] ?? null, 480, 360, (string)$item['title'])) ?>" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" decoding="async">
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col p-5">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= h($meta['classes']) ?>">
                                        <i class="fas <?= h($meta['icon']) ?> mr-1.5" aria-hidden="true"></i>
                                        <?= h($meta['label']) ?>
                                    </span>
                                    <?php if (!empty($item['category_name'])): ?>
                                        <span class="truncate text-xs text-gray-400"><?= h($item['category_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="line-clamp-2 text-lg font-bold text-gray-900">
                                    <a href="<?= h($item['url']) ?>" class="transition-colors hover:text-brand-600"><?= h($item['title']) ?></a>
                                </h3>
                                <?php if (!empty($item['excerpt'])): ?>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-500"><?= h($item['excerpt']) ?></p>
                                <?php endif; ?>
                                <a href="<?= h($item['url']) ?>" class="mt-auto pt-4 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    View details <i class="fas fa-arrow-right ml-1 text-xs" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    ?>
                    <nav class="mt-10 flex items-center justify-center gap-2" aria-label="Search results pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= h($buildSearchUrl(['page' => $page - 1])) ?>" class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 hover:border-brand-300 hover:text-brand-700" aria-label="Previous page">
                                <i class="fas fa-chevron-left mr-2 text-xs" aria-hidden="true"></i> Previous
                            </a>
                        <?php endif; ?>
                        <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>
                            <a href="<?= h($buildSearchUrl(['page' => $pageNumber])) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border text-sm font-semibold <?= $pageNumber === $page ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-brand-300 hover:text-brand-700' ?>" <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= h($buildSearchUrl(['page' => $page + 1])) ?>" class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 hover:border-brand-300 hover:text-brand-700" aria-label="Next page">
                                Next <i class="fas fa-chevron-right ml-2 text-xs" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
