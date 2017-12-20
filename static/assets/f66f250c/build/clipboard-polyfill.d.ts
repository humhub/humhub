import { Promise } from "es6-promise";
import { DT } from "./DT";
export default class ClipboardPolyfill {
    static readonly DT: typeof DT;
    static setDebugLog(f: (s: string) => void): void;
    static suppressWarnings(): void;
    static write(data: DT): Promise<void>;
    static writeText(s: string): Promise<void>;
    static read(): Promise<DT>;
    static readText(): Promise<string>;
}
