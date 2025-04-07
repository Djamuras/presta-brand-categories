{* views/templates/front/brand_category_detail.tpl *}
{extends file='page.tpl'}

{block name='page_header_container'}
<div class="breadcrumb-container">
    <nav data-depth="3" class="breadcrumb">
        <ol>
            <li>
                <a href="{$link->getPageLink('index', true)}">
                    <span>{l s='Home' mod='brandcategorymodule'}</span>
                </a>
            </li>
            <li>
                <a href="{$link->getManufacturerLink($manufacturer->id, $manufacturer->link_rewrite)}">
                    <span>{$manufacturer->name}</span>
                </a>
            </li>
            <li>
                <span>{$brand_category.name}</span>
            </li>
        </ol>
    </nav>
</div>
{/block}

{block name='page_content'}
<section class="brand-category-detail container">
    <div class="row">
        <div class="col-12">
            <header class="page-header">
                <h1 class="h1">{$brand_category.name} - {$manufacturer->name}</h1>
                
                {if $brand_category.description}
                    <div class="brand-category-description mt-3">
                        <p class="text-muted">{$brand_category.description}</p>
                    </div>
                {/if}
            </header>
        </div>
    </div>

    {* Product Listing *}
    <div class="products row">
        {foreach $products as $product}
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 product-item">
                <article class="product-miniature js-product-miniature">
                    <div class="thumbnail-container">
                        <div class="product-image">
                            {if $product.image_url}
                                <a href="{$product.link}" class="thumbnail product-thumbnail">
                                    <img src="{$product.image_url}" 
                                         alt="{$product.name}" 
                                         class="img-fluid">
                                </a>
                            {/if}
                        </div>
                        
                        <div class="product-description">
                            <h3 class="h3 product-title">
                                <a href="{$product.link}">{$product.name}</a>
                            </h3>
                            
                            {if $product.description_short}
                                <div class="product-short-description">
                                    {$product.description_short nofilter}
                                </div>
                            {/if}
                            
                            <div class="product-price-and-shipping">
                                <span class="price">{$product.price|number_format:2} {$currency.sign}</span>
                            </div>
                            
                            <div class="product-actions">
                                <form action="{$link->getPageLink('cart')}" method="post">
                                    <input type="hidden" name="token" value="{$token}">
                                    <input type="hidden" name="id_product" value="{$product.id}">
                                    <input type="hidden" name="qty" value="1">
                                    <button 
                                        class="btn btn-primary add-to-cart" 
                                        data-button-action="add-to-cart" 
                                        type="submit"
                                    >
                                        <i class="material-icons shopping-cart">&#xE547;</i>
                                        {l s='Add to cart' mod='brandcategorymodule'}
                                    </button>
                                </form>
                                
                                <a 
                                    href="{$product.link}" 
                                    class="btn btn-secondary quick-view" 
                                    data-link-action="quickview"
                                >
                                    {l s='Quick View' mod='brandcategorymodule'}
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        {/foreach}
    </div>

    {* No Products Message *}
    {if $products|count == 0}
        <div class="alert alert-info">
            {l s='No products found in this category.' mod='brandcategorymodule'}
        </div>
    {/if}
</section>
{/block}

{block name='page_footer'}
<div class="clearfix"></div>
{/block}