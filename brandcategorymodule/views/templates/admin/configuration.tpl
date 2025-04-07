{* views/templates/admin/configuration.tpl *}
<div class="panel">
    <div class="panel-heading">
        {l s='Brand Category Manager' mod='brandcategorymodule'}
    </div>

    {* Create Brand Category Form *}
    <form action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" method="post">
        <div class="form-group">
            <label>{l s='Manufacturer' mod='brandcategorymodule'}</label>
            <select name="manufacturer_id" class="form-control">
                <option value="">{l s='Select Manufacturer' mod='brandcategorymodule'}</option>
                {foreach from=$manufacturers item=manufacturer}
                    <option value="{$manufacturer.id_manufacturer}">{$manufacturer.name}</option>
                {/foreach}
            </select>
        </div>

        <div class="form-group">
            <label>{l s='Category Name' mod='brandcategorymodule'}</label>
            <input type="text" name="category_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{l s='Category Description' mod='brandcategorymodule'}</label>
            <textarea name="category_description" class="form-control"></textarea>
        </div>

        <button type="submit" name="submitBrandCategory" class="btn btn-primary">
            {l s='Create Brand Category' mod='brandcategorymodule'}
        </button>
    </form>

    {* Existing Brand Categories *}
    <h3>{l s='Existing Brand Categories' mod='brandcategorymodule'}</h3>
    <table class="table">
        <thead>
            <tr>
                <th>{l s='Manufacturer' mod='brandcategorymodule'}</th>
                <th>{l s='Category Name' mod='brandcategorymodule'}</th>
                <th>{l s='Description' mod='brandcategorymodule'}</th>
                <th>{l s='Status' mod='brandcategorymodule'}</th>
                <th>{l s='Actions' mod='brandcategorymodule'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$brand_categories item=category}
                <tr>
                    <td>{$category.manufacturer_name}</td>
                    <td>{$category.name}</td>
                    <td>{$category.description}</td>
                    <td>
                        {if $category.active}
                            <span class="badge badge-success">{l s='Active' mod='brandcategorymodule'}</span>
                        {else}
                            <span class="badge badge-danger">{l s='Inactive' mod='brandcategorymodule'}</span>
                        {/if}
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-edit">{l s='Edit' mod='brandcategorymodule'}</a>
                        <a href="#" class="btn btn-sm btn-delete">{l s='Delete' mod='brandcategorymodule'}</a>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>