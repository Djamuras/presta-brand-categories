# PrestaShop Brand Category Module

## Overview
This PrestaShop module allows you to create and manage brand-specific product categories, providing enhanced organization and navigation for your online store.

## Features
- Create custom categories for specific brands
- Assign products to brand-specific categories
- Display brand categories on manufacturer pages
- Detailed category views with product listings
- Easy-to-use admin interface

## Installation
1. Download the module files
2. Upload the entire `brandcategorymodule` folder to the `modules/` directory of your PrestaShop installation
3. Go to Back Office > Modules > Module Manager
4. Find "Brand Category Manager" and click "Install"

## Usage

### Creating Brand Categories
1. Navigate to Modules > Brand Category Manager
2. Select a manufacturer from the dropdown
3. Enter a category name (e.g., "Portwest Shoes", "Portwest Gloves")
4. Add an optional description
5. Click "Create Brand Category"

### Assigning Products to Brand Categories
1. Edit a product in the Back Office
2. Find the "Brand Categories" section
3. Check the appropriate brand categories for the product
4. Save the product

### Frontend Display
- Brand pages now show categories with product previews
- Each category displays up to 6 products
- "View All Products" link for categories with more products
- Detailed category pages show full product listings

## Requirements
- PrestaShop 8.1.2
- PHP 7.4+
- MySQL 5.7+

## Customization
- Modify `views/css/brand_categories.css` to change styling
- Edit templates in `views/templates/front/` for custom designs

## Troubleshooting
- Ensure module is correctly installed
- Clear PrestaShop cache after installation
- Check database permissions
- Verify PrestaShop version compatibility

## Changelog
- 1.0.1: Added frontend category display and detailed views
- 1.0.0: Initial release

## Support
For issues or feature requests, contact [Your Support Email]

## License
MIT License

## Future Improvements
- Advanced filtering options
- More detailed product comparison
- Enhanced reporting features