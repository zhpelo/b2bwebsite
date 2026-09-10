<?php
declare(strict_types=1);

/**
 * 路由注册：集中管理前后台路由，便于维护
 */

use App\Core\Router;
use App\Controllers\SiteController;
use App\Controllers\AdminController;
use App\Controllers\PluginAdminController;
use App\Controllers\PluginCronController;
use App\Controllers\AppStoreController;

function register_routes(Router $router): void {
    $router->add('GET', '/admin/app-store', [AppStoreController::class, 'index']);
    // 插件平台：Cron 使用独立 Token；管理路由由 PluginAdminController 强制管理员认证。
    $router->add('GET', '/plugin-cron', [PluginCronController::class, 'run']);
    $router->add('GET', '/admin/app-store/plugins', [PluginAdminController::class, 'index']);
    $router->add('POST', '/admin/app-store/plugins/upload', [PluginAdminController::class, 'upload']);
    $router->add('GET', '/admin/app-store/plugins/market', [PluginAdminController::class, 'market']);
    $router->add('POST', '/admin/app-store/plugins/install', [PluginAdminController::class, 'installMarket']);
    $router->add('POST', '/admin/app-store/plugins/action', [PluginAdminController::class, 'action']);
    $router->add('GET', '/admin/app-store/plugins/settings', [PluginAdminController::class, 'settings']);
    $router->add('POST', '/admin/app-store/plugins/settings', [PluginAdminController::class, 'saveSettings']);
    $router->add('POST', '/admin/app-store/plugins/jobs/run', [PluginAdminController::class, 'runJobs']);
    $router->add('POST', '/admin/app-store/plugins/jobs/action', [PluginAdminController::class, 'jobAction']);
    // 旧插件 URL 兼容：GET 跳转到统一地址，POST 暂时保留处理能力。
    $router->add('GET', '/admin/plugins', [AppStoreController::class, 'legacyPlugins']);
    $router->add('GET', '/admin/plugins/market', [AppStoreController::class, 'legacyPluginMarket']);
    $router->add('GET', '/admin/plugins/settings', [AppStoreController::class, 'legacyPluginSettings']);
    $router->add('POST', '/admin/plugins/upload', [PluginAdminController::class, 'upload']);
    $router->add('POST', '/admin/plugins/market/install', [PluginAdminController::class, 'installMarket']);
    $router->add('POST', '/admin/plugins/action', [PluginAdminController::class, 'action']);
    $router->add('POST', '/admin/plugins/settings', [PluginAdminController::class, 'saveSettings']);
    $router->add('POST', '/admin/plugins/jobs/run', [PluginAdminController::class, 'runJobs']);
    $router->add('POST', '/admin/plugins/jobs/action', [PluginAdminController::class, 'jobAction']);
    // 前台
    $router->add('GET', '/', [SiteController::class, 'home']);
    $router->add('GET', '/search', [SiteController::class, 'search']);
    $router->add('GET', '/products', [SiteController::class, 'products']);
    $router->add('GET', '/product-category/:slug', [SiteController::class, 'productCategory']);
    $router->add('GET', '/product/:slug', [SiteController::class, 'productDetail']);
    $router->add('GET', '/cases', [SiteController::class, 'cases']);
    $router->add('GET', '/case/:slug', [SiteController::class, 'caseDetail']);
    $router->add('GET', '/blog', [SiteController::class, 'blog']);
    $router->add('GET', '/blog/:slug', [SiteController::class, 'blogDetail']);
    $router->add('GET', '/page/:slug', [SiteController::class, 'pageDetail']);
    $router->add('GET', '/about', [SiteController::class, 'about']);
    $router->add('GET', '/contact', [SiteController::class, 'contact']);
    $router->add('POST', '/contact', [SiteController::class, 'contact']);
    $router->add('POST', '/inquiry', [SiteController::class, 'inquiry']);
    $router->add('GET', '/robots.txt', [SiteController::class, 'robots']);
    $router->add('GET', '/sitemap.xml', [SiteController::class, 'sitemap']);

    // 后台：登录
    $router->add('GET', '/admin/login', [AdminController::class, 'login']);
    $router->add('POST', '/admin/login', [AdminController::class, 'doLogin']);
    $router->add('POST', '/admin/logout', [AdminController::class, 'logout']);
    $router->add('GET', '/admin', [AdminController::class, 'dashboard']);
    $router->add('GET', '/admin/settings-general', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-general', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-company', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-company', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-trade', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-trade', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-media', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-media', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-contact', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-contact', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-translate', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-translate', [AdminController::class, 'saveSettings']);
    $router->add('GET', '/admin/settings-custom', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings-custom', [AdminController::class, 'saveSettings']);

    // 后台：产品
    $router->add('GET', '/admin/products', [AdminController::class, 'productList']);
    $router->add('GET', '/admin/products/selector', [AdminController::class, 'productSelector']);
    $router->add('GET', '/admin/products/create', [AdminController::class, 'productCreate']);
    $router->add('POST', '/admin/products/create', [AdminController::class, 'productStore']);
    $router->add('GET', '/admin/products/edit', [AdminController::class, 'productEdit']);
    $router->add('POST', '/admin/products/edit', [AdminController::class, 'productUpdate']);
    $router->add('POST', '/admin/products/delete', [AdminController::class, 'productDelete']);
    $router->add('POST', '/admin/products/restore', [AdminController::class, 'productRestore']);
    $router->add('POST', '/admin/products/permanent-delete', [AdminController::class, 'productPermanentDelete']);
    $router->add('POST', '/admin/products/bulk', [AdminController::class, 'productBulkAction']);

    // 后台：产品分类
    $router->add('GET', '/admin/product-categories', [AdminController::class, 'productCategoryList']);
    $router->add('GET', '/admin/product-categories/create', [AdminController::class, 'productCategoryCreate']);
    $router->add('POST', '/admin/product-categories/create', [AdminController::class, 'productCategoryStore']);
    $router->add('GET', '/admin/product-categories/edit', [AdminController::class, 'productCategoryEdit']);
    $router->add('POST', '/admin/product-categories/edit', [AdminController::class, 'productCategoryUpdate']);
    $router->add('POST', '/admin/product-categories/delete', [AdminController::class, 'productCategoryDelete']);

    // 后台：文章分类
    $router->add('GET', '/admin/post-categories', [AdminController::class, 'postCategoryList']);
    $router->add('GET', '/admin/post-categories/create', [AdminController::class, 'postCategoryCreate']);
    $router->add('POST', '/admin/post-categories/create', [AdminController::class, 'postCategoryStore']);
    $router->add('GET', '/admin/post-categories/edit', [AdminController::class, 'postCategoryEdit']);
    $router->add('POST', '/admin/post-categories/edit', [AdminController::class, 'postCategoryUpdate']);
    $router->add('POST', '/admin/post-categories/delete', [AdminController::class, 'postCategoryDelete']);

    // 后台：案例
    $router->add('GET', '/admin/cases', [AdminController::class, 'caseList']);
    $router->add('GET', '/admin/cases/create', [AdminController::class, 'caseCreate']);
    $router->add('POST', '/admin/cases/create', [AdminController::class, 'caseStore']);
    $router->add('GET', '/admin/cases/edit', [AdminController::class, 'caseEdit']);
    $router->add('POST', '/admin/cases/edit', [AdminController::class, 'caseUpdate']);
    $router->add('POST', '/admin/cases/delete', [AdminController::class, 'caseDelete']);

    // 后台：文章
    $router->add('GET', '/admin/posts', [AdminController::class, 'postList']);
    $router->add('GET', '/admin/posts/create', [AdminController::class, 'postCreate']);
    $router->add('POST', '/admin/posts/create', [AdminController::class, 'postStore']);
    $router->add('GET', '/admin/posts/edit', [AdminController::class, 'postEdit']);
    $router->add('POST', '/admin/posts/edit', [AdminController::class, 'postUpdate']);
    $router->add('POST', '/admin/posts/delete', [AdminController::class, 'postDelete']);

    // 后台：页面
    $router->add('GET', '/admin/pages', [AdminController::class, 'pageList']);
    $router->add('GET', '/admin/pages/create', [AdminController::class, 'pageCreate']);
    $router->add('POST', '/admin/pages/create', [AdminController::class, 'pageStore']);
    $router->add('GET', '/admin/pages/edit', [AdminController::class, 'pageEdit']);
    $router->add('POST', '/admin/pages/edit', [AdminController::class, 'pageUpdate']);
    $router->add('POST', '/admin/pages/delete', [AdminController::class, 'pageDelete']);

    // 后台：留言与询单
    $router->add('GET', '/admin/messages', [AdminController::class, 'messageList']);
    $router->add('GET', '/admin/messages/detail', [AdminController::class, 'messageDetail']);
    $router->add('POST', '/admin/messages/delete', [AdminController::class, 'messageDelete']);
    $router->add('GET', '/admin/inquiries', [AdminController::class, 'inquiryList']);
    $router->add('GET', '/admin/inquiries/detail', [AdminController::class, 'inquiryDetail']);
    $router->add('POST', '/admin/inquiries/status', [AdminController::class, 'inquiryUpdateStatus']);
    $router->add('POST', '/admin/inquiries/delete', [AdminController::class, 'inquiryDelete']);
    $router->add('GET', '/admin/inquiries/export', [AdminController::class, 'inquiryExport']);

    // 后台：员工
    $router->add('GET', '/admin/staff', [AdminController::class, 'staffList']);
    $router->add('GET', '/admin/staff/create', [AdminController::class, 'staffCreate']);
    $router->add('POST', '/admin/staff/create', [AdminController::class, 'staffStore']);
    $router->add('GET', '/admin/staff/edit', [AdminController::class, 'staffEdit']);
    $router->add('POST', '/admin/staff/edit', [AdminController::class, 'staffUpdate']);
    $router->add('POST', '/admin/staff/delete', [AdminController::class, 'staffDelete']);

    // 后台：个人资料、媒体、AJAX
    $router->add('GET', '/admin/profile', [AdminController::class, 'profile']);
    $router->add('POST', '/admin/profile/update', [AdminController::class, 'profileUpdate']);
    $router->add('GET', '/admin/media', [AdminController::class, 'mediaList']);
    $router->add('GET', '/admin/media/connector', [AdminController::class, 'mediaConnector']);
    $router->add('POST', '/admin/media/delete', [AdminController::class, 'mediaDelete']);
    $router->add('POST', '/admin/media/update', [AdminController::class, 'mediaUpdate']);
    $router->add('POST', '/admin/media/upload', [AdminController::class, 'mediaUpload']);
    $router->add('POST', '/admin/media/folder/create', [AdminController::class, 'mediaFolderCreate']);
    $router->add('POST', '/admin/media/folder/delete', [AdminController::class, 'mediaFolderDelete']);
    $router->add('POST', '/admin/media/connector', [AdminController::class, 'mediaConnector']);
    $router->add('POST', '/admin/upload-image', [AdminController::class, 'uploadImage']);
    $router->add('GET', '/admin/media-library', [AdminController::class, 'mediaLibrary']);

    // 后台：程序更新
    $router->add('GET', '/admin/settings-updater', [AdminController::class, 'updaterIndex']);
    $router->add('POST', '/admin/settings-updater/download', [AdminController::class, 'updaterDownload']);
    $router->add('POST', '/admin/settings-updater/install', [AdminController::class, 'updaterInstall']);
    $router->add('POST', '/admin/settings-updater/delete-backup', [AdminController::class, 'updaterDeleteBackup']);
    $router->add('GET', '/admin/settings-updater/migrations', [AdminController::class, 'updaterMigrationStatus']);
    $router->add('POST', '/admin/settings-updater/migrations/run', [AdminController::class, 'updaterRunMigrations']);

    // 后台：外观区块 - 网站模版
    $router->add('GET', '/admin/appearance', [AppStoreController::class, 'legacyThemes']);
    $router->add('GET', '/admin/app-store/themes', [AdminController::class, 'themeList']);
    $router->add('GET', '/admin/app-store/themes/upload', [AdminController::class, 'themeUploadForm']);
    $router->add('GET', '/admin/app-store/themes/:id', [AdminController::class, 'themeAppStoreDetail']);
    $router->add('POST', '/admin/app-store/themes/upload', [AdminController::class, 'themeUpload']);
    $router->add('POST', '/admin/app-store/themes/activate', [AdminController::class, 'themeActivate']);
    $router->add('POST', '/admin/app-store/themes/delete', [AdminController::class, 'themeDelete']);
    $router->add('POST', '/admin/app-store/themes/settings', [AdminController::class, 'themeAppStoreSettings']);
    $router->add('POST', '/admin/app-store/themes/install', [AdminController::class, 'themeAppStoreInstall']);
    $router->add('POST', '/admin/app-store/themes/purchase', [AdminController::class, 'themeAppStorePurchase']);
    // 旧主题 URL 兼容。
    $router->add('GET', '/admin/appearance/themes', [AppStoreController::class, 'legacyThemes']);
    $router->add('GET', '/admin/appearance/themes/upload', [AppStoreController::class, 'legacyThemeUpload']);
    $router->add('GET', '/admin/appearance/themes/app-store/:id', [AppStoreController::class, 'legacyThemeDetail']);
    $router->add('POST', '/admin/appearance/themes/upload', [AdminController::class, 'themeUpload']);
    $router->add('POST', '/admin/appearance/themes/activate', [AdminController::class, 'themeActivate']);
    $router->add('POST', '/admin/appearance/themes/delete', [AdminController::class, 'themeDelete']);
    $router->add('POST', '/admin/appearance/themes/app-store/settings', [AdminController::class, 'themeAppStoreSettings']);
    $router->add('POST', '/admin/appearance/themes/app-store/install', [AdminController::class, 'themeAppStoreInstall']);
    $router->add('POST', '/admin/appearance/themes/app-store/purchase', [AdminController::class, 'themeAppStorePurchase']);

    // 后台：外观区块 - 轮播图管理
    $router->add('GET', '/admin/appearance/sliders', [AdminController::class, 'sliderList']);
    $router->add('GET', '/admin/appearance/sliders/create', [AdminController::class, 'sliderCreate']);
    $router->add('POST', '/admin/appearance/sliders/create', [AdminController::class, 'sliderStore']);
    $router->add('GET', '/admin/appearance/sliders/edit', [AdminController::class, 'sliderEdit']);
    $router->add('POST', '/admin/appearance/sliders/edit', [AdminController::class, 'sliderUpdate']);
    $router->add('POST', '/admin/appearance/sliders/delete', [AdminController::class, 'sliderDelete']);

    // 后台：外观区块 - 模板区块配置
    $router->add('GET', '/admin/appearance/blocks', [AdminController::class, 'blockList']);
    $router->add('POST', '/admin/appearance/blocks/save', [AdminController::class, 'blockSave']);

    // 后台：外观区块 - 菜单管理
    $router->add('GET', '/admin/appearance/menus', [AdminController::class, 'menuList']);
    $router->add('GET', '/admin/appearance/menus/create', [AdminController::class, 'menuCreate']);
    $router->add('POST', '/admin/appearance/menus/create', [AdminController::class, 'menuStore']);
    $router->add('GET', '/admin/appearance/menus/edit', [AdminController::class, 'menuEdit']);
    $router->add('POST', '/admin/appearance/menus/edit', [AdminController::class, 'menuUpdate']);
    $router->add('POST', '/admin/appearance/menus/delete', [AdminController::class, 'menuDelete']);
}
