# Live Updates

The `live` module pushes events to connected browsers so UI elements update without a page reload — unread-notification counters, new stream entries, desktop notifications.

By default it works via short polling against `/live/poll`. An experimental [WebSocket push driver](https://docs.humhub.org/docs/admin/push-updates/) is available as an opt-in.

For mobile and browser push notifications outside the active tab, install the [Firebase module](https://marketplace.humhub.com/module/fcm-push) — the live module itself only reaches open browser tabs.

## Sending a live event from a module

Place live-event classes under your module's `live/` directory. Each extends `humhub\modules\live\components\LiveEvent`:

```php
namespace johndoe\example\live;

use humhub\modules\live\components\LiveEvent;
use humhub\modules\content\models\Content;

class ExampleHappened extends LiveEvent
{
    public int $exampleId;
    public string $text;

    public function init()
    {
        parent::init();
        $this->visibility = Content::VISIBILITY_OWNER;   // who can receive this event
    }
}
```

Trigger it from your controller / service:

```php
use humhub\modules\live\components\Sender;

(new ExampleHappened([
    'exampleId' => $example->id,
    'text' => $example->title,
]))->send();
```

The frontend `humhub.modules.live` module dispatches the event to any registered JavaScript handler.

## Receiving on the frontend

Register a handler in your module's JavaScript:

```js
humhub.module('example', function (module, require, $) {
    var live = require('live');

    var init = function () {
        live.on('johndoe.example.live.ExampleHappened', function (event) {
            // event.data is the LiveEvent payload
        });
    };

    module.export({ init: init });
});
```

The event name on the frontend is the fully-qualified PHP class name with `\` replaced by `.`.

## Visibility

`LiveEvent::$visibility` decides who receives the event. Default is `Content::VISIBILITY_PUBLIC` (everyone connected); use `VISIBILITY_OWNER` for events that should only reach a specific user. For container-scoped events, set `$contentContainerId` so only users with access to that container get the event.
