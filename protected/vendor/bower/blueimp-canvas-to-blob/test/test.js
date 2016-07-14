/*
 * JavaScript Canvas to Blob Test
 * https://github.com/blueimp/JavaScript-Canvas-to-Blob
 *
 * Copyright 2012, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*global window, describe, it, Blob */

;(function (expect) {
  'use strict'

  // 80x60px GIF image (color black, base64 data):
  var b64Data = 'R0lGODdhUAA8AIABAAAAAP///ywAAAAAUAA8AAACS4SPqcvtD6' +
      'OctNqLs968+w+G4kiW5omm6sq27gvH8kzX9o3n+s73/g8MCofE' +
      'ovGITCqXzKbzCY1Kp9Sq9YrNarfcrvcLDovH5PKsAAA7'
  var imageUrl = 'data:image/gif;base64,' + b64Data
  var blob = window.dataURLtoBlob && window.dataURLtoBlob(imageUrl)

  describe('canvas.toBlob', function () {
    it('Converts a canvas element to a blob and passes it to the callback function', function (done) {
      window.loadImage(blob, function (canvas) {
        canvas.toBlob(
          function (newBlob) {
            expect(newBlob).to.be.a(Blob)
            done()
          }
        )
      }, {canvas: true})
    })

    it('Converts a canvas element to a PNG blob', function (done) {
      window.loadImage(blob, function (canvas) {
        canvas.toBlob(
          function (newBlob) {
            expect(newBlob.type).to.be('image/png')
            done()
          },
          'image/png'
        )
      }, {canvas: true})
    })

    it('Converts a canvas element to a JPG blob', function (done) {
      window.loadImage(blob, function (canvas) {
        canvas.toBlob(
          function (newBlob) {
            expect(newBlob.type).to.be('image/jpeg')
            done()
          },
          'image/jpeg'
        )
      }, {canvas: true})
    })

    it('Keeps the aspect ratio of the canvas image', function (done) {
      window.loadImage(blob, function (canvas) {
        canvas.toBlob(
          function (newBlob) {
            window.loadImage(newBlob, function (img) {
              expect(img.width).to.be(canvas.width)
              expect(img.height).to.be(canvas.height)
              done()
            })
          }
        )
      }, {canvas: true})
    })

    it('Keeps the image data of the canvas image', function (done) {
      window.loadImage(blob, function (canvas) {
        canvas.toBlob(
          function (newBlob) {
            window.loadImage(newBlob, function (newCanvas) {
              var canvasData = canvas.getContext('2d')
                  .getImageData(0, 0, canvas.width, canvas.height)
              var newCanvasData = newCanvas.getContext('2d')
                  .getImageData(0, 0, newCanvas.width, newCanvas.height)
              expect(canvasData.width).to.be(newCanvasData.width)
              expect(canvasData.height).to.be(newCanvasData.height)
              done()
            }, {canvas: true})
          }
        )
      }, {canvas: true})
    })
  })
}(this.expect))
