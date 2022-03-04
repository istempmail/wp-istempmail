=== Block Temporary Email ===
Contributors: istempmail
Tags: istempmail, disposable email, temporary email, fake email, trashmail, mailinator
Requires at least: 2.9
Tested up to: 5.9
Stable tag: 1.4
License: GPLv2 or later

This plugin will detect and block disposable, temporary, fake email addresses
every time an email is submitted.

== Description ==
This plugin will verify every email address submitted by users.
It'll automatically detect and block disposable, temporary emails.
It'll give a nice warning message when users are signing up, logging in,
or changing email to a fake email.

It checks only the domain name against a service named
[IsTempMail](https://www.istempmail.com/).
If a domain name is blocked, it will be stored into a local blacklist.
You can also manage your own local whitelist and blacklist.

The plugin integrates with the `is_email()` function of WordPress.
It works seamlessly with WooCommerce, Contact Form 7,
Gravity Form, Jetpack/Grunion contact forms,
WordPress registration form and any form which uses the `is_email()` function.

== Installation ==
Sign up and get your API token at [IsTempMail](https://www.istempmail.com/sign-up).

Upload the plugin to your blog, then click Activate it,
and enter the API token at your WordPress Dashboard > Settings > IsTempMail.

== Screenshots ==
1. No disposable email address when signing up
2. No temporary email address when updating profile
3. Manage local blacklist and whitelist

== Frequently Asked Questions ==
= Do you send my user emails to other servers? =
No, we don't. The plugin will only send the domain part of email address to validate.

= Is the plugin free? =
Yes, the plugin is free to use. IsTempMail also offers free API token which you can use for one blog.

= What happens if the service is down? =
It will continue to validate emails using local blacklist and whitelist.
The submitted emails are valid by default.
So even when the service is temporarily down, users can register, log in
and comment on your blog just fine.

= What happens to existing users with fake email address =
Existing users registered with fake email address won't be able to log in to your blog when the service is up and working.
This is a little punishment for using temporary email addresses. They won't mind, because they'll never remember
which disposable email address was used to flood your website database.

== Changelog ==
= 1.4 =
- Update API request format.
- Separate IsTempMail blacklist and your own local blacklist.

= 1.3 =
In prior versions, it'll check all emails passed to `is_email()` function.
You can now change the Hook behavior to check only emails submitted via browsers (POST/GET values).

= 1.2 =
If a domain name has no MX record and doesn't resolve to any IP address, it will be marked as "unresolvable"
 and the email will be considered as invalid. It helps in preventing some typos like gmail.con or gmail.comm.

= 1.1 =
Add local blacklist and whitelist.

= 1.0 =
First version