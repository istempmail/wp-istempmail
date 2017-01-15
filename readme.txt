=== IsTempMail ===
Contributors: istempmail
Tags: istempmail, disposable email, temporary email, fake email, mailinator
Requires at least: 2.9
Tested up to: 4.7.1
Stable tag: 1.0
License: GPLv2 or later

This plugin will detect and block disposable, temporary, fake email addresses every time an email is submitted.

== Description ==
When installed and activated, the plugin will verify every email address submitted by user. It'll automatically detect
and block disposable, temporary email. It'll give a nice warning message when users are signing up, logging in,
or changing email to a fake email.

It will not use local blacklist, but will check domain name against a service named
[IsTempMail](https://www.istempmail.com/).

The plugin will work right after installed and activated.

No need to register, pay or subscribe. It will use the public API provided by IsTempMail by default. The only limit is
10 email checks per minute, that is sufficient for personal blog.
For bigger website flooded with fake emails, premium plans are available.

== Installation ==
Upload the IsTempMail plugin to your blog, then click Activate it, and it'll work immediately.

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

= Do you send my users email to other servers? =
No, we don't. The plugin will only send the domain part of email address to validate.

= What happens if the service is down? =
The submitted email is valid by default. So even when the service is temporarily down, users can register, log in
and comment on your blog just fine.

Any existing users registered with fake email address will unable to log in to your blog when the service is up and working.
This is a little punishment for using temporary email address. They won't mind, because they'll never remember
which disposable email address was used to flood your website database.

== Changelog ==
= 1.0.0 =
First version