# WooCommerce CSV Importer

**Version:** 1.0  
**Author:** Your Name  
**License:** GPL2  
**GitHub Repository:** [https://github.com/yourusername/wc-csv-importer](https://github.com/yourusername/wc-csv-importer)

## Description

WooCommerce CSV Importer is a WordPress plugin that allows you to import products into your WooCommerce store using a CSV file. This tool simplifies the process of adding multiple products at once, saving you time and effort.

## Features

- **Admin Interface:** Easy-to-use form in the WordPress admin dashboard for uploading CSV files.
- **CSV Processing:** Parses CSV files and adds products to WooCommerce automatically.
- **Error Handling:** Displays success and error messages based on the import process.
- **Category and Tag Management:** Automatically creates categories and tags if they don't exist.

## Installation

1. **Download the Plugin:**
   - Clone the repository using Git:
     ```bash
     git clone https://github.com/yourusername/wc-csv-importer.git
     ```
   - Or download the ZIP file from GitHub and extract it.

2. **Upload to WordPress:**
   - Upload the `wc-csv-importer` folder to the `/wp-content/plugins/` directory of your WordPress installation.

3. **Activate the Plugin:**
   - Log in to your WordPress admin dashboard.
   - Navigate to **Plugins > Installed Plugins**.
   - Locate **WooCommerce CSV Importer** and click **Activate**.

## Usage

1. **Navigate to the Importer:**
   - In the WordPress admin dashboard, go to **WooCommerce > CSV Importer**.

2. **Prepare Your CSV File:**
   - Ensure your CSV file has the following headers:
     - `name` (required): Product name.
     - `description`: Product description.
     - `short_description`: Product short description.
     - `price`: Regular price.
     - `sale_price`: Sale price.
     - `sku`: Stock Keeping Unit.
     - `stock`: Stock quantity.
     - `categories`: Comma-separated categories.
     - `tags`: Comma-separated tags.
     - `type`: Product type (e.g., `simple`, `variable`).
     - `status`: Product status (`publish`, `draft`, `private`).

   - **Example CSV Content:**
     ```csv
     name,description,short_description,price,sale_price,sku,stock,categories,tags,type,status
     Sample Product,This is a detailed description.,Short description here,29.99,19.99,SP001,100,Category1,Tag1,simple,publish
     Another Product,Another description.,Another short description,49.99,,AP002,50,Category2,Tag2,simple,publish
     ```

3. **Import Products:**
   - Click on **Choose File** and select your CSV file.
   - Click **Import Products**.
   - The plugin will process the file and display success or error messages.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Commit your changes with clear messages.
4. Submit a pull request detailing your changes.

## License

This plugin is licensed under the [GPL2 License](LICENSE).

## Support

If you encounter any issues or have questions, please open an issue in the [GitHub repository](https://github.com/yourusername/wc-csv-importer/issues).

