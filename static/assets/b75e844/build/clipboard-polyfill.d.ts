import { DT } from "./DT";
declare global  {
    interface Navigator {
        clipboard: {
            writeText?: (s: string) => Promise<void>;
            readText?: () => Promise<string>;
        };
    }
}
export default class ClipboardPolyfill {
    static readonly DT: typeof DT;
    static setDebugLog(f: (s: string) => void): void;
    static suppressWarnings(): void;
    static write(data: DT): Promise<void>;
    static writeText(s: string): Promise<void>;
    static read(): Promise<DT>;
    static readText(): Promise<string>;
}
