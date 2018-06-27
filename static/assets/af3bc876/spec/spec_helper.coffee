$ = jQuery

@KEY_CODE =
  DOWN: 40
  UP: 38
  ESC: 27
  TAB: 9
  ENTER: 13
  CTRL: 17
  P: 80
  N: 78

@fixtures or= loadJSONFixtures("data.json")["data.json"]

@triggerAtwhoAt = ($inputor) ->
  simulateTypingIn $inputor
  simulateChoose $inputor

@simulateTypingIn = ($inputor, flag, pos=31) ->
  $inputor.data("atwho").setContextFor flag || "@"
  oDocument = $inputor[0].ownerDocument
  oWindow = oDocument.defaultView || oDocument.parentWindow
  if $inputor.attr('contentEditable') == 'true' && oWindow.getSelection
    $inputor.focus()
    sel = oWindow.getSelection()
    range = oDocument.createRange()
    range.setStart $inputor.contents().get(0), pos
    range.setEnd $inputor.contents().get(0), pos
    range.collapse false
    sel.removeAllRanges()
    sel.addRange(range)
  else
    $inputor.caret('pos', pos)
  $inputor.trigger("keyup")

@simulateChoose = ($inputor) ->
  e = $.Event("keydown", keyCode: KEY_CODE.ENTER)
  $inputor.trigger(e)

@getAppOf = ($inputor, at = "@") ->
  $inputor.data('atwho').setContextFor(at)
