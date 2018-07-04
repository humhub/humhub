import {Promise as PromisePolyfill} from "es6-promise";
import {DT, suppressDTWarnings} from "./DT";

// Avoid using the Promise polyfill unless needed.
// https://github.com/lgarron/clipboard-polyfill/issues/59
var PromiseOrPolyfill = (typeof Promise === "undefined") ? PromisePolyfill : Promise;

// Debug log strings should be short, since they are copmiled into the production build.
// TODO: Compile debug logging code out of production builds?
var debugLog: (s: string) => void = function(s: string) {};
var showWarnings = true;
// Workaround for:
// - IE9 (can't bind console functions directly), and
// - Edge Issue #14495220 (referencing `console` without F12 Developer Tools can cause an exception)
var warnOrLog = function() {
  (console.warn || console.log).apply(console, arguments);
};
var warn = warnOrLog.bind("[clipboard-polyfill]");

var TEXT_PLAIN = "text/plain";

declare global {
  interface Navigator {
    clipboard: {
      writeText?: (s: string) => Promise<void>;
      readText?: () => Promise<string>;
    };
  }
}

export default class ClipboardPolyfill {
  public static readonly DT = DT;

  public static setDebugLog(f: (s: string) => void): void {
    debugLog = f;
  }

  public static suppressWarnings() {
    showWarnings = false;
    suppressDTWarnings();
  }

  public static write(data: DT): Promise<void> {
    if (showWarnings && !data.getData(TEXT_PLAIN)) {
      warn("clipboard.write() was called without a "+
        "`text/plain` data type. On some platforms, this may result in an "+
        "empty clipboard. Call clipboard.suppressWarnings() "+
        "to suppress this warning.");
    }

    return (new PromiseOrPolyfill((resolve, reject) => {
      // Internet Explorer
      if (seemToBeInIE()) {
        if (writeIE(data)) {
          resolve();
        } else {
          reject(new Error("Copying failed, possibly because the user rejected it."));
        }
        return;
      }

      if (execCopy(data)) {
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
      if (copyUsingTempSelection(document.body, data)) {
        debugLog("copyUsingTempSelection worked");
        resolve();
        return;
      }

      // Fallback 2 for desktop Safari. 
      if (copyUsingTempElem(data)) {
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
    })) as Promise<void>;
  }

  public static writeText(s: string): Promise<void> {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(s);
    }
    var dt = new DT();
    dt.setData(TEXT_PLAIN, s);
    return this.write(dt);
  }

  public static read(): Promise<DT> {
    return (new PromiseOrPolyfill((resolve, reject) => {
      // TODO: Attempt to use navigator.clipboard.read() directly.
      // Requires DT -> DataTransfer conversion.
      this.readText().then(
        (s: string) => resolve(DTFromText(s)),
        reject
      );
    })) as Promise<DT>;
  }

  public static readText(): Promise<string> {
    if (navigator.clipboard && navigator.clipboard.readText) {
      return navigator.clipboard.readText();
    }
    if (seemToBeInIE()) {
      return readIE();
    }
    return (new PromiseOrPolyfill((resolve, reject) => {
      reject("Read is not supported in your browser.");
    })) as Promise<string>;
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

function execCopy(data: DT): boolean {
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
  return tracker.success;
}

// Temporarily select a DOM element, so that `execCommand()` is not rejected.
function copyUsingTempSelection(e: HTMLElement, data: DT): boolean {
  selectionSet(e);
  var success = execCopy(data);
  selectionClear();
  return success;
}

// Create a temporary DOM element to select, so that `execCommand()` is not
// rejected.
function copyUsingTempElem(data: DT): boolean {
  var tempElem = document.createElement("div");
  // Setting an individual property does not support `!important`, so we set the
  // whole style instead of just the `-webkit-user-select` property.
  tempElem.setAttribute("style", "-webkit-user-select: text !important");
  // Place some text in the elem so that Safari has something to select.
  tempElem.textContent = "temporary element";
  document.body.appendChild(tempElem);

  var success = copyUsingTempSelection(tempElem, data);

  document.body.removeChild(tempElem);
  return success;
}

// Uses shadow DOM.
function copyTextUsingDOM(str: string): boolean {
  debugLog("copyTextUsingDOM");

  var tempElem = document.createElement("div");
  // Setting an individual property does not support `!important`, so we set the
  // whole style instead of just the `-webkit-user-select` property.
  tempElem.setAttribute("style", "-webkit-user-select: text !important");
  // Use shadow DOM if available.
  var spanParent: Node = tempElem;
  if (tempElem.attachShadow) {
    debugLog("Using shadow DOM.");
    spanParent = tempElem.attachShadow({mode: "open"});
  }

  var span = document.createElement("span");
  span.innerText = str;

  spanParent.appendChild(span);
  document.body.appendChild(tempElem);
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

/******** Convenience ********/

function DTFromText(s: string): DT {
  var dt = new DT();
  dt.setData(TEXT_PLAIN, s);
  return dt;
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
  var text = data.getData(TEXT_PLAIN);
  if (text !== undefined) {
    return (window as IEWindow).clipboardData.setData("Text", text);
  }

  throw ("No `text/plain` value was specified.");
}

// Returns "" if the read failed, e.g. because the user rejected the permission.
function readIE(): Promise<string> {
  return (new PromiseOrPolyfill((resolve, reject) => {
    var text = (window as IEWindow).clipboardData.getData("Text");
    if (text === "") {
      reject(new Error("Empty clipboard or could not read plain text from clipboard"));
    } else {
      resolve(text);
    }
  })) as Promise<string>;
}

/******** Expose `clipboard` on the global object in browser. ********/

// TODO: Figure out how to expose ClipboardPolyfill as self.clipboard using
// WebPack?
declare var module: any;
module.exports = ClipboardPolyfill;
