// https://developer.mozilla.org/en-US/docs/Web/API/DataTransfer/setData
interface DataTransfer {
  setData: (key: string, value: any) => void;
  getData: (key: string) => any;
}

interface IEWindowClipbardData {
  setData: (key: string, value: string) => void;
  getData: (key: string) => string;
}

interface IEWindow extends Window {
  clipboardData: IEWindowClipbardData
}

export class Test {
  results: { [key:string]: any } = {};
  run() {
    this.results["queryCommandSupported"] = document.queryCommandSupported("copy");

    this.results["pre_start.enabled"] = document.queryCommandEnabled("copy");
    try {
      this.setup();
    } catch (e) {
      console.error(e);
    }
    this.results["post_setup.enabled"] = document.queryCommandEnabled("copy");

    var listener = this.copyListener.bind(this);
    document.addEventListener("copy", listener);
    try {
      var success = document.execCommand("copy");
      this.results["exec.success"] = success;
    } finally {
      document.removeEventListener("copy", listener);
    }

    this.results["pre_teardown.enabled"] = document.queryCommandEnabled("copy");
    try {
      this.teardown();
    } catch (e) {
      console.error(e);
    }
    this.results["post_teardown.enabled"] = document.queryCommandEnabled("copy");

    console.log(JSON.stringify(this.results, null, "  "));
  }
  setup() {}
  copyListener(e: ClipboardEvent) {}
  teardown() {}
  select(e: Element) {
    var sel = document.getSelection();
    sel.removeAllRanges();
    var range = document.createRange();
    range.selectNodeContents(e);
    sel.addRange(range);
  }
  clearSelection() {
    var sel = document.getSelection();
    sel.removeAllRanges();
  }
}

export namespace Test {

export class Plain extends Test {
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "Plain");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "Plain");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
}

export class PlainHTML extends Test {
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "PlainHTML");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/html", "PlainHTML <b>markup</b>");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
}

export class PlainBoth extends Test {
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "PlainBoth");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "PlainBoth no markup");
    e.clipboardData.setData("text/html", "PlainBoth <b>markup</b>");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
}

export class PlainBothHTMLFirst extends Test {
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "PlainBothHTMLFirst");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/html", "PlainBothHTMLFirst <b>markup</b>");
    e.clipboardData.setData("text/plain", "PlainBothHTMLFirst no markup");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
}

export class SelectBody extends Test {
  setup() {
    this.select(document.body);
  }
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "SelectBody");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "SelectBody");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
  teardown() {
    // TODO: Restore selection?
    this.clearSelection();
  }
}

export class SelectTempElem extends Test {
  private tempElem: Element;
  setup() {
    this.tempElem = document.createElement("pre");
    this.tempElem.textContent = "SelectTempElem";
    document.body.appendChild(this.tempElem);
    this.select(this.tempElem);
  }
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "SelectTempElem");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "SelectTempElem");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
  teardown() {
    this.clearSelection();
    document.body.removeChild(this.tempElem);
  }
}

export class SelectTempElemUserSelectNone extends Test {
  private tempElem: HTMLElement;
  setup() {
    this.tempElem = document.createElement("pre");
    this.tempElem.style["user-select"] = "none";
    this.tempElem.style["-webkit-user-select"] = "none";
    this.tempElem.style["-moz-user-select"] = "none";
    this.tempElem.style["-ms-user-select"] = "none";
    this.tempElem.textContent = "SelectTempElemUserSelectNone";
    document.body.appendChild(this.tempElem);
    this.select(this.tempElem);
  }
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "SelectTempElemUserSelectNone");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "SelectTempElemUserSelectNone");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
  teardown() {
    this.clearSelection();
    document.body.removeChild(this.tempElem);
  }
}

export class SelectTempElemUserSelectNoneNested extends Test {
  private tempElem: HTMLElement;
  private tempElem2: HTMLElement;
  setup() {

    this.tempElem2 = document.createElement("pre");
    this.tempElem2.style["user-select"] = "text";
    this.tempElem2.style["-webkit-user-select"] = "text";
    this.tempElem2.style["-moz-user-select"] = "text";
    this.tempElem2.style["-ms-user-select"] = "text";
    this.tempElem2.textContent = "SelectTempElemUserSelectNoneNested";

    this.tempElem = document.createElement("pre");
    this.tempElem.style["user-select"] = "none";
    this.tempElem.style["-webkit-user-select"] = "none";
    this.tempElem.style["-moz-user-select"] = "none";
    this.tempElem.style["-ms-user-select"] = "none";
    this.tempElem.appendChild(this.tempElem2);
    document.body.appendChild(this.tempElem);

    this.select(this.tempElem2);
  }
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "SelectTempElemUserSelectNoneNested");
    this.results["pre_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.clipboardData.setData("text/plain", "SelectTempElemUserSelectNoneNested");
    this.results["post_setData.text_plain"] = e.clipboardData.getData("text/plain");
    e.preventDefault();
  }
  teardown() {
    this.clearSelection();
    document.body.removeChild(this.tempElem);
  }
}

export class CopyTempElem extends Test {
  private tempElem: Element;
  setup() {
    this.tempElem = document.createElement("div");
    var shadowRoot = this.tempElem.attachShadow({mode: "open"});
    document.body.appendChild(this.tempElem);

    var span = document.createElement("span");
    span.textContent = "CopyTempElem\n" + new Date();
    span.style.whiteSpace = "pre-wrap";
    shadowRoot.appendChild(span);
    this.select(span);
  }
  copyListener(e: ClipboardEvent) {
    console.log("copyListener", "CopyTempElem");
    this.results["no_setData.text_plain"] = e.clipboardData.getData("text/plain");
  }
  teardown() {
    this.clearSelection();
    document.body.removeChild(this.tempElem);
  }
}

export class WindowClipboardData extends Test {
  private tempElem: Element;
 run() {
    this.results["start.enabled"] = document.queryCommandEnabled("copy");
    (<IEWindow>(window)).clipboardData.setData("Text", "WindowClipboardData");
    this.results["end.enabled"] = document.queryCommandEnabled("copy");

  }
}

export class DataTransferConstructor extends Test {
  setup() {
    try {
      var dt = new DataTransfer();
      dt.setData("text/plain", "plain text");
      dt.setData("text/html", "<b>markup</b> text");
      this.results["getData.text_plain"] = dt.getData("text/plain");
      this.results["getData.text_html"] = dt.getData("text/html");
    } catch (e) {
      console.error(e);
    }
  }
}

}

// TODO: Try `event.clipboardData.items.add()` in listener.

// TODO: MutationObserver test.
