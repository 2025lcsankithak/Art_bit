# ArtBid project structure

ArtBid is a PHP and MySQL auction application intended for XAMPP.

## Folders and files

- `index.php` — entry point. Loads shared code and chooses which page view to render.
- `db/database.sql` — the project's only SQL file; contains the schema, profile-field upgrade, and demo data.
- `includes/database.php` — opens the PDO connection to MySQL.
- `includes/helpers.php` — reusable formatting and HTML escaping helpers.
- `includes/actions.php` — handles registration, login, logout, bids, wishlist updates, and listing creation.
- `includes/data.php` — reads auction, seller, and dashboard data for the current request.
- `views/header.php` — document head, site navigation, notices, and main content opening tag.
- `views/discover.php` — artwork search, filters, and auction cards.
- `views/artwork-detail.php` — artwork details and bidding controls.
- `views/seller-studio.php` — seller dashboard and artwork list.
- `views/buyer-dashboard.php` — buyer dashboard summary.
- `views/modals.php` — sign-in, registration, and artwork listing dialogs.
- `views/footer.php` — site footer and shared dialog/JavaScript includes.
- `assets/css/style.css` — responsive application styling.
- `assets/js/app.js` — bid suggestion, registration form toggle, and countdown behavior.

## Run with XAMPP

1. Start Apache and MySQL in XAMPP.
2. In phpMyAdmin, import `db/database.sql` once. It creates the tables, adds missing seller profile fields on older databases, and inserts demo data.
3. Sign in with a demo account below or create an account from the sign-in dialog.
4. Open `http://localhost/art_bid/`.
5. The default MySQL connection is `localhost`, database `artbid`, user `root`, blank password. Adjust `includes/database.php` if your local setup differs.

Demo accounts all use the password `password123`: sellers `ananya.seller@artbid.test` and `kabir.seller@artbid.test`; buyers `mira.buyer@artbid.test` and `arjun.buyer@artbid.test`. Sellers can list artwork from Seller Studio. Buyers can search, save, and bid. A bid must exceed the current bid by at least ₹100.
