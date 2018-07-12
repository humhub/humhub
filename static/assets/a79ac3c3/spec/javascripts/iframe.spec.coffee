describe "iframe editor", ->
    $inputor = null
    app = null
    $ = jQuery
    beforeEach ->
        loadFixtures "inputors.html"
        ifr = $('#iframeInput')[0]
        doc = ifr.contentDocument || iframe.contentWindow.document
        if (ifrBody = doc.body) is null # IE
          doc.write "<body></body>"
          ifrBody = doc.body
        ifrBody.contentEditable = true
        ifrBody.id = 'ifrBody'
        ifrBody.innerHTML = 'Stay Foolish, Stay Hungry. @Jobs'
        $inputor = $(ifrBody)
        $inputor.atwho('setIframe', ifr)
        $inputor.atwho(at: "@", data: ['Jobs'])
        app = getAppOf $inputor

    afterEach ->
      $inputor.atwho 'destroy'

    it "can insert content", ->
      triggerAtwhoAt $inputor
      expect($inputor.text()).toContain('@Jobs')

    it "insert by click", ->
      simulateTypingIn $inputor
      app.controller().view.$el.find('ul').children().first().trigger('click')
      expect($inputor.text()).toContain('@Jobs')
