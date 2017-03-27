=== Block Temporary Email ===
Contributors: istempmail
Tags: istempmail, disposable email, temporary email, fake email, trashmail, mailinator
Requires at least: 2.9
Tested up to: 4.7.3
Stable tag: 1.2
License: GPLv2 or later

This plugin will detect and block disposable, temporary, fake email addresses every time an email is submitted.

== Description ==
When installed and activated, the plugin will verify every email address submitted by users. It'll automatically detect
and block disposable, temporary email. It'll give a nice warning message when users are signing up, logging in,
or changing email to a fake email.

It checks domain name against a service named
[IsTempMail](https://www.istempmail.com/).
If the domain name is blocked, it'll store it into your local blacklist.
You can also update your own local whitelist and blacklist.

The plugin will work right after installed and activated.

No need to register, pay or subscribe. It uses the public API provided by IsTempMail by default. The only limit is
10 email checks per minute, that is sufficient for personal blogs.
For bigger websites flooded with fake emails, premium plans are available.

The plugin integrates with the `is_email()` function of WordPress. It works seamlessly with WooCommerce, Contact Form 7,
Gravity Form, Jetpack/Grunion contact forms, WordPress registration form and any form which uses the `is_email()` function.

== Installation ==
Upload the plugin to your blog, then click Activate it, and it'll work immediately.

If you have registered an account and get an API token at [IsTempMail](https://www.istempmail.com/sign-in),
you can enter it at your WordPress Dashboard > Settings > IsTempMail.

== Screenshots ==
1. No disposable email address when signing up
2. No temporary email address when updating profile
3. No need to register, pay or subscribe.

== Frequently Asked Questions ==
= Is the plugin free? =
Yes, the plugin is completely free. You do not need to register, pay or subscribe to IsTempMail. We'll use
its public API by default, which limits 10 emails check per minute.

= Do you send my user emails to other servers? =
No, we don't. The plugin will only send the domain part of email address to validate.

= What happens if the service is down? =
It will continue to validate emails using local blacklist and whitelist.
The submitted emails are valid by default. So even when the service is temporarily down, users can register, log in
and comment on your blog just fine.

= What happens to existing users with fake email address =
Existing users registered with fake email address won't be able to log in to your blog when the service is up and working.
This is a little punishment for using temporary email addresses. They won't mind, because they'll never remember
which disposable email address was used to flood your website database.

== Changelog ==
= 1.2 =
If a domain name has no MX record and doesn't resolve to any IP address, it will be marked as "unresolvable"
 and the email will be considered as invalid. It helps in preventing some typos like gmail.con or gmail.comm.

= 1.1 =
Add local blacklist and whitelist.

= 1.0 =
First version