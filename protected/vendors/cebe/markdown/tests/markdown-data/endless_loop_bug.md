Creating an Action <a name="creating-action"></a>
------------------

For the "Hello" task, you will create a `say` [action](structure-controllers.md#creating-actions) that reads
a `message` parameter from the request and displays that message back to the user. If the request
does not provide a `message` parameter, the action will display the default "Hello" message.

> Info: [Actions](structure-controllers.md#creating-actions) are the objects that end users can directly refer to for
  execution. Actions are grouped by [controllers](structure-controllers.md). The execution result of
  an action is the response that an end user will receive.

Actions must be declared in ...
