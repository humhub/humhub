describe("jquery.caret", function() {
  var $inputor;
  $inputor = null;
  var fixPos = 0;

  beforeEach(function() {
    var html = ''
      + '<textarea id="inputor" name="at" rows="8" cols="40">'
      + '  Stay Foolish, Stay Hungry. @Jobs'
      + '</textarea>'

    var fixture = setFixtures(html);
    $inputor = fixture.find('#inputor');

    var fixPos = 20;
  });

  it('Set/Get caret pos', function() {
    $inputor.caret('pos', 15);
    expect($inputor.caret('pos')).toBe(15);
  });

  // TODO: I don't know how to test this functions yet. = =.
  // it("Set/Get caret position", function() {
  //   $inputor.caret('position', 20);
  //   pos = $inputor.caret('position'); // => {left: 15, top: 30, height: 20}
  //   expect(pos).toBe({ left : 2, top : 2, height : 17 });
  // });

  // $('#inputor').caret('offset'); // => {left: 300, top: 400, height: 20}
  // $('#inputor').caret('offset', fixPos);

});