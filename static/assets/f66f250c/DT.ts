const DataType: {[key:string]:string} = {
  TEXT_PLAIN: "text/plain",
  TEXT_HTML: "text/html"
};

const DataTypeLookup: Set<string> = new Set<string>();
for (var key in DataType) {
  DataTypeLookup.add(DataType[key]);
}

// TODO: Dedup with main file?
var warn = (console.warn || console.log).bind(console, "[clipboard-polyfill]");
var showWarnings = true;
export function suppressDTWarnings() {
  showWarnings = false;
}

export class DT {
  private m: Map<string, string> = new Map<string, string>();

  public setData(type: string, value: string): void {
    if (showWarnings && !(DataTypeLookup.has(type))) {
      warn("Unknown data type: " + type, "Call clipboard.suppressWarnings() "+
        "to suppress this warning.");
    }

    this.m.set(type, value);
  }

  public getData(type: string): string | undefined {
    return this.m.get(type);
  }

  // TODO: Provide an iterator consistent with DataTransfer.
  public forEach(f: (value: string, key: string) => void): void {
    return this.m.forEach(f);
  }
}
