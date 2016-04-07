=== Reply By Email ===
Contributors: replypush
Tags: comments, reply, email, replies, discussion, subscribe
Requires at least: 4.4.2
Tested up to: 4.4.2
Stable Tag: 0.2.2
Header tagline: Comment and Post Reply via Email
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows your users to reply to articles / blog posts and comments via email.

== Description ==

Much like Wordpress.com's "Comment Reply via Email", you can reply to any post you subscribe to.  

Integrates with the wonderful [subscribe2](https://wordpress.org/plugins/subscribe2/ "subscribe2") and [replyPUSH.com](https://replypush.com "replyPUSH.com") service to provide the email reply magic.

It automatically adds notifications for comment posts to keep the conversation flowing :)

All the subscriber need do, is reply at the top of their quoted email notification.

== Installation ==
1. Sign up at [replyPUSH](beta.replypush.com/signup "replyPUSH").
1. You need to install [subscribe2](https://wordpress.org/plugins/subscribe2/ "subscribe2") plugin if not already using it, as it won't work without it.
1. Follow the configuration guidance for subscribe2, add your subscribers. Depending on what you want to do you may wish to auto-subscribe new users. 
1. We recommend if you are allowing public subscribers, to check "Comment author must have a previously approved comment" in Settings->Discussion. An anonymous user is created as a stand in for public replies. 
1. Install Reply By Email via Plugins->Add New, then activate.
1. In a new tab you can get your replyPUSH credentials [here](http://beta.replypush.com/profile "replyPUSH credentials").
1. Back in Wordpress click on "Reply By Email" in the sidebar menu.
1. Carefully copy over from replyPUSH the Account No, Secret ID and Secret Key to the field provided. Save the form.
1. Copy the Notify URL to replyPUSH profile, and save the form. If it won't let you save it is usually because it is not a publicly accessible or resolvable URL, which is required for it to work.

== Frequently Asked Questions ==

= How it works? =

This plugin doesn't "phone home" like ET. Instead it adds special security headers, and a hidden marker to the email, which get carried over they reply. The reply address delvers to replyPUSH. After verification it gets sent to your site's Notify URL (hence the "PUSH" part). After a second round of verification an security, the comment is posted, or queued for approval.  

= Why are the templates / emails different looking? =

The plugin overrides subscribe2 templates for post notifications. This is to enable it to work, and also provides a helpful footer to explain to the subscriber what to do. Emails will be sent in html, however "plain" will be plainer. 

= Can the templates be modified? =

Yes you can. We recommend you use the default, however if you want to change things click on the substitution reference popup link for guidance. Make sure to provide enough information. Certain substitutions like `{TYPE_LINK_WORDS}`, `{POST}`, `{TITLE}` are pretty essential for subscribers to be able to understand the notification. 

= What's the deal with the anonymous user? =

The plugin creates an anonymous user, in order to handle replies from public users (non-members). The plugin will attempt to display a name if given with the email, otherwise just 'anon' will be used. When this user in created a random password is selected, so nobody can login with it. You could change the display name if you like. 

= How to get help? =

If you are having issues with the plugin try the Reply By Email wordpress [support forum}(https://wordpress.org/support/plugin/reply-by-email "wordpress support forum")

If you are having issues with the service or replies you can also use the [replyPUSH help forum](http://beta.replypush.com/help/ "replyPUSH help forum").

We are eager to help, just ask

Emails don't arrive instantly so bear in mind there may be some latency, beyond your or replyPUSH control.

As we are starting out, direct troubleshooting opportunities will help up iron out any potential issues, and improve the plugin and service. 

== Changelog ==

= 0.2.2 =
* Initial release
