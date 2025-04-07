{* views/templates/front/brand_categories.tpl *}
{if isset($brand_categories) && $brand_categories|count > 0}
<div class="brand-categories-container">
    <h2>{l s='Product Categories by' mod='brandcategorymodule'} {$manufacturer->name}</h2>
    
    {foreach $brand_categories as $category}
        <div class="brand-category">
            <div class="category-header">
                <h3>{$category.name}</h3>
                {if $category.description}
                    <p class="category-description text-muted">{$category.description}</p>
                {/if}
            </div>
            
            {if isset($category.products) && $category.products|count > 0}
                <div class="category-products row">
                    {foreach $category.products as $product}
                        <div class="col-xs-12 col-sm-4 col-md-2 product-item">
                            <div class="product-miniature">
                                {if $product.image_url}
                                    <a href="{$product.link}">
                                        <img src="{$product.image_url}" 
                                             alt="{$product.name}" 
                                             class="img-fluid product-thumbnail">
                                    </a>
                                {/if}
                                <div class="product-details">
                                    <h4 class="product-title">
                                        <a href="{$product.link}">{$product.name}</a>
                                    </h4>
                                    {if $product.description_short}
                                        <p class="product-description">{$product.description_short nofilter}</p>
                                    {/if}
                                    <div class="product-price">
                                        {$product.price|number_format:2} {$currency.sign}
                                    </div>
                                    <div class="product-actions">
                                        <a href="{$product.link}" class="btn btn-primary">
                                            {l s='View Product' mod='brandcategorymodule'}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    
                    {if $category.product_count > 6}
                        <div class="col-12 text-center">
                            <a href="{$link->getModuleLink('brandcategorymodule', 'brandcategory', ['id_brand_category' => $category.id_brand_category])}" 
                               class="btn btn-secondary">
                                {l s='View All %d Products' sprintf=[$category.product_count] mod='brandcategorymodule'}
                            </a>
                        </div>
                    {/if}
                </div>
            {else}
                <div class="alert alert-info">
                    {l s='No products found in this category.' mod='brandcategorymodule'}
                </div>
            {/if}
        </div>
    {/foreach}
</div>
{else}
    <div class="alert alert-info">
        {l s='No brand categories found for this manufacturer.' mod='brandcategorymodule'}
    </div>
{/if}