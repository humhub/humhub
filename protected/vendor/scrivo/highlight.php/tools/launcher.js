dojoConfig = {
  async: true,
  baseUrl: "lib_dojo/",
  packages: [{
    name: "dojo",
    location: "dojo"
  },{
    name: "dojox",
    location: "dojox"
  }],
  deps: ["export"],
  highlightJsDir: "/home/www/highlight.php/tools/lib_highlight/"
};

require("./lib_dojo/dojo/dojo.js");


