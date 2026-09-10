<?php
declare(strict_types=1);

namespace App\Models;

/**
 * 前台统一搜索：只返回可公开访问的产品、文章和案例。
 */
class SiteSearch extends BaseModel {
    private const TYPES = ['all', 'product', 'post', 'case'];

    public function search(string $query, string $type = 'all', int $page = 1, int $perPage = 12): array {
        $query = trim($query);
        $type = in_array($type, self::TYPES, true) ? $type : 'all';
        $page = max(1, $page);
        $perPage = max(1, min(48, $perPage));

        $emptyResult = [
            'items' => [],
            'counts' => ['all' => 0, 'product' => 0, 'post' => 0, 'case' => 0],
            'total' => 0,
            'page' => 1,
            'per_page' => $perPage,
            'total_pages' => 1,
            'type' => $type,
        ];
        if ($query === '') {
            return $emptyResult;
        }

        $escapedQuery = $this->escapeLike($query);
        $params = [
            ':exact' => $query,
            ':prefix' => $escapedQuery . '%',
            ':contains' => '%' . $escapedQuery . '%',
        ];
        $unionSql = $this->buildUnionSql();

        $counts = $emptyResult['counts'];
        foreach ($this->fetchAll(
            "SELECT content_type, COUNT(*) AS total
             FROM ({$unionSql}) AS matched_content
             GROUP BY content_type",
            $params
        ) as $row) {
            $contentType = (string)($row['content_type'] ?? '');
            if (isset($counts[$contentType])) {
                $counts[$contentType] = (int)($row['total'] ?? 0);
            }
        }
        $counts['all'] = $counts['product'] + $counts['post'] + $counts['case'];

        $total = $counts[$type];
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $where = '';
        if ($type !== 'all') {
            $where = ' WHERE content_type = :content_type';
            $params[':content_type'] = $type;
        }

        $items = $this->fetchAll(
            "SELECT *
             FROM ({$unionSql}) AS matched_content
             {$where}
             ORDER BY relevance ASC, updated_at DESC, id DESC
             LIMIT :limit OFFSET :offset",
            $params + [':limit' => $perPage, ':offset' => $offset]
        );

        foreach ($items as &$item) {
            $contentType = (string)$item['content_type'];
            if ($contentType === 'product') {
                $images = json_decode((string)($item['media'] ?? '[]'), true);
                $item['cover'] = is_array($images) ? (string)($images[0] ?? '') : '';
                $item['url'] = url('/product/' . $item['slug']);
            } else {
                $item['cover'] = (string)($item['media'] ?? '');
                $item['url'] = url(($contentType === 'case' ? '/case/' : '/blog/') . $item['slug']);
            }

            $excerptSource = trim((string)($item['summary'] ?? ''));
            if ($excerptSource === '') {
                $excerptSource = (string)($item['content'] ?? '');
            }
            $excerpt = html_entity_decode(strip_tags($excerptSource), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $excerpt = trim((string)preg_replace('/\s+/u', ' ', $excerpt));
            $item['excerpt'] = mb_substr($excerpt, 0, 180);
            unset($item['content'], $item['media'], $item['relevance']);
        }
        unset($item);

        return [
            'items' => $items,
            'counts' => $counts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'type' => $type,
        ];
    }

    private function buildUnionSql(): string {
        $productMatch = "(
            products.title LIKE :contains ESCAPE '\\'
            OR products.slug LIKE :contains ESCAPE '\\'
            OR COALESCE(products.summary, '') LIKE :contains ESCAPE '\\'
            OR COALESCE(products.content, '') LIKE :contains ESCAPE '\\'
            OR COALESCE(products.product_type, '') LIKE :contains ESCAPE '\\'
            OR COALESCE(products.vendor, '') LIKE :contains ESCAPE '\\'
            OR COALESCE(products.tags, '') LIKE :contains ESCAPE '\\'
        )";
        $postMatch = "(
            posts.title LIKE :contains ESCAPE '\\'
            OR posts.slug LIKE :contains ESCAPE '\\'
            OR COALESCE(posts.summary, '') LIKE :contains ESCAPE '\\'
            OR COALESCE(posts.content, '') LIKE :contains ESCAPE '\\'
        )";

        return "
            SELECT
                products.id,
                products.title,
                products.slug,
                products.summary,
                products.content,
                products.images_json AS media,
                product_categories.name AS category_name,
                products.created_at,
                products.updated_at,
                'product' AS content_type,
                CASE
                    WHEN products.title = :exact COLLATE NOCASE THEN 0
                    WHEN products.title LIKE :prefix ESCAPE '\\' THEN 1
                    WHEN products.title LIKE :contains ESCAPE '\\' THEN 2
                    WHEN products.slug LIKE :contains ESCAPE '\\' THEN 3
                    WHEN COALESCE(products.summary, '') LIKE :contains ESCAPE '\\' THEN 4
                    ELSE 5
                END AS relevance
            FROM products
            LEFT JOIN product_categories
                ON product_categories.id = products.category_id
                AND product_categories.type = 'product'
            WHERE products.status = 'active'
              AND products.deleted_at IS NULL
              AND {$productMatch}

            UNION ALL

            SELECT
                posts.id,
                posts.title,
                posts.slug,
                posts.summary,
                posts.content,
                posts.cover AS media,
                product_categories.name AS category_name,
                posts.created_at,
                posts.updated_at,
                posts.post_type AS content_type,
                CASE
                    WHEN posts.title = :exact COLLATE NOCASE THEN 0
                    WHEN posts.title LIKE :prefix ESCAPE '\\' THEN 1
                    WHEN posts.title LIKE :contains ESCAPE '\\' THEN 2
                    WHEN posts.slug LIKE :contains ESCAPE '\\' THEN 3
                    WHEN COALESCE(posts.summary, '') LIKE :contains ESCAPE '\\' THEN 4
                    ELSE 5
                END AS relevance
            FROM posts
            LEFT JOIN product_categories
                ON product_categories.id = posts.category_id
                AND product_categories.type = 'post'
            WHERE posts.status = 'active'
              AND posts.post_type IN ('post', 'case')
              AND {$postMatch}
        ";
    }

    private function escapeLike(string $value): string {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
