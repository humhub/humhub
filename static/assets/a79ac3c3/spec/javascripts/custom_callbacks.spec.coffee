describe "custom callbacks", ->
  $inputor = null
  $ = jQuery

  beforeEach ->
    loadFixtures("inputors.html")
  afterEach ->
    $inputor.atwho 'destroy'

  describe "remoteFilter()", ->
    it "only renders the view for data from the latest lookup", ->
      callbackList = []

      remoteFilter = jasmine.createSpy("remoteFilter").and.callFake (_, cb) ->
        callbackList.push cb

      $inputor = $("#inputor").atwho({
        at: "@",
        data: [],
        callbacks: {
          remoteFilter
        }
      })
      $inputor.val('@foo')

      app = getAppOf $inputor
      controller = app.controller()
      spyOn(controller, 'renderView')

      simulateTypingIn $inputor
      expect(remoteFilter).toHaveBeenCalled()
      simulateTypingIn $inputor
      expect(callbackList.length).toBeGreaterThan(1)
      while callbackList.length > 1
        callbackList.shift()(['no renders'])
        expect(controller.renderView).not.toHaveBeenCalled()

      callbackList.shift()(['render'])
      expect(controller.renderView).toHaveBeenCalled()

    it "does not attempt to render the view after query has been cleared", ->
      remoteFilterCb = null

      remoteFilter = jasmine.createSpy("remoteFilter").and.callFake (_, cb) ->
        remoteFilterCb = cb

      $inputor = $("#inputor").atwho({
        at: "@",
        data: [],
        callbacks: {
          remoteFilter
        }
      })

      app = getAppOf $inputor
      controller = app.controller()
      spyOn controller, 'renderView'

      simulateTypingIn $inputor
      expect(remoteFilter).toHaveBeenCalled()
      $inputor.val ''
      simulateTypingIn $inputor
      expect(remoteFilter.calls.count()).toEqual(1)
      remoteFilterCb ['should not render']
      expect(controller.renderView).not.toHaveBeenCalled()

    it "does not attempt to render the view after focus has been lost", ->
      remoteFilterCb = null

      remoteFilter = jasmine.createSpy("remoteFilter").and.callFake (_, cb) ->
        remoteFilterCb = cb

      $inputor = $("#inputor").atwho({
        at: "@",
        data: [],
        callbacks: {
          remoteFilter
        }
      })

      app = getAppOf $inputor
      controller = app.controller()
      spyOn controller, 'renderView'

      simulateTypingIn $inputor
      expect(remoteFilter).toHaveBeenCalled()
      $inputor.blur();
      remoteFilterCb ['should not render']
      expect(controller.renderView).not.toHaveBeenCalled()
