describe "content editable", ->
	$inputor = null
	app = null
	$ = jQuery

	beforeEach ->
		loadFixtures "inputors.html"
		$inputor = $("#editable").atwho
			at: "@",
			data: ["Jobs"]
			editableAtwhoQueryAttrs: {class: "hello", "data-editor-verified":true}
		app = getAppOf $inputor

	afterEach ->
        $inputor.atwho 'destroy'

	it "can insert content", ->
		triggerAtwhoAt $inputor
		expect($inputor.text()).toContain('@Jobs')

	it "insert by click", ->
		simulateTypingIn $inputor
		$inputor.blur()
		app.controller().view.$el.find('ul').children().first().trigger('click')
		expect($inputor.text()).toContain('@Jobs')

	it "unwrap span.atwho-query after match failed", ->
		simulateTypingIn $inputor
		expect $('.atwho-query').length
			.toBe 1
		$('.atwho-query').html "@J "
		simulateTypingIn $inputor, "@", 3
		expect $('.atwho-query').length
			.toBe 0

	it "wrap span.atwho-query with customize attrs", ->
		# for #235
		simulateTypingIn $inputor
		expect $('.atwho-query').data('editor-verified')
			.toBe true
