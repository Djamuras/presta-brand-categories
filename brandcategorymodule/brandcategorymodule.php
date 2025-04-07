<?php
/**
 * Brand Category Module for PrestaShop
 *
 * @package   BrandCategoryModule
 * @version   1.0.2
 * @author    Your Name
 * @copyright Your Company
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrandCategoryModule extends Module
{
    public function __construct()
    {
        $this->name = 'brandcategorymodule';
        $this->tab = 'administration';
        $this->version = '1.0.2';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.1.2',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Brand Category Manager');
        $this->description = $this->l('Allows creation of brand-specific product categories');
    }

    public function install()
    {
        return parent::install() &&
            $this->createBrandCategoryTables() &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayBrandContent') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayAdminProductsMainStepRightColumnBottom');
    }

    public function uninstall()
    {
        return $this->dropBrandCategoryTables() &&
            parent::uninstall();
    }

    /**
     * Create custom tables for brand categories
     */
    protected function createBrandCategoryTables()
    {
        $sql = [];

        // Table for brand-specific categories
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'brand_category` (
            `id_brand_category` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_manufacturer` INT(10) UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_brand_category`),
            KEY `id_manufacturer` (`id_manufacturer`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4';

        // Table for linking products to brand categories
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'brand_category_product` (
            `id_brand_category` INT(10) UNSIGNED NOT NULL,
            `id_product` INT(10) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_brand_category`, `id_product`),
            KEY `id_product` (`id_product`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4';

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Drop custom tables when uninstalling
     */
    protected function dropBrandCategoryTables()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'brand_category`';
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'brand_category_product`';

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Display additional fields in product page
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        // Get current product ID
        $id_product = (int)$params['id_product'];

        // Get all manufacturers
        $manufacturers = Manufacturer::getManufacturers();

        // Get current product's brand categories
        $current_brand_categories = $this->getProductBrandCategories($id_product);

        // Get all brand categories
        $brand_categories = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'brand_category 
            WHERE active = 1 
            ORDER BY id_manufacturer, name'
        );

        // Assign to smarty
        $this->context->smarty->assign([
            'manufacturers' => $manufacturers,
            'brand_categories' => $brand_categories,
            'current_brand_categories' => $current_brand_categories,
            'product_id' => $id_product
        ]);

        return $this->display(__FILE__, 'views/templates/admin/product_brand_categories.tpl');
    }

    /**
     * Display brand categories in product creation/edit form (alternative hook)
     */
    public function hookDisplayAdminProductsMainStepRightColumnBottom($params)
    {
        // Get current product ID (0 for new product)
        $id_product = (int)$params['id_product'];

        // If editing an existing product, get its manufacturer
        $manufacturer_id = 0;
        if ($id_product > 0) {
            $product = new Product($id_product);
            $manufacturer_id = $product->id_manufacturer;
        }

        // Get all manufacturers
        $manufacturers = Manufacturer::getManufacturers();

        // Get current product's brand categories
        $current_brand_categories = $this->getProductBrandCategories($id_product);

        // Get brand categories for the product's manufacturer
        $brand_categories = Db::getInstance()->executeS(
            'SELECT bc.*, m.name as manufacturer_name 
            FROM '._DB_PREFIX_.'brand_category bc
            INNER JOIN '._DB_PREFIX_.'manufacturer m ON bc.id_manufacturer = m.id_manufacturer
            WHERE bc.active = 1 
            ' . ($manufacturer_id > 0 ? 'AND bc.id_manufacturer = '.(int)$manufacturer_id : '') . '
            ORDER BY m.name, bc.name'
        );

        // Group brand categories by manufacturer
        $grouped_categories = [];
        foreach ($brand_categories as $category) {
            $grouped_categories[$category['manufacturer_name']][] = $category;
        }

        // Assign to smarty
        $this->context->smarty->assign([
            'manufacturers' => $manufacturers,
            'grouped_categories' => $grouped_categories,
            'current_brand_categories' => $current_brand_categories,
            'product_id' => $id_product
        ]);

        return $this->display(__FILE__, 'views/templates/admin/product_brand_categories.tpl');
    }

    /**
     * Save brand categories when product is updated
     */
    public function hookActionProductUpdate($params)
    {
        $id_product = (int)$params['id_product'];
        $brand_categories = Tools::getValue('brand_categories', []);

        try {
            // Remove existing brand category associations
            Db::getInstance()->delete(
                'brand_category_product', 
                'id_product = '.(int)$id_product
            );

            // Add new brand category associations
            if (!empty($brand_categories)) {
                $insert_data = [];
                foreach ($brand_categories as $id_brand_category) {
                    $insert_data[] = [
                        'id_brand_category' => (int)$id_brand_category,
                        'id_product' => (int)$id_product
                    ];
                }
                
                // Validate categories belong to product's manufacturer
                $product = new Product($id_product);
                $valid_categories = Db::getInstance()->executeS(
                    'SELECT id_brand_category FROM '._DB_PREFIX_.'brand_category 
                    WHERE id_manufacturer = '.(int)$product->id_manufacturer
                );
                
                $valid_category_ids = array_column($valid_categories, 'id_brand_category');
                
                $filtered_insert_data = array_filter($insert_data, function($item) use ($valid_category_ids) {
                    return in_array($item['id_brand_category'], $valid_category_ids);
                });

                if (!empty($filtered_insert_data)) {
                    Db::getInstance()->insert('brand_category_product', $filtered_insert_data);
                }
            }
        } catch (Exception $e) {
            // Log error or handle it as needed
            PrestaShopLogger::addLog(
                'Brand Category Module: Error updating product categories - ' . $e->getMessage(), 
                3 // Error level
            );
        }
    }

    /**
     * Get product's brand categories
     */
    public function getProductBrandCategories($id_product)
    {
        return Db::getInstance()->executeS(
            'SELECT bc.* FROM '._DB_PREFIX_.'brand_category bc
            INNER JOIN '._DB_PREFIX_.'brand_category_product bcp 
            ON bc.id_brand_category = bcp.id_brand_category
            WHERE bcp.id_product = '.(int)$id_product
        );
    }

    /**
     * Configuration page
     */
    public function getContent()
    {
        $output = null;

        // Process form submission for creating brand categories
        if (Tools::isSubmit('submitBrandCategory')) {
            $manufacturer_id = (int)Tools::getValue('manufacturer_id');
            $category_name = Tools::getValue('category_name');
            $category_description = Tools::getValue('category_description');

            if (!$manufacturer_id) {
                $output .= $this->displayError($this->l('Please select a manufacturer'));
            } elseif (empty($category_name)) {
                $output .= $this->displayError($this->l('Please enter a category name'));
            } else {
                // Insert new brand category
                $result = Db::getInstance()->insert('brand_category', [
                    'id_manufacturer' => $manufacturer_id,
                    'name' => pSQL($category_name),
                    'description' => pSQL($category_description),
                    'active' => 1,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s')
                ]);

                if ($result) {
                    $output .= $this->displayConfirmation($this->l('Brand category created successfully'));
                } else {
                    $output .= $this->displayError($this->l('Error creating brand category'));
                }
            }
        }

        // Fetch existing brand categories
        $brand_categories = Db::getInstance()->executeS(
            'SELECT bc.*, m.name as manufacturer_name 
            FROM '._DB_PREFIX_.'brand_category bc
            LEFT JOIN '._DB_PREFIX_.'manufacturer m ON bc.id_manufacturer = m.id_manufacturer
            ORDER BY m.name, bc.name'
        );

        // Get manufacturers
        $manufacturers = Manufacturer::getManufacturers();

        // Assign to smarty
        $this->context->smarty->assign([
            'brand_categories' => $brand_categories,
            'manufacturers' => $manufacturers
        ]);

        // Render configuration page
        return $output . $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * Hook to display brand categories on brand page
     */
    public function hookDisplayBrandContent($params)
    {
        // Ensure manufacturer is valid
        if (!isset($params['manufacturer']) || !$params['manufacturer']) {
            return '';
        }

        $id_manufacturer = (int)$params['manufacturer']->id;
        $id_lang = (int)$this->context->language->id;
        
        // Fetch brand categories for this manufacturer with product count
        $sql = '
            SELECT bc.id_brand_category, 
                   bc.name, 
                   bc.description, 
                   m.name as manufacturer_name,
                   (
                       SELECT COUNT(DISTINCT bcp.id_product) 
                       FROM '._DB_PREFIX_.'brand_category_product bcp
                       INNER JOIN '._DB_PREFIX_.'product p ON bcp.id_product = p.id_product
                       WHERE bcp.id_brand_category = bc.id_brand_category
                       AND p.active = 1
                   ) as product_count
            FROM '._DB_PREFIX_.'brand_category bc
            INNER JOIN '._DB_PREFIX_.'manufacturer m ON bc.id_manufacturer = m.id_manufacturer
            WHERE bc.id_manufacturer = '.(int)$id_manufacturer.'
            AND bc.active = 1
            HAVING product_count > 0
        ';

        $brand_categories = Db::getInstance()->executeS($sql);

        // If no categories found, return empty
        if (empty($brand_categories)) {
            return '';
        }

        // Prepare categories with their products
        foreach ($brand_categories as &$category) {
            // Fetch products for each category
            $category['products'] = $this->getBrandCategoryProducts(
                $category['id_brand_category'], 
                $id_lang, 
                6 // Limit to 6 products per category
            );
        }

        // Prepare manufacturer link
        $manufacturer_link = $this->context->link->getManufacturerLink(
            $params['manufacturer']->id, 
            $params['manufacturer']->link_rewrite
        );

        // Assign to smarty with additional data
        $this->context->smarty->assign([
            'brand_categories' => $brand_categories,
            'manufacturer' => $params['manufacturer'],
            'manufacturer_link' => $manufacturer_link
        ]);

        // Return the rendered template
        return $this->display(__FILE__, 'views/templates/front/brand_categories.tpl');
    }

    /**
     * Get products for a specific brand category
     */
    public function getBrandCategoryProducts($id_brand_category, $id_lang, $limit = null)
    {
        $sql = '
            SELECT p.id_product, 
                   pl.name, 
                   pl.description_short, 
                   pl.link_rewrite,
                   img.id_image,
                   p.price,
                   p.reference
            FROM '._DB_PREFIX_.'brand_category_product bcp
            INNER JOIN '._DB_PREFIX_.'product p 
                ON bcp.id_product = p.id_product
            INNER JOIN '._DB_PREFIX_.'product_lang pl 
                ON p.id_product = pl.id_product AND pl.id_lang = '.(int)$id_lang.'
            LEFT JOIN '._DB_PREFIX_.'image img 
                ON p.id_product = img.id_product AND img.cover = 1
            WHERE bcp.id_brand_category = '.(int)$id_brand_category.'
            AND p.active = 1
        ';
    
        if ($limit) {
            $sql .= ' LIMIT '.(int)$limit;
        }
    
        $products = Db::getInstance()->executeS($sql);
    
        // Prepare products with full details
        $prepared_products = [];
        foreach ($products as $product) {
            $prepared_products[] = [
                'id' => $product['id_product'],
                'name' => $product['name'],
                'description_short' => $product['description_short'],
                'link_rewrite' => $product['link_rewrite'],
                'price' => Product::getPriceStatic($product['id_product']),
                'link' => Context::getContext()->link->getProductLink(
                    $product['id_product'], 
                    $product['link_rewrite']
                ),
                'id_image' => $product['id_image'],
                'image_url' => $product['id_image'] 
                    ? Context::getContext()->link->getImageLink(
                        $product['link_rewrite'], 
                        $product['id_image'], 
                        'home_default'
                    ) 
                    : null,
                'reference' => $product['reference']
            ];
        }
    
        return $prepared_products;
    }
    
    /**
     * Add frontend CSS for brand categories
     */
    public function hookActionFrontControllerSetMedia()
    {
        // Add CSS for brand category styling
        $this->context->controller->registerStylesheet(
            'brand-category-styles',
            'modules/'.$this->name.'/views/css/brand_categories.css',
            [
                'media' => 'all',
                'priority' => 150
            ]
        );
    }