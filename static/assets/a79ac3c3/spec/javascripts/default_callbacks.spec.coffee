
$inputor = null
app = null

describe "default callbacks", ->
  $ = jQuery
  callbacks = null
  text = null

  beforeEach ->
    loadFixtures("inputors.html")
    $inputor = $("#inputor").atwho at: "@", data: fixtures["names"]
    app = getAppOf $inputor

  beforeEach ->
    text = $.trim $inputor.text()
    callbacks = $.fn.atwho.default.callbacks
    app = $inputor.data("atwho")

  afterEach ->
    $inputor.atwho 'destroy'

  it "refactor the data before save", ->
    items = callbacks.beforeSave.call(app.controller(), fixtures["names"])
    expect(items).toContain({"name":"Jacob"})
    expect(items).toContain({"name":"Isabella"})

  it "should match the key word following @", ->
    query = callbacks.matcher.call(app, "@", text)
    expect(query).toBe("Jobs")

  it "should not match a space following @ if acceptSpaceBar flag omitted", ->
    $inputor = $("#inputor").atwho at: "@", data: fixtures["names"]
    text = $.trim $inputor.text()
    query = callbacks.matcher.call(app, "@", text)
    expect(query).toBe("Jobs")

  it "should not match a space following @ if acceptSpaceBar flag false", ->
    $inputor = $("#inputor").atwho at: "@", data: fixtures["names"], acceptSpaceBar: false
    text = $.trim $inputor.text()
    query = callbacks.matcher.call(app, "@", text, false, false)
    expect(query).toBe("Jobs")

  it "should match a space following @ if acceptSpaceBar flag set to true", ->
    $inputor = $("#inputor4").atwho at: "@", data: fixtures["names"], acceptSpaceBar: true
    text = $.trim $inputor.text()
    query = callbacks.matcher.call(app, "@", text, false, true)
    expect(query).toBe("Jobs Blobs")

  it "should match the key word fllowing @ with specials chars", ->
    $inputor = $("#special-chars").atwho at: "@", data: fixtures["names"]
    text = $.trim $inputor.text()

    query = callbacks.matcher.call(app, "@", text)
    expect(query).toBe(decodeURI("J%C3%A9r%C3%A9m%C3%BF"))

  it "can filter data", ->
    names = callbacks.beforeSave.call(app.controller(), fixtures["names"])
    names = callbacks.filter.call(app, "jo", names, "name")
    expect(names).toContain name: "Joshua"

  it "can filter numeric data", ->
    numerics = callbacks.beforeSave.call(app.controller(), fixtures["numerics"])
    numerics = callbacks.filter.call(app, "1", numerics, "name")
    expect(numerics).toContain name: 10

  it "request data from remote by ajax if set remoteFilter", ->
    remote_call = jasmine.createSpy("remote_call")
    $inputor.atwho
      at: "@"
      data: null,
      callbacks:
        remoteFilter: remote_call

    simulateTypingIn $inputor
    expect(remote_call).toHaveBeenCalled()

  it "can sort the data", ->
    names = callbacks.beforeSave.call(app.controller(), fixtures["names"])
    names = callbacks.sorter.call(app, "e", names, "name")
    expect(names[0].name).toBe 'Ethan'

  it "can sort numeric data", ->
    numerics = callbacks.beforeSave.call(app.controller(), fixtures["numerics"])
    numerics = callbacks.sorter.call(app, "1", numerics, "name")
    expect(numerics[0].name).toBe 13

  it "don't sort the data without a query", ->
    names = callbacks.beforeSave.call(app.controller(), fixtures["names"])
    names = callbacks.sorter.call(app, "", names, "name")
    expect(names[0]).toEqual({ name : 'Jacob' })

  it "can eval temple", ->
    map = {name: "username", nick: "nick_name"}
    tpl = '<li data-value="${name}">${nick}</li>'
    html = '<li data-value="username">nick_name</li>'

    result = callbacks.tplEval.call(app, tpl, map)
    expect(result).toBe(html)

  it "can evaluate template as a function", ->
    map = {name: "username", nick: "nick_name"}
    tpl = (map)-> '<li data-value="'+map.name+'">'+map.nick+'</li>'
    html = '<li data-value="username">nick_name</li>'
    result = callbacks.tplEval.call(app, tpl, map)
    expect(result).toBe(html)


  it "can highlight the query", ->
    html = '<li data-value="username">Ethan</li>'
    highlighted = callbacks.highlighter.call(app, html, "e")
    result = '<li data-value="username"> <strong>E</strong>than </li>'
    expect(highlighted).toBe(result)

  it "can insert the text which be choosed", ->
    spyOn(callbacks, "beforeInsert").and.callThrough()
    triggerAtwhoAt $inputor
    expect(callbacks.beforeInsert).toHaveBeenCalled()

  it "can adjust offset before reposition", ->
    spyOn(callbacks, "beforeReposition").and.callThrough()
    triggerAtwhoAt $inputor
    expect(callbacks.beforeReposition).toHaveBeenCalled()
