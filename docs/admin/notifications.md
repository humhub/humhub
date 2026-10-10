# Notifications — configuration for administrators

HumHub notifies users through the web (the bell in the top bar), e-mail, mobile push and the
channels modules add. Each user decides per category which of these channels reach them. The web
list shows a notification as soon as it is written. Mail and push
go through a delivery layer that keeps the volume down on a busy network: the first mail in a
quiet hour goes out at once, further ones are held back a little and collected into one message,
and nothing is sent for a notification the user has already seen on the web. This page covers
what you can set and where.

## Requirements

- **The queue.** Notifications are written by a queued job. A worker has to be running — the
  cron entry `php yii queue/run` every minute, or the `queue/listen` daemon — see
  [asynchronous tasks](https://docs.humhub.org/docs/admin/asynchronous-tasks). Without a worker,
  no notification appears on the web and no mail or push leaves. The delays below need a queue
  driver that runs a job later than it was pushed, as the MySQL and Redis drivers do (see
  [Instant delivery](#instant-delivery)).
- **Cron.** The hourly run re-queues mails and pushes whose job got lost and cleans up the
  delivery records (see [Diagnosing](#diagnosing)); the daily run deletes old notifications (see
  [Retention](#retention)).
- **A cache shared by the web server and the queue worker.** The worker decides whether a user is
  online from the cache the web requests write. With a cache local to a process (APCu, the array
  cache) or a worker on another host without a shared cache (e.g. Redis), the worker never sees
  anybody online and sends mails also to users who are on the site.

## Defaults for all users

*Administration → Settings → Notifications* sets what users get who have not chosen themselves,
and what every user gets after *Reset all users* (shown to administrators who may also manage
users). The page lists the notification categories; each one expands to a checkbox per channel —
*Web*, *E-Mail* and, with a push module installed, *Mobile*. Every change is saved at once.

| Category | Default |
|---|---|
| *Directly addressed to you* | always on, every channel |
| *Reactions on my content* (comments, likes) | on, every channel |
| *New followers* | on for the web, off for e-mail and push |
| *New content in my Spaces*, with the spaces (*From these Spaces*) whose new content is announced to members who have not chosen themselves | on, every channel; no spaces |
| *Administration* | on, every channel |
| one category per module (Messenger, Tasks, …), or several when a module defines them, under *From modules* | on, every channel the module's notifications use |

*Directly addressed to you* — mentions, invitations, requests, changes to your space roles and
group memberships, and content or comments an administrator deleted — cannot be switched off:
it always reaches the user on every channel.
*Administration* is only shown to users who may manage settings, users or spaces. A category whose
notifications have a list of their own (the messenger's inbox) has no web switch. A notification
switched off for the web but on for e-mail is still mailed, without appearing in the web list.

Users change the same switches for themselves under *Account settings → Notifications*. There a
profile sets all of them at once: *Everything* (every category on every channel), *Recommended*
(your defaults: the user's own switches are removed, so later changes of the defaults reach
them; unlike *Reset to defaults* it keeps their choice of spaces), *Important only* (the web list complete,
e-mail and push only for what is addressed to the user directly) and *Custom*, selected by
itself as soon as the switches match none of the others.

### Updating from 1.19

Settings from earlier versions are converted on update, for the web list, e-mail and push alike:
the nine core categories of 1.19 are merged into the five above. A category whose every former
category was off is switched off, a category with any former category on stays on, and a
category none of whose former categories was ever stored follows the new defaults (so *New
followers* by e-mail and push is off unless a user had switched it on).

A module's settings are carried over when its former category had the module's id, which is
true for many modules (e.g. Calendar, News, Messenger). The settings of other module categories are
dropped and their default applies again. If users had switched module notifications off, you may
want to ask them to check their notification settings after the update.

## Timings

The delays are not in the user interface but properties of each channel, set in
`config/common.php` under the channel's id (`email`, `mobile`, or the id a module registers):

```php
return [
    'components' => [
        'notification' => [
            'targets' => [
                'email' => [
                    // Seconds to wait for the 1st, 2nd, 3rd and every further mail
                    // a user receives within the window. Default: [0, 300, 900, 1800]
                    'delays' => [0, 600, 1800, 3600],
                    // The window the mails are counted in. Default: 3600
                    'delayWindow' => 7200,
                    // Minimum wait for low-priority notifications (likes, follows). Default: 1800
                    'lowPriorityDelay' => 3600,
                    // Do not mail a user who is on the site right now. Default: true for e-mail
                    'skipWhenOnline' => false,
                ],
                'mobile' => [
                    // Push at once, always
                    'delays' => [0],
                    // Default: false for push
                    'skipWhenOnline' => false,
                ],
            ],
        ],
    ],
];
```

A scalar value can also be set as an environment variable:
`HUMHUB_CONFIG__COMPONENTS__NOTIFICATION__TARGETS__EMAIL__SKIP_WHEN_ONLINE=false`.

How the numbers act, per user and channel:

| Setting | Effect |
|---|---|
| `delays` | The wait of a new notification depends on how many *messages* (mails, pushes) the user received on the channel within `delayWindow`: none → the first value, one → the second, and so on; the last value applies to every further one. A mail with five notifications counts once. |
| `delayWindow` | How far back the messages are counted. After a quiet window the user is back at the first value. |
| `lowPriorityDelay` | A **low**-priority notification (likes, follows) waits at least this long — and usually leaves earlier, with the next message. |
| `skipWhenOnline` | A mail that is due while the user was active on the site within the last minute is not sent: the user sees the notifications on the web. Notifications not yet due stay waiting. |

A notification with **high** priority (direct ones: mentions, invitations, requests)
ignores `delays` and goes at once.

When a message is due, it takes every waiting notification of the user and channel along, also
those due later. A notification that arrives while a message is waiting (due at least five
seconds later) joins it. Several notifications of one group (e.g. five likes on a post) appear
once ("Anna, Bob and 3 more like your post").

Right before sending, HumHub drops a notification that was seen in the meantime, whose category
the user has switched off for the channel since, or whose content is gone. If nothing is left, nothing is
sent.

An example with the defaults on `email`, for a user who is not on the site:

| Time | Event | Result |
|---|---|---|
| 10:00 | a comment | mailed at once — no mail in the last hour |
| 10:02 | a comment | waits five minutes (one mail in the hour): due 10:07 |
| 10:04 | a like (low priority) | would wait thirty minutes, joins the mail due 10:07 |
| 10:07 | | one mail with both notifications |
| 10:20 | a comment | waits fifteen minutes (two mails in the hour): due 10:35 |
| 10:25 | the user opens the comment on the web and leaves | the mail due 10:35 is dropped |
| 10:30 | a mention (high priority) | mailed at once |

Had the user been on the site at 10:07, the mail would have been dropped as well.

Two common setups:

- **Everything at once**, as before 1.20: `'delays' => [0]` and `'skipWhenOnline' => false` on
  `email`.
- **Very quiet**: `'delays' => [0, 1800]` and `'delayWindow' => 86400` — the first mail of the
  day at once, after that at most one mail every thirty minutes collecting everything new
  (direct notifications still go at once).

A channel can be switched off for the whole installation with `'active' => false`.

### Instant delivery

The delays need a queue that runs a job later than it was pushed, as the MySQL, Redis, file,
Beanstalk and AMQP drivers do. With a queue that runs jobs at once (`Instant`, `Sync`) or a
driver HumHub does not know, every delay counts as zero: every notification goes out at once,
one message each. The detection can be overridden on the component — `false` for a custom
driver that honours delays, `true` to deliver at once with any queue:

```php
'notification' => [
    // null (default): detect from the queue driver
    'instantDelivery' => false,
],
```

Times are stored as local wall-clock times; across a daylight-saving change a delay can be an hour
shorter or longer.

## Summary mails

The activity summary mail mentions how many unread notifications a user has, with a link to the
notification overview. It does not list them, and no summary is sent for notifications alone.

## Retention

Seen notifications are deleted after two months, unseen ones after three, by the daily cron
run. Both are properties of the notification module:

```php
'modules' => [
    'notification' => [
        'deleteSeenNotificationsMonths' => 1,
        'deleteUnseenNotificationsMonths' => 2,
    ],
],
```

## Diagnosing

- **Nothing arrives, not even on the web:** the queue worker is not running; the dispatch jobs
  wait in the queue.
- **Web notifications arrive, mails do not:** check the user's switches for the category, the
  mailer settings, the delivery records below, and the application log (*Administration →
  Information → Logging*) for entries of the category `notification` — a failing channel is
  logged as `Notification #… for user …: delivery through email failed`, a mail the mailer
  refused as `Notification mail to … was not sent`.
- **Mails arrive although the user is on the site:** the queue worker does not share the cache
  with the web server (see [Requirements](#requirements)).
- **Mails arrive late, not at all, or all at once:** check the queue driver
  ([Instant delivery](#instant-delivery)) and that the worker and the hourly cron run.
- **No push:** the *Mobile* channel only exists while a push module (e.g. `fcm-push`) is
  installed and set up.

Every scheduled mail or push is a row in the table `notification_delivery` — one per
notification and channel (`channel` is `email`, `mobile` or a module's id), with `due_at` and,
once sent, `sent_at`. Its `state`:

| `state` | Meaning |
|---|---|
| 0 pending | waiting for `due_at`, or for the next message to take it along |
| 1 sent | went out; the rows of one message share `sent_at` |
| 2 skipped | not sent: seen meanwhile, category switched off for the channel, user online, user disabled, or the notification no longer loads |
| 3 failed | the channel failed; the error is in the log, category `notification`; no retry |

Pending rows overdue by more than five minutes are re-queued by the hourly cron. Finished rows
are deleted after 30 days. For example, the last deliveries of one user:

```sql
SELECT notification_id, channel, state, due_at, sent_at
FROM notification_delivery WHERE user_id = 42 ORDER BY id DESC LIMIT 20;
```
