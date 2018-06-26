export declare function suppressDTWarnings(): void;
export declare class DT {
    private m;
    setData(type: string, value: string): void;
    getData(type: string): string | undefined;
    forEach(f: (value: string, key: string) => void): void;
}
