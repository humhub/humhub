# Live Updates

The HumHub "Live" component updates the user interface without the need for user interaction.

These are currently e.g.

- Number of unread notifications
- Notification of new posts or comments in the stream
- Display of desktop notifications

By default, this is realized via a polling mechanism. A experimental [push driver](https://docs.humhub.org/docs/admin/push-updates/) via WebSockets is also available. 

For Mobile & Browser "Push Notifications" the [Firebase module](https://marketplace.humhub.com/module/fcm-push) must be installed. 
