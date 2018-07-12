
describe "views", ->

  $inputor = null
  app = null
  $ = jQuery
  beforeEach ->
    loadFixtures "inputors.html"
    $inputor = $ "#inputor"
      .atwho at: "@", data: fixtures["names"]
    app = getAppOf $inputor
  afterEach ->
    $inputor.atwho 'destroy'

  describe "issues", ->
    controller = null
    callbacks = null

    beforeEach ->
      controller = app.controller()
      callbacks = $.fn.atwho.default.callbacks
      simulateTypingIn $inputor

    it "selected no highlight(.cur); github issues#234", ->
      simulateTypingIn $inputor
      expect targetLi = controller.view.$el.find('ul li:last')
        .not.toHaveClass 'cur'
      spyOn controller.view, "choose"
        .and.callThrough()
      targetLi.trigger clickEvent = $.Event("click.atwho-view")
      expect targetLi
        .toHaveClass 'cur'

    it "only hides on scroll if scrollTop is changed (github issue #305)", ->
      simulateTypingIn $inputor
      expect(controller.view.visible()).toBe true
      $inputor.trigger 'scroll'
      expect(controller.view.visible()).toBe true
