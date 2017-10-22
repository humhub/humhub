import {Promise} from "es6-promise";
import DT from "./DT";

// Debug log strings shorts, since they are copmiled into the production build.
// TODO: Compile debug logging code out of production builds?
var debugLog: (s: string) => void = function(s: string) {};
var missingPlainTextWarning = true;

var TEXT_PLAIN = "text/plain";

var warn = (console.warn || console.log).bind(console, "[clipboard-polyfill]");

export default class ClipboardPolyfill {
  public static readonly DT = DT;

  public static setDebugLog(f: (s: string) => void): void {
    debugLog = f;
  }

  public static suppressMissingPlainTextWarning() {
    missingPlainTextWarning = false;
  }

  public static write(data: DT): Promise<void> {
    if (missingPlainTextWarning && !data.getData(TEXT_PLAIN)) {
      warn("clipboard.write() was called without a "+
        "`text/plain` data type. On some platforms, this may result in an "+
        "empty clipboard. Call clipboard.suppressMissingPlainTextWarning() "+
        "to suppress this warning.");
    }

    return new Promise<void>((resolve, reject) => {
      // Internet Explorer
      if (seemToBeInIE()) {
        if (writeIE(data)) {
          resolve()
        } else {
          reject(new Error("Copying failed, possibly because the user rejected it."));
        }
        return;
      }

      var tracker = execCopy(data);
      if (tracker.success) {
        debugLog("regular execCopy worked");
        resolve();
        return;
      }

      // Success detection on Edge is not possible, due to bugs in all 4
      // detection mechanisms we could try to use. Assume success.
      if (navigator.userAgent.indexOf("Edge") > -1) {
        debugLog("UA \"Edge\" => assuming success");
        resolve();
        return;
      }

      // Fallback 1 for desktop Safari.
      tracker = copyUsingTempSelection(document.body, data);
      if (tracker.success) {
        debugLog("copyUsingTempSelection worked");
        resolve();
        return;
      }

      // Fallback 2 for desktop Safari. 
      tracker = copyUsingTempElem(data);
      if (tracker.success) {
        debugLog("copyUsingTempElem worked");
        resolve();
        return;
      }

      // Fallback for iOS Safari.
      var text = data.getData(TEXT_PLAIN);
      if (text !== undefined && copyTextUsingDOM(text)) {
        debugLog("copyTextUsingDOM worked");
        resolve();
        return;
      }

      reject(new Error("Copy command failed."));
    });
  }

  public static writeText(s: string): Promise<void> {
    var dt = new DT();
    dt.setData(TEXT_PLAIN, s);
    return this.write(dt);
  }

  public static read(): Promise<DT> {
    return new Promise((resolve, reject) => {
      if (seemToBeInIE()) {
        readIE().then(
          (s: string) => resolve(DT.fromText(s)),
          reject
        );
        return;
      }
      // TODO: Attempt to read using async clipboard API.
      reject("Read is not supported in your browser.")
    });
  }

  public static readText(): Promise<string> {
    if (seemToBeInIE()) {
      return readIE();
    }
    return new Promise((resolve, reject) => {
      // TODO: Attempt to read using async clipboard API.
      reject("Read is not supported in your browser.")
    });
  }

  // Legacy v1 API.
  public static copy(obj: string|{[key:string]:string}|HTMLElement): Promise<void> {
    warn("The clipboard.copy() API is deprecated and may be removed in a future version. Please switch to clipboard.write() or clipboard.writeText().");

    return new Promise((resolve, reject) => {
      var data: DT;
      if (typeof obj === "string") {
        data = DT.fromText(obj);
      } else if (obj instanceof HTMLElement) {
        data = DT.fromElement(obj);
      } else if (obj instanceof Object) {
        data = DT.fromObject(obj);
      } else {
        reject("Invalid data type. Must be string, DOM node, or an object mapping MIME types to strings.");
        return;
      }
      this.write(data);
    });
  }

  // Legacy v1 API.
  public static paste(): Promise<string> {
    warn("The clipboard.paste() API is deprecated and may be removed in a future version. Please switch to clipboard.read() or clipboard.readText().");
    return this.readText();
  }
}

/******** Implementations ********/

class FallbackTracker {
  public success: boolean = false;
}

function copyListener(tracker: FallbackTracker, data: DT, e: ClipboardEvent): void {
  debugLog("listener called");
  tracker.success = true;
  data.forEach((value: string, key: string) => {
    e.clipboardData.setData(key, value);
    if (key === TEXT_PLAIN && e.clipboardData.getData(key) != value) {
      debugLog("setting text/plain failed");
      tracker.success = false;
    }
  });
  e.preventDefault();
}

function execCopy(data: DT): FallbackTracker {
  var tracker = new FallbackTracker();
  var listener = copyListener.bind(this, tracker, data);

  document.addEventListener("copy", listener);
  try {
    // We ignore the return value, since FallbackTracker tells us whether the
    // listener was called. It seems that checking the return value here gives
    // us no extra information in any browser.
    document.execCommand("copy");
  } finally {
    document.removeEventListener("copy", listener);
  }
  return tracker;
}

// Create a temporary DOM element to select, so that `execCommand()` is not
// rejected.
function copyUsingTempSelection(e: HTMLElement, data: DT): FallbackTracker {
  selectionSet(e);
  var tracker = execCopy(data);
  selectionClear();
  return tracker;
}

// Create a temporary DOM element to select, so that `execCommand()` is not
// rejected.
function copyUsingTempElem(data: DT): FallbackTracker {
  var tempElem = document.createElement("div");
  // Place some text in the elem so that Safari has something to select.
  tempElem.textContent = "temporary element";
  document.body.appendChild(tempElem);

  var tracker = copyUsingTempSelection(tempElem, data);

  document.body.removeChild(tempElem);
  return tracker;
}

// Uses shadow DOM.
function copyTextUsingDOM(str: string): boolean {
  debugLog("copyTextUsingDOM");

  var tempElem = document.createElement("div");
  var shadowRoot = tempElem.attachShadow({mode: "open"});
  document.body.appendChild(tempElem);

  var span = document.createElement("span");
  span.innerText = str;
  // span.style.whiteSpace = "pre-wrap"; // TODO: Use `innerText` above instead?
  shadowRoot.appendChild(span);
  selectionSet(span);

  var result = document.execCommand("copy");

  selectionClear();
  document.body.removeChild(tempElem);

  return result;
}

/******** Selection ********/

function selectionSet(elem: Element): void {
  var sel = document.getSelection();
  var range = document.createRange();
  range.selectNodeContents(elem);
  sel.removeAllRanges();
  sel.addRange(range);
}

function selectionClear(): void {
  var sel = document.getSelection();
  sel.removeAllRanges();
}

/******** Internet Explorer ********/

interface IEWindow extends Window {
  clipboardData: {
    setData: (key: string, value: string) => boolean;
    // Always results in a string: https://msdn.microsoft.com/en-us/library/ms536436(v=vs.85).aspx
    getData: (key: string) => string;
  }
}

function seemToBeInIE(): boolean {
  return typeof ClipboardEvent === "undefined" &&
         typeof (window as IEWindow).clipboardData !== "undefined" &&
         typeof (window as IEWindow).clipboardData.setData !== "undefined";
}

function writeIE(data: DT): boolean {
  // IE supports text or URL, but not HTML: https://msdn.microsoft.com/en-us/library/ms536744(v=vs.85).aspx
  // TODO: Write URLs to `text/uri-list`? https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Recommended_drag_types
  var text = data.getData("text/plain");
  if (text !== undefined) {
    return (window as IEWindow).clipboardData.setData("Text", text);
  }

  throw ("No `text/plain` value was specified.");
}

// Returns "" if the read failed, e.g. because rejected the permission.
function readIE(): Promise<string> {
  return new Promise((resolve, reject) => {
    var text = (window as IEWindow).clipboardData.getData("Text");
    if (text === "") {
      reject(new Error("Empty clipboard or could not read plain text from clipboard"));
    } else {
      resolve(text);
    }
  })
}

/******** Expose `clipboard` on the global object in browser. ********/

// TODO: Figure out how to expose ClipboardPolyfill as self.clipboard using
// WebPack?
declare var module: any;
module.exports = ClipboardPolyfill;
