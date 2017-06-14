# Rigby Star Reviews
## Rating System with Administration Tools

Rigby is a standalone CMS-agnostic star rating system. It's easy to install and requires very little coding knowledge to implement. It includes a WordPress like
console that lets administrators view compiled review data, reply to and edit reviews and add/edit product information. Customers and visitors can interact with 
the Rigby system by leaving reviews, viewing review information for specific ratings and products and find your review ratings during a web search using structured
data.

## Requirements:
- PHP 5.3 or higher
- Access to a MySQL database with version 5.5.51 or higher
- PHP sessions must be enabled.

## Credits:
Rigby uses code from the following libraries:
- random_compat: [https://github.com/paragonie/random_compat](https://github.com/paragonie/random_compat) - Used for building user tokens for admin login.
- Faker: [https://github.com/fzaninotto/Faker](https://github.com/fzaninotto/Faker) - Used to generate fake names/email addresses and lorem ipsum text content for fake reviews.
- PHPMailer: [https://github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer) - Used to send password reset emails for users that have forgotten their passwords. 
- Rigby uses a PDO wrapper for MySQL queries. That wrapper comes from an article found at [PHP Delusions](https://phpdelusions.net/pdo/pdo_wrapper), which is a wonderful blog.
- Rigby is named after the British actor [Terrence Rigby](https://en.wikipedia.org/wiki/Terence_Rigby).

## Installation:
1. Download the Rigby source files or clone the Git repository from https://github.com/GabrielMioni/rigby-star-reviews.git.
2. Place the Rigby folder on your home directory.
3. In your browser, visit www.your-web-site.com/rigby/installer/
4. The Rigby Installer will walk you through some steps. Rigby will do the following:
 - Check your PHP version and confirm PHP sessions are enabled
 - Request MySQL credentials (Database, Username and Password). The MySQL user credentials must include write and delete access.
 - Try to create MySQL tables it requires. If it fails, it will provide you with feedback.
 - Ask you to create your first Rigby admin account.
5. After finising installation you'll be sent to the Rigby admin login page. Use the Rigby Username and Password you've created.
7. Delete the installer directory at www.your-web-site.com/rigby/installer/ directory. You don't need it anymore and it will be a vulnerability risk.
8. Congratulations! You are ready to use Rigby.

## Admin Features:
Rigby Administration Console has five sub-modules:
1. Dashboard: Provides a quick overview of recent reviews, trends over the last week.
2. Reviews: Displays searchable reviews. Reviews can be editted and replied to. Replys will be displayed to customers/visitors.
3. Products: Product full names and IDs can be entered in Rigby. Product data is used to display specific reviews and also specify which
product/service is being reviewed.
4. Settings: You can create fake reviews here. More to come.

## Rigby Widgets:
Rigby widgets provide ways for customers to interact with Rigby and view review data.
1. Submit Module: A small form that accepts new reviews.
2. Sidebar: Displays formatted reviews.
3. Histogram: An easy to understand graph that provides a total review count and the percentage each star rating takes up from
that total.
4. Aggregate Rating: Builds HTML that includes structured data with schema.org markup. This will give information to
search engines about your product and review rating on pages where the Aggregate Rating widget is included.

All widgets can either be set for a specific product, or for all products saved in the Rigby system.
