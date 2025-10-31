# Easy Webhooks - Translation Files

This folder contains translation files for the Easy Webhooks plugin.

## Available Translations

- **Spanish (Spain)** - `easy-webhooks-wp-es_ES.po` - Complete

## How to Translate

### Using POEdit (Recommended)

1. Download and install [POEdit](https://poedit.net/)
2. Open the `easy-webhooks-wp.pot` file in POEdit
3. Go to **File > New from POT/PO file**
4. Select your language
5. Translate all strings
6. Save the file as `easy-webhooks-wp-{locale}.po` (e.g., `easy-webhooks-wp-fr_FR.po`)
7. POEdit will automatically generate the `.mo` file

### Using translate.wordpress.org (For WordPress.org Contributors)

Once the plugin is on WordPress.org, you can contribute translations directly at:
https://translate.wordpress.org/projects/wp-plugins/easy-webhooks-wp/

### Locale Codes

Common locale codes:
- English (US): `en_US`
- Spanish (Spain): `es_ES`
- French (France): `fr_FR`
- German (Germany): `de_DE`
- Italian (Italy): `it_IT`
- Portuguese (Brazil): `pt_BR`
- Dutch (Netherlands): `nl_NL`
- Russian (Russia): `ru_RU`
- Japanese (Japan): `ja`
- Chinese (Simplified): `zh_CN`
- Chinese (Traditional): `zh_TW`

Full list: https://make.wordpress.org/polyglots/teams/

## File Structure

- `easy-webhooks-wp.pot` - Template file (DO NOT EDIT - generated from source code)
- `easy-webhooks-wp-{locale}.po` - Editable translation file for each language
- `easy-webhooks-wp-{locale}.mo` - Compiled translation file (generated from .po file)

## Contributing Translations

We welcome translation contributions! 

### Option 1: Submit via GitHub
1. Fork the repository
2. Create your translation file using the `.pot` template
3. Submit a pull request with your `.po` and `.mo` files

### Option 2: Email
Send your `.po` and `.mo` files to: info@valandiangelidis.com

### Option 3: WordPress.org (After Plugin Approval)
Contribute directly through the WordPress.org translation system

## Testing Your Translation

1. Place your `.po` and `.mo` files in this `languages/` folder
2. Go to WordPress Admin > Settings > General
3. Change "Site Language" to your language
4. Visit the Easy Webhooks settings page to see your translations

## Translation Best Practices

- Keep button text short and clear
- Maintain the same tone (professional but friendly)
- Preserve placeholders like `%s` and `%d` in their correct positions
- Don't translate:
  - Plugin name ("Easy Webhooks")
  - Technical terms (HTTP, URL, webhook, JSON, API)
  - Code-related strings (featured_image_id, featured_image_url)

## Questions?

Contact: info@valandiangelidis.com
Website: https://valandiangelidis.com

---

Thank you for helping make Easy Webhooks accessible to users worldwide! 🌍
