# GutSparen Offers

WordPress plugin for managing and displaying GutSparen discount offers as banners and overview cards.

## What It Does

- Adds a custom post type for offers: `gso_offer`
- Adds an offer category taxonomy: `gso_offer_category`
- Lets editors manage offer details in wp-admin
- Provides shortcodes for:
  - a single banner
  - the best active banner automatically
  - an overview grid of active offers
- Adds admin helpers:
  - custom columns
  - shortcode copy button
  - shortcode help notice

## Plugin Structure

`gutsparen-offers/`

- `gutsparen-offers.php`: plugin bootstrap
- `includes/class-gso-cpt.php`: custom post type and taxonomy
- `includes/class-gso-meta-boxes.php`: admin offer fields
- `includes/class-gso-shortcodes.php`: frontend shortcodes
- `includes/class-gso-assets.php`: admin/public assets
- `includes/class-gso-admin-columns.php`: admin list table columns
- `includes/class-gso-admin-help.php`: shortcode help in wp-admin
- `public/css/gso-public.css`: frontend styles
- `public/js/gso-public.js`: frontend interactions
- `admin/css/gso-admin.css`: admin styles
- `admin/js/gso-admin.js`: admin copy button behavior

## Installation

1. Copy the `gutsparen-offers` folder into `wp-content/plugins/`
2. Activate `GutSparen Offers` in WordPress admin
3. Go to `GutSparen Offers` in the dashboard

## Managing Offers

Each offer supports:

- Offer title
- Company name
- Short description
- Discount code
- Target URL
- Expiry date
- Priority
- Premium flag
- Active flag
- Featured image
- Offer categories

Notes:

- Lower `Priority` value wins
- Only offers with `Active = Yes` are shown
- Expired offers are automatically hidden
- Category shortcode filters use the category slug, not the visible name

## Shortcodes

### Banner

Show a specific offer:

```text
[gutsparen_banner id="123"]
```

Show the best active, non-expired offer:

```text
[gutsparen_banner]
```

Show the best active, non-expired offer from one category:

```text
[gutsparen_banner category="technik"]
```

### Overview Grid

Show all active, non-expired offers:

```text
[gutsparen_offers_overview]
```

Show all active, non-expired offers from one category:

```text
[gutsparen_offers_overview category="technik"]
```

## Overview Filters

The overview shortcode includes:

- search field
- category dropdown
- filter button

Filtering uses URL parameters:

- `gso_search`
- `gso_category`

Existing page query parameters are preserved so the filter works on pages like:

```text
/?page_id=298
```

## Banner Behavior

Current banner behavior includes:

- background-image banner layout
- premium badge
- revealable discount code
- scratch-style reveal animation
- revealed code stays open during the current page view
- revealed code resets after page refresh

## Admin Features

In the offers list table, the plugin shows:

- ID
- Premium
- Active
- Expiry Date
- Priority
- Shortcode copy button

The plugin also shows a shortcode help box inside the offer admin screens.

## Asset Versioning

Frontend and admin CSS/JS use `filemtime(...)` for cache busting, so style and script changes should refresh automatically after file edits.

## Development Notes

- The plugin is currently built as a classic WordPress plugin without a build step
- Styling is handled directly in `public/css/gso-public.css`
- Frontend interactions are kept lightweight in `public/js/gso-public.js`

## Author

Anouar Ben Hamza
