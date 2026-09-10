<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\PostModel;
use App\Models\SiteSearch;
use App\Models\Message;
use App\Models\Inquiry;
use App\Plugins\Exceptions\FormValidationException;

class SiteController extends BaseController {
    public function home(): void {
        $products = (new Product())->getList(6, true);  // 只获取已上架产品
        foreach ($products as &$p) $p['url'] = url('/product/' . $p['slug']);
        
        $cases = (new PostModel())->getList(6, true, 'case');
        foreach ($cases as &$c) $c['url'] = url('/case/' . $c['slug']);
        
        // 首页使用全局 SEO 设置
        $this->renderSite('home', [
            'products' => $products,
            'cases' => $cases,
            'seo' => [
                'title' => $this->siteData['site']['seo_title'] ?: $this->siteData['site']['name'],
                'description' => $this->siteData['site']['seo_description'] ?: $this->siteData['site']['tagline'],
                'keywords' => $this->siteData['site']['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function search(): void {
        $query = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 120);
        $type = trim((string)($_GET['type'] ?? 'all'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = (new SiteSearch())->search($query, $type, $page, 12);

        $pageTitle = $query === '' ? 'Search' : 'Search results for “' . $query . '”';
        $this->renderSite('search', array_merge($result, [
            'query' => $query,
            'seo' => [
                'title' => $pageTitle . ' - ' . $this->siteData['site']['name'],
                'description' => $query === ''
                    ? 'Search products, articles and cases.'
                    : 'Search results for ' . $query . '.',
                'robots' => 'noindex,follow',
                'canonical' => base_url() . '/search',
            ],
        ]));
    }

    public function products(): void {
        if (isset($_GET['category'])) {
            $category = (new Category())->getById((int)$_GET['category']);
            if (!$category || ($category['type'] ?? 'product') !== 'product') {
                $this->notFound();
                return;
            }

            header('Location: ' . product_category_url($category), true, 301);
            exit;
        }

        $this->renderProductList(null);
    }

    public function productCategory(string $slug): void {
        $categoryModel = new Category();
        $currentCategory = $categoryModel->getBySlug($slug, 'product');
        if (!$currentCategory) {
            $this->notFound();
            return;
        }

        $canonical = product_category_url($currentCategory);
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $expectedPath = parse_url($canonical, PHP_URL_PATH) ?: '';
        if ($currentPath !== $expectedPath) {
            header('Location: ' . $canonical, true, 301);
            exit;
        }

        $this->renderProductList($currentCategory);
    }

    private function renderProductList(?array $currentCategory): void {
        $categoryModel = new Category();
        $productModel = new Product();
        $categoryId = $currentCategory ? (int)$currentCategory['id'] : 0;

        // 获取产品列表
        if ($categoryId > 0) {
            $items = $productModel->getByCategory($categoryId);
        } else {
            $items = $productModel->getList(0, true);  // 只获取已上架产品
        }
        foreach ($items as &$i) $i['url'] = url('/product/' . $i['slug']);
        
        // 获取分类列表
        $categories = $categoryModel->getTree('product');
        
        $title = $currentCategory ? $currentCategory['name'] : 'All Products';
        $seoDescription = trim((string)($currentCategory['description'] ?? ''));
        if ($seoDescription === '') {
            $seoDescription = (string)($this->siteData['site']['seo_description'] ?: $this->siteData['site']['tagline'] ?? '');
        }
        
        $pageData = plugin_filter('product.list_data', [
            'title' => $title,
            'items' => $items,
            'categories' => $categories,
            'current_category' => $currentCategory,
        ], ['category' => $currentCategory]);
        $this->renderSite('product_list', array_merge($pageData, [
            'seo' => [
                'title' => $title . ' - ' . $this->siteData['site']['name'],
                'description' => $seoDescription,
                'canonical' => $currentCategory ? base_url() . '/product-category/' . rawurlencode((string)$currentCategory['slug']) : base_url() . '/products',
            ]
        ]));
    }

    public function productDetail(string $slug): void {
        $productModel = new Product();
        $categoryModel = new Category();
        
        $item = $productModel->getBySlug($slug);
        // 只有已上架的产品才能查看
        if (!$item || $item['status'] !== 'active') { $this->notFound(); return; }
        
        // 获取分类信息用于面包屑
        $category = null;
        if (!empty($item['category_id'])) {
            $category = $categoryModel->getById((int)$item['category_id']);
        }
        
        // 获取相关产品（同分类或最新产品）
        $relatedProducts = [];
        if (!empty($item['category_id'])) {
            $relatedProducts = $productModel->getByCategory((int)$item['category_id'], 6);
            // 排除当前产品
            $relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] !== $item['id']);
            $relatedProducts = array_slice($relatedProducts, 0, 4);
        }
        // 如果相关产品不足4个，补充最新产品
        if (count($relatedProducts) < 4) {
            $latest = $productModel->getList(6, true);
            foreach ($latest as $p) {
                if ($p['id'] !== $item['id'] && !in_array($p['id'], array_column($relatedProducts, 'id'))) {
                    $relatedProducts[] = $p;
                    if (count($relatedProducts) >= 4) break;
                }
            }
        }
        foreach ($relatedProducts as &$p) $p['url'] = url('/product/' . $p['slug']);

        $priceMode = normalize_product_price_mode((string)($item['price_mode'] ?? 'tier'));
        
        $productData = plugin_filter('product.detail_data', [
            'item' => $item,
            'category' => $category,
            'images' => $item['images'] ?? [],
            'price_mode' => $priceMode,
            'price_tiers' => $productModel->getPrices((int)$item['id']),
            'product_skus' => $productModel->getSkus((int)$item['id']),
            'currency' => $this->siteData['site']['currency'] ?? 'USD',
            'related_products' => $relatedProducts,
            'whatsapp' => $this->siteData['site']['whatsapp'],
            'inquiry_form' => true,
        ], ['slug' => $slug]);
        $this->renderSite('product_detail', array_merge($productData, [
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?: $this->siteData['site']['tagline']),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]));
    }

    public function cases(): void {
        $items = (new PostModel())->getList(0, true, 'case');
        foreach ($items as &$i) $i['url'] = url('/case/' . $i['slug']);
        $this->renderSite('case_list', [
            'title' => 'Cases',
            'items' => $items,
            'seo' => ['title' => 'Cases - ' . $this->siteData['site']['name']]
        ]);
    }

    public function caseDetail(string $slug): void {
        $item = (new PostModel())->getBySlug($slug, 'case');
        if (!$item || ($item['status'] ?? 'active') !== 'active') { $this->notFound(); return; }
        $this->renderSite('case_detail', [
            'item' => $item,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?? ''),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function blog(): void {
        $categoryModel = new Category();
        $postModel = new PostModel();
        
        // 获取当前分类ID
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $currentCategory = $categoryId > 0 ? $categoryModel->getById($categoryId) : null;
        
        // 获取文章列表
        if ($categoryId > 0) {
            $items = $postModel->getByCategory($categoryId, 0, 'post');
        } else {
            $items = $postModel->getList(0, true, 'post');  // 只获取已发布文章
        }
        foreach ($items as &$i) $i['url'] = url('/blog/' . $i['slug']);
        
        // 获取分类列表（带文章数量）
        $categories = $categoryModel->getTree('post');
        
        $title = $currentCategory ? $currentCategory['name'] : 'Blog';
        
        $this->renderSite('post_list', [
            'title' => $title,
            'items' => $items,
            'categories' => $categories,
            'current_category' => $currentCategory,
            'seo' => ['title' => $title . ' - ' . $this->siteData['site']['name']]
        ]);
    }

    public function blogDetail(string $slug): void {
        $postModel = new PostModel();
        $categoryModel = new Category();
        
        $item = $postModel->getBySlug($slug, 'post');
        // 只有已发布的文章才能查看
        if (!$item || ($item['status'] ?? 'active') !== 'active') { $this->notFound(); return; }
        
        // 获取分类信息用于面包屑
        $category = null;
        if (!empty($item['category_id'])) {
            $category = $categoryModel->getById((int)$item['category_id']);
        }

        $previousPost = $postModel->getPreviousActive($item, 'post');
        $nextPost = $postModel->getNextActive($item, 'post');
        $recommendedPosts = $postModel->getRecommended($item, 3, 'post');
        foreach ($recommendedPosts as &$post) {
            $post['url'] = url('/blog/' . $post['slug']);
        }
        unset($post);
        
        $this->renderSite('post_detail', [
            'item' => $item,
            'category' => $category,
            'previous_post' => $previousPost,
            'next_post' => $nextPost,
            'recommended_posts' => $recommendedPosts,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?? ''),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function pageDetail(string $slug): void {
        $postModel = new PostModel();
        $item = $postModel->getBySlug($slug, 'page');

        if (!$item || ($item['status'] ?? 'active') !== 'active') {
            $this->notFound();
            return;
        }

        $this->renderSite('page_detail', [
            'item' => $item,
            'seo' => [
                'title' => ($item['seo_title'] ?: $item['title']) . ' - ' . $this->siteData['site']['name'],
                'description' => $item['seo_description'] ?: ($item['summary'] ?: $this->siteData['site']['tagline']),
                'keywords' => $item['seo_keywords'] ?? '',
            ]
        ]);
    }

    public function about(): void {
        $this->renderSite('about', [
            'seo' => ['title' => 'About Us - ' . $this->siteData['site']['name']]
        ]);
    }

    public function contact(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            try {
                $input = plugin_filter('form.before_validate', [
                    'name' => trim((string)$_POST['name']),
                    'email' => trim((string)$_POST['email']),
                    'company' => trim((string)($_POST['company'] ?? '')),
                    'phone' => trim((string)($_POST['phone'] ?? '')),
                    'message' => trim((string)$_POST['message']),
                ], ['form' => 'contact']);
            } catch (FormValidationException) {
                $this->redirectAfterFormValidationFailure('/contact');
            }
            $id = (new Message())->create($input);
            plugin_event('message.created', ['id' => $id, 'data' => $input]);
            plugin_event('form.submitted', ['form' => 'contact', 'id' => $id, 'data' => $input]);
            $this->renderSite('thanks');
            return;
        }
        $this->renderSite('contact', ['seo' => ['title' => 'Contact - ' . $this->siteData['site']['name']]]);
    }

    public function inquiry(): void {
        csrf_check();
        try {
            $input = plugin_filter('form.before_validate', [
                'product_id' => (int)($_POST['product_id'] ?? 0),
                'name' => trim((string)$_POST['name']),
                'email' => trim((string)$_POST['email']),
                'company' => trim((string)($_POST['company'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'quantity' => trim((string)($_POST['quantity'] ?? '')),
                'message' => trim((string)($_POST['message'] ?? '')),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'source_url' => $_SERVER['HTTP_REFERER'] ?? '',
            ], ['form' => 'inquiry']);
        } catch (FormValidationException) {
            $this->redirectAfterFormValidationFailure('/products');
        }
        $id = (new Inquiry())->create($input);
        plugin_event('inquiry.created', ['id' => $id, 'data' => $input]);
        plugin_event('form.submitted', ['form' => 'inquiry', 'id' => $id, 'data' => $input]);
        $this->renderSite('thanks');
    }

    private function redirectAfterFormValidationFailure(string $fallback): never {
        $target = url($fallback);
        $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
        if ($referer !== '') {
            $parts = parse_url($referer);
            $host = strtolower((string)($parts['host'] ?? ''));
            $currentHost = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')) ?? '');
            if ($host !== '' && $host === $currentHost) {
                $path = (string)($parts['path'] ?? '');
                if ($path !== '' && str_starts_with($path, '/')) {
                    $target = $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
                }
            }
        }
        header('Location: ' . $target, true, 303);
        exit;
    }

    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nSitemap: " . base_url() . "/sitemap.xml\n";
        exit;
    }

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        $db = \App\Core\Database::getInstance();
        $urls = [base_url() . '/', base_url() . '/products', base_url() . '/cases', base_url() . '/blog', base_url() . '/contact'];
        
        $res = $db->query("SELECT slug FROM product_categories WHERE type = 'product'");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/product-category/' . rawurlencode((string)$r['slug']);
        $res = $db->query("SELECT slug FROM products WHERE status = 'active' AND deleted_at IS NULL");  // 只索引已上架且未删除产品
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/product/' . $r['slug'];
        $res = $db->query("SELECT slug FROM posts WHERE status = 'active' AND post_type = 'case'");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/case/' . $r['slug'];
        $res = $db->query("SELECT slug FROM posts WHERE status = 'active' AND post_type = 'post'");  // 只索引已发布文章
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/blog/' . $r['slug'];
        $res = $db->query("SELECT slug FROM posts WHERE status = 'active' AND post_type = 'page'");
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) $urls[] = base_url() . '/page/' . $r['slug'];

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) echo "  <url><loc>" . h($u) . "</loc></url>\n";
        echo "</urlset>";
        exit;
    }

    public function notFound(): void {
        http_response_code(404);
        $this->renderSite('404', ['seo' => ['title' => '404 - ' . $this->siteData['site']['name']]]);
    }
}
