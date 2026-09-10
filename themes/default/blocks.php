<?php
/**
 * 默认主题 - 区块配置文件
 * 
 * 定义所有可在后台自定义的模板区块内容。
 * 每个区块包含：label（后台显示名）、fields（字段列表）。
 * 字段类型：text / textarea / image / media / icon / color / select / repeater
 * 
 * 前台模板通过 block('区块key', '字段key') 读取值。
 * 用户修改保存在 storage/blocks/{theme}.php，不修改本文件。
 */

$sliderOptions = [];
try {
    if (class_exists(\App\Models\Slider::class)) {
        $sliderModel = new \App\Models\Slider();
        foreach ($sliderModel->getAll() as $slider) {
            if (($slider['status'] ?? 'active') !== 'active') {
                continue;
            }

            $slug = trim((string)($slider['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $name = trim((string)($slider['name'] ?? ''));
            $sliderOptions[$slug] = ($name !== '' ? $name : $slug) . ' (' . $slug . ')';
        }
    }
} catch (\Throwable $e) {
    $sliderOptions = [];
}

if (empty($sliderOptions)) {
    $sliderOptions = ['home-hero' => '首页轮播图 (home-hero)'];
}

$defaultHeroSlider = (string)array_key_first($sliderOptions);

$menuOptions = [];
try {
    if (class_exists(\App\Models\Menu::class)) {
        $menuModel = new \App\Models\Menu();
        foreach ($menuModel->getAll() as $menu) {
            $slug = trim((string)($menu['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $name = trim((string)($menu['name'] ?? ''));
            $status = (string)($menu['status'] ?? 'active');
            $menuOptions[$slug] = ($name !== '' ? $name : $slug) . ' (' . $slug . ')' . ($status === 'active' ? '' : ' - 已禁用');
        }
    }
} catch (\Throwable $e) {
    $menuOptions = [];
}

$defaultFooterMenu = (string)(array_key_first($menuOptions) ?? '');

return [

    // ============================================================
    // 首页 - 首屏轮播
    // ============================================================
    'home_hero' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '首屏轮播',
        'description' => '图片、标题、描述、按钮来自 外观区块 -> 轮播图；这里选择使用哪个轮播图区块。',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'slider_slug' => [
                'type' => 'select',
                'label' => '选择轮播图区块',
                'default' => $defaultHeroSlider,
                'options' => $sliderOptions,
            ],
            'autoplay' => [
                'type' => 'select',
                'label' => '自动轮播',
                'default' => 'yes',
                'options' => ['yes' => '启用', 'no' => '关闭'],
            ],
            'show_overlay' => [
                'type' => 'select',
                'label' => '显示文字层',
                'default' => 'yes',
                'options' => ['yes' => '显示', 'no' => '隐藏'],
            ],
            'fallback_label' => ['type' => 'text', 'label' => '首屏标记文字', 'default' => 'Featured Products'],
        ],
    ],

    // ============================================================
    // 首页 - 优势卖点区块（3列）
    // ============================================================
    'home_value_props' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '优势卖点',
        'description' => '首页轮播下方的 3 列优势卖点',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'prop_one_icon'  => ['type' => 'icon',  'label' => 'Item 1 Icon',  'default' => 'fas fa-check-circle'],
            'prop_one_heading' => ['type' => 'text',  'label' => 'Item 1 Title', 'default' => 'Quality Assurance'],
            'prop_one_desc'  => ['type' => 'text',  'label' => 'Item 1 Description', 'default' => 'ISO-aligned production with strict QC before shipment.'],
            'prop_two_icon'  => ['type' => 'icon',  'label' => 'Item 2 Icon',  'default' => 'fas fa-globe-americas'],
            'prop_two_heading' => ['type' => 'text',  'label' => 'Item 2 Title', 'default' => 'Global Logistics'],
            'prop_two_desc'  => ['type' => 'text',  'label' => 'Item 2 Description', 'default' => 'On-time delivery with consolidated freight options.'],
            'prop_three_icon'  => ['type' => 'icon',  'label' => 'Item 3 Icon',  'default' => 'fas fa-user-shield'],
            'prop_three_heading' => ['type' => 'text',  'label' => 'Item 3 Title', 'default' => 'Dedicated Support'],
            'prop_three_desc'  => ['type' => 'text',  'label' => 'Item 3 Description', 'default' => 'One-to-one account service for long-term buyers.'],
        ],
    ],

    // ============================================================
    // 首页 - 精选产品区块
    // ============================================================
    'home_featured' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '精选产品',
        'description' => '精选产品板块标题文字',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Featured Products'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Company Highlights'],
            'text'       => ['type' => 'textarea', 'label' => 'Description', 'default' => 'Selected products for global B2B buyers.'],
            'product_ids' => [
                'type' => 'product_picker',
                'label' => '勾选商品',
                'default' => '',
                'limit' => 8,
                'status' => 'active',
            ],
            'link_text'  => ['type' => 'text', 'label' => 'View All Text', 'default' => 'View All →'],
            'detail_text' => ['type' => 'text', 'label' => '详情按钮文字', 'default' => 'View Details'],
            'empty_text' => ['type' => 'text', 'label' => '无产品提示', 'default' => 'No products available.'],
        ],
    ],

    // ============================================================
    // 首页 - Why Choose Us 区块
    // ============================================================
    'home_why_us' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '为什么选择我们',
        'description' => '为什么选择我们板块',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Why Choose Us'],
            'badge1'     => ['type' => 'text', 'label' => 'Badge 1',   'default' => 'ISO Certified'],
            'badge2'     => ['type' => 'text', 'label' => 'Badge 2',   'default' => 'OEM & ODM'],
            'badge3'     => ['type' => 'text', 'label' => 'Badge 3',   'default' => 'R&D Team'],
            'link_text'  => ['type' => 'text', 'label' => 'Button Text', 'default' => 'About Us'],
            'media_type' => [
                'type' => 'select',
                'label' => '媒体类型',
                'default' => 'image',
                'options' => ['image' => '图片', 'video' => '视频'],
            ],
            'media' => [
                'type' => 'media',
                'label' => '展示媒体',
                'default' => '',
                'allowed' => 'all',
                'media_type_key' => 'media_type',
                'legacy_image_key' => 'image',
                'default_media_type' => 'image',
            ],
        ],
    ],

    // ============================================================
    // 首页 - 公司展示区块
    // ============================================================
    'home_company_show' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '公司展示',
        'description' => '展示后台系统设置中的公司展示图片。',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Company Show'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'A closer look at our production environment, team, and quality control process.'],
            'link_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'View Company Profile →'],
        ],
    ],

    // ============================================================
    // 首页 - 资质证书区块
    // ============================================================
    'home_certificates' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '资质证书',
        'description' => '展示后台系统设置中的资质证书图片。',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Certificates & Compliance'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Verified documentation and supplier capabilities for confident B2B purchasing.'],
        ],
    ],

    // ============================================================
    // 首页 - 案例区块
    // ============================================================
    'home_cases' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '成功案例',
        'description' => '成功案例板块标题',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Success Cases'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Global Presence'],
        ],
    ],

    // ============================================================
    // 首页 - 文章区块
    // ============================================================
    'home_articles' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '文章资讯',
        'description' => '展示最新发布的文章。',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Latest Insights'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Industry knowledge, sourcing guides, and company updates for global buyers.'],
            'link_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'View All Articles →'],
            'empty_text' => ['type' => 'text', 'label' => 'Empty Text', 'default' => 'No articles available.'],
        ],
    ],

    // ============================================================
    // 首页 - 底部 CTA 区块
    // ============================================================
    'home_cta' => [
        'group' => 'home',
        'group_label' => '首页',
        'label' => '底部行动区',
        'description' => '首页底部行动号召区块',
        'fields' => [
            'is_visible' => ['type' => 'select', 'label' => '是否显示', 'default' => 'yes', 'options' => ['yes' => '显示', 'no' => '隐藏']],
            'heading'    => ['type' => 'text', 'label' => 'Heading',     'default' => 'Ready to start your project?'],
            'text'       => ['type' => 'text', 'label' => 'Description', 'default' => 'Contact us today for a professional quote and expert consultation.'],
            'btn1_text'  => ['type' => 'text', 'label' => 'Button 1 Text', 'default' => 'Request Quote'],
            'btn2_text'  => ['type' => 'text', 'label' => 'Button 2 Text (WhatsApp)', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // 页头 - Header 区块
    // ============================================================
    'header' => [
        'group' => 'global',
        'group_label' => '全站通用',
        'label' => '页头导航',
        'description' => '网站顶部导航栏',
        'fields' => [
            'search_placeholder' => ['type' => 'text', 'label' => 'Search Placeholder', 'default' => 'Search products, articles and cases'],
            'cta_text' => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => 'Request Quote'],
            'cta_url'  => ['type' => 'text', 'label' => 'CTA Button URL',  'default' => '/contact'],
        ],
    ],

    // ============================================================
    // 页脚 - Footer 区块
    // ============================================================
    'footer' => [
        'group' => 'global',
        'group_label' => '全站通用',
        'label' => '页脚信息',
        'description' => '网站底部信息',
        'fields' => [
            'quick_links_title' => ['type' => 'text', 'label' => 'Quick Links Title', 'default' => 'Quick Links'],
            'quick_links_menu_slug' => [
                'type' => 'select',
                'label' => 'Quick Links Menu',
                'default' => $defaultFooterMenu,
                'options' => $menuOptions,
            ],
            'contact_title'     => ['type' => 'text', 'label' => 'Contact Title',     'default' => 'Contact'],
            'copyright'         => ['type' => 'text', 'label' => 'Copyright Text',    'default' => ''],
        ],
    ],

    // ============================================================
    // 浮动联系窗口
    // ============================================================
    'float_contact' => [
        'group' => 'global',
        'group_label' => '全站通用',
        'label' => '悬浮联系窗口',
        'description' => '右下角/左侧悬浮联系窗口',
        'fields' => [
            'toggle_text'  => ['type' => 'text', 'label' => 'Toggle Button Text', 'default' => 'Contact'],
            'eyebrow'      => ['type' => 'text', 'label' => 'Eyebrow Text',   'default' => 'Global Contact'],
            'title'        => ['type' => 'text', 'label' => 'Panel Title',    'default' => 'Talk to our team'],
            'desc'         => ['type' => 'text', 'label' => 'Panel Description', 'default' => 'Quick access to your configured contact details and social channels.'],
            'cta_text'     => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => 'Send Inquiry'],
            'cta_url'      => ['type' => 'text', 'label' => 'CTA Button URL',  'default' => '/contact'],
        ],
    ],

    // ============================================================
    // Contact 页面
    // ============================================================
    'page_contact' => [
        'group' => 'contact',
        'group_label' => '联系我们页面',
        'label' => '联系我们页内容',
        'description' => '联系我们页面',
        'fields' => [
            'label'       => ['type' => 'text', 'label' => 'Page Label',    'default' => 'Contact'],
            'heading'     => ['type' => 'text', 'label' => 'Page Heading',  'default' => 'Contact Us'],
            'form_title'  => ['type' => 'text', 'label' => 'Form Title',    'default' => 'Send Message'],
            'form_subtitle' => ['type' => 'text', 'label' => 'Form Subtitle', 'default' => 'Project requirements, customization, etc.'],
            'form_btn'    => ['type' => 'text', 'label' => 'Form Submit Button', 'default' => 'Send Message'],
            'response_label' => ['type' => 'text', 'label' => 'Response Time Label', 'default' => 'Avg. Response Time'],
            'markets_label'  => ['type' => 'text', 'label' => 'Markets Label', 'default' => 'Main Markets'],
            'chat_btn'    => ['type' => 'text', 'label' => 'WhatsApp Button', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // About 页面
    // ============================================================
    'page_about' => [
        'group' => 'about',
        'group_label' => '关于我们页面',
        'label' => '关于我们页内容',
        'description' => '关于我们页面',
        'fields' => [
            'label'       => ['type' => 'text', 'label' => 'Page Label',    'default' => 'Company Profile'],
            'cta_btn1'    => ['type' => 'text', 'label' => 'CTA Button 1',  'default' => 'Send My Inquiry'],
            'cta_btn2'    => ['type' => 'text', 'label' => 'CTA Button 2',  'default' => 'Book a Factory Tour'],
            'company_show_title' => ['type' => 'text', 'label' => 'Gallery Title', 'default' => 'Company Show'],
            'certificates_title' => ['type' => 'text', 'label' => 'Certificates Title', 'default' => 'Certificates'],
            'sidebar_title'   => ['type' => 'text', 'label' => 'Sidebar Title',  'default' => 'Contact Provider'],
            'sidebar_btn'     => ['type' => 'text', 'label' => 'Sidebar Button', 'default' => 'Send My Inquiry'],
            'sidebar_chat'    => ['type' => 'text', 'label' => 'Sidebar Chat Button', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // 产品列表页
    // ============================================================
    'product_list' => [
        'group' => 'products',
        'group_label' => '产品页面',
        'label' => '产品列表页',
        'description' => '产品列表 Banner、分类、询盘卡片与空状态文案。',
        'fields' => [
            'text' => ['type' => 'textarea', 'label' => '默认描述', 'default' => 'Browse our full range of products.'],
            'categories_title' => ['type' => 'text', 'label' => '分类标题', 'default' => 'Categories'],
            'all_text' => ['type' => 'text', 'label' => '全部产品文字', 'default' => 'All Products'],
            'quote_title' => ['type' => 'text', 'label' => '询盘卡片标题', 'default' => 'Quick Quote'],
            'quote_text' => ['type' => 'textarea', 'label' => '询盘卡片描述', 'default' => 'Found what you need? Send an inquiry to get a quote now.'],
            'quote_btn' => ['type' => 'text', 'label' => '询盘按钮文字', 'default' => 'Contact'],
            'empty_text' => ['type' => 'text', 'label' => '无产品提示', 'default' => 'No products found'],
            'detail_text' => ['type' => 'text', 'label' => '产品详情按钮', 'default' => 'View Details'],
        ],
    ],

    // ============================================================
    // 产品详情页
    // ============================================================
    'product_detail' => [
        'group' => 'products',
        'group_label' => '产品页面',
        'label' => '产品详情页',
        'description' => '产品详情页辅助文案。',
        'fields' => [
            'description_title' => ['type' => 'text', 'label' => '描述标题', 'default' => 'Product Description'],
            'inquiry_text' => ['type' => 'text', 'label' => '询盘按钮', 'default' => 'Send Inquiry'],
            'chat_text' => ['type' => 'text', 'label' => 'WhatsApp按钮', 'default' => 'Chat Now'],
            'sample_text' => ['type' => 'text', 'label' => '样品提示', 'default' => 'Still deciding? Get samples of'],
            'sample_link_text' => ['type' => 'text', 'label' => '样品链接文字', 'default' => 'Request Sample'],
            'price_range_label' => ['type' => 'text', 'label' => '价格区间标题', 'default' => 'Price Range'],
            'price_range_note' => ['type' => 'text', 'label' => '价格区间说明', 'default' => 'Final price depends on order quantity and customization.'],
            'negotiable_label' => ['type' => 'text', 'label' => '面谈价格标题', 'default' => 'Negotiable Price'],
            'negotiable_text' => ['type' => 'text', 'label' => '面谈价格说明', 'default' => 'Send your quantity and requirements to receive a tailored quotation.'],
            'more_category_text' => ['type' => 'text', 'label' => '更多分类产品', 'default' => 'More in this category'],
            'related_heading' => ['type' => 'text', 'label' => '相关产品标题', 'default' => 'Related Products'],
            'related_product_ids' => [
                'type' => 'product_picker',
                'label' => '人工选择相关产品',
                'default' => '',
                'limit' => 4,
                'status' => 'active',
            ],
        ],
    ],

    // ============================================================
    // 品牌颜色
    // ============================================================
    'brand_colors' => [
        'group' => 'brand',
        'group_label' => '品牌与样式',
        'label' => '品牌颜色',
        'description' => '品牌主色调（影响按钮、链接等）',
        'fields' => [
            'primary'    => ['type' => 'color', 'label' => 'Primary Color (Accent)', 'default' => '#0ea5e9'],
            'primary_dark' => ['type' => 'color', 'label' => 'Primary Dark (Hover)', 'default' => '#0284c7'],
            'ink'        => ['type' => 'color', 'label' => 'Text Dark',    'default' => '#0f172a'],
            'muted'      => ['type' => 'color', 'label' => 'Text Muted',   'default' => '#475569'],
            'surface'    => ['type' => 'color', 'label' => 'Background',    'default' => '#ffffff'],
            'border'     => ['type' => 'color', 'label' => 'Border Color',  'default' => '#e2e8f0'],
        ],
    ],

];
