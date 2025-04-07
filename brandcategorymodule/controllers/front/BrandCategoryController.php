<?php
/**
 * Frontend controller for Brand Category
 */
class BrandCategoryModuleBrandCategoryController extends ModuleFrontController
{
    public $brand_category;
    public $manufacturer;

    public function init()
    {
        parent::init();

        // Get brand category ID from URL
        $id_brand_category = (int)Tools::getValue('id_brand_category');

        // Validate brand category
        if (!$id_brand_category) {
            Tools::redirect('index.php?controller=404');
        }

        // Fetch brand category details
        $this->brand_category = Db::getInstance()->getRow('
            SELECT bc.*, m.name as manufacturer_name, m.id_manufacturer, m.link_rewrite as manufacturer_link_rewrite
            FROM '._DB_PREFIX_.'brand_category bc
            INNER JOIN '._DB_PREFIX_.'manufacturer m ON bc.id_manufacturer = m.id_manufacturer
            WHERE bc.id_brand_category = '.(int)$id_brand_category.'
            AND bc.active = 1
        ');

        // Check if brand category exists
        if (!$this->brand_category) {
            Tools::redirect('index.php?controller=404');
        }

        // Get manufacturer details
        $this->manufacturer = new Manufacturer(
            $this->brand_category['id_manufacturer'], 
            $this->context->language->id
        );
    }

    public function initContent()
    {
        parent::initContent();

        // Get language ID
        $id_lang = (int)$this->context->language->id;

        // Fetch products for this brand category
        $products = Db::getInstance()->executeS('
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
            WHERE bcp.id_brand_category = '.(int)$this->brand_category['id_brand_category'].'
            AND p.active = 1
        ');

        // Prepare products with full details
        $prepared_products = [];
        foreach ($products as $product) {
            $prepared_products[] = [
                'id' => $product['id_product'],
                'name' => $product['name'],
                'description_short' => $product['description_short'],
                'link_rewrite' => $product['link_rewrite'],
                'price' => Product::getPriceStatic($product['id_product']),
                'link' => $this->context->link->getProductLink(
                    $product['id_product'], 
                    $product['link_rewrite']
                ),
                'id_image' => $product['id_image'],
                'image_url' => $product['id_image'] 
                    ? $this->context->link->getImageLink(
                        $product['link_rewrite'], 
                        $product['id_image'], 
                        'home_default'
                    ) 
                    : null,
                'reference' => $product['reference']
            ];
        }

        // Assign data to template
        $this->context->smarty->assign([
            'brand_category' => $this->brand_category,
            'manufacturer' => $this->manufacturer,
            'products' => $prepared_products
        ]);

        // Set template
        $this->setTemplate('module:brandcategorymodule/views/templates/front/brand_category_detail.tpl');
    }

    /**
     * Add page-specific CSS and JS
     */
    public function setMedia()
    {
        parent::setMedia();

        // Add module-specific styles
        $this->addCSS(_MODULE_DIR_.'brandcategorymodule/views/css/brand_categories.css');
    }
}