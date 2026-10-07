# Notifications — configuration for administrators

HumHub notifies users through the web (the bell in the top bar), e-mail, mobile push and the
channels modules add. Today every enabled channel receives a notification as soon as it is
written: one mail or one push per notification. The next update adds a delivery layer that
keeps the volume down on a busy network — the first mail in a quiet hour goes out at once,
further ones are held back a little and collected into one message, and nothing is sent for a
notification the user has already read on the web. This page covers what you can set and where.

## Requirements

- **The queue.** Notifications are written by a queued job. A worker has to be running — the
  cron entry `php yii queue/run` every minute, or the `queue/listen` daemon — see
  [asynchronous tasks](https://docs.humhub.org/docs/admin/asynchronous-tasks). Without a worker,
  no notification appears on the web and no mail or push leaves.
- **Cron.** The daily run deletes old notifications (see [Retention](#retention)).

## Defaults for all users

*Administration → Settings → Notifications* sets what new users start with, and what every
user gets after *Reset for all users* (shown to administrators who may also manage users):

| Setting | Values | Default |
|---|---|---|
| E-Mail: mode | *Send*, *Off* | Send |
| Mobile: mode (only with a push module installed) | *Send*, *Off* | Send |
| Groups per channel | on/off for *Reactions on my content*, *New content in my spaces*, *Administration*, and one switch per module that has a group of its own (Messenger, Tasks, …) | on |
| Receive 'New Content' Notifications for the following spaces | the spaces whose new content is announced to members who have not chosen themselves | none |

*Directly addressed to you* — mentions, invitations, requests, messages, and content or comments
an administrator deleted — is not switchable: a user who wants these to stop turns the channel
off. *Administration* is only shown to users who may manage settings, users or spaces. The web
list has no settings; it is always on.

A third e-mail mode, *Summary only* — the unread notifications in a block at the top of the
activity summary mail instead of separate mails — comes with a later update, together with that
block.

The page also sets the default interval of the activity summary mail while summary mails are
enabled. A user's own interval is stored only when it differs from this default, so users who
never chose one follow later changes of it.

Users change the same settings for themselves under *Account settings → Notifications*, and
*Reset to defaults* there returns them to yours.

Settings from earlier versions are converted on update: a channel whose every category was off
becomes *Off*, a group whose every category was off is switched off, everything else follows the
new defaults. Notification categories of other modules are not converted.

## Timings

The delays below are reserved for the delivery layer of the next update; setting them now has
no effect yet. They are not in the user interface but properties of each channel, set in
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
                    // Do not mail a user who is online right now. Default: true for e-mail
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

How the numbers will act:

- A notification with **high** priority (direct ones) ignores `delays` and goes at once.
- A **normal** one is scheduled by `delays`: with the defaults, the first mail of a quiet hour
  immediately, the next five minutes later, then fifteen, then thirty for every further one.
- A **low** one (likes, follows) waits at least `lowPriorityDelay`, but goes along with any
  mail that leaves earlier.
- A notification that arrives while a mail is already waiting joins that mail.
- Before a mail is sent, HumHub checks again whether the notification has been seen in the
  meantime, whether the user still has the channel on, and (`skipWhenOnline`) whether the user
  is online. If so, the mail is not sent.

Two common setups:

- **Everything at once**, as before 1.20: `'delays' => [0]` and `'skipWhenOnline' => false` on
  `email`.
- **Very quiet**: `'delays' => [0, 1800]` and `'delayWindow' => 86400` — the first mail of the
  day at once, everything else collected every thirty minutes.

A channel can be switched off for the whole installation with `'active' => false`.

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
- **Web notifications arrive, mails do not:** check the user's mode and group switches, the
  mailer settings, and the application log (*Administration → Information → Logging*) for
  entries of the category `notification` — a failing channel is logged as
  `Notification #… delivery through email failed`, a mail the mailer refused as
  `Notification mail to … was not sent`.
- **No push:** the *Mobile* channel only exists while a push module (e.g. `fcm-push`) is
  installed and set up.
