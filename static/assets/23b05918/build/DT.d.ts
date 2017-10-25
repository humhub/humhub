export default class DT {
    private m;
    setData(type: string, value: string): void;
    getData(type: string): string | undefined;
    forEach(f: (value: string, key: string) => void): void;
    static fromText(s: string): DT;
    static fromObject(obj: {
        [key: string]: string;
    }): DT;
    static fromElement(e: HTMLElement): DT;
}
