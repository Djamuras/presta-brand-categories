{* views/templates/admin/product_brand_categories.tpl *}
<div class="form-group brand-categories-container">
    <h4>{l s='Brand Categories' mod='brandcategorymodule'}</h4>
    
    {if $grouped_categories}
        {foreach from=$grouped_categories key=manufacturer_name item=categories}
            <div class="brand-manufacturer-section">
                <h5>{$manufacturer_name}</h5>
                <div class="row">
                    {foreach from=$categories item=brand_category}
                        <div class="col-md-4 col-sm-6">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" 
                                           name="brand_categories[]" 
                                           value="{$brand_category.id_brand_category}"
                                           {foreach from=$current_brand_categories item=current_category}
                                               {if $current_category.id_brand_category == $brand_category.id_brand_category}checked{/if}
                                           {/foreach}
                                    >
                                    {$brand_category.name}
                                    {if $brand_category.description}
                                        <small class="text-muted">({$brand_category.description})</small>
                                    {/if}
                                </label>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/foreach}
    {else}
        <div class="alert alert-info">
            {l s='No brand categories found. Create categories in the module configuration.' mod='brandcategorymodule'}
            <a href="{$link->getAdminLink('AdminModules')}&configure=brandcategorymodule" class="btn btn-primary btn-sm ml-2">
                {l s='Create Brand Categories' mod='brandcategorymodule'}
            </a>
        </div>
    {/if}

    <div class="help-block">
        {l s='Select brand-specific categories for this product. Categories are filtered by the product\'s manufacturer.' mod='brandcategorymodule'}
    </div>
</div>

<style>
.brand-categories-container .checkbox {
    margin-bottom: 10px;
}
.brand-categories-container .brand-manufacturer-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}
.brand-categories-container .brand-manufacturer-section:last-child {
    border-bottom: none;
}
</style>