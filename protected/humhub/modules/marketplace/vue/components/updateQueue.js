/**
 * "Update all": `update(id)` for every id, strictly one after another — each update replaces
 * files and runs migrations, two at once would compete. A failure is recorded and the queue
 * goes on; `stop()` lets the running update finish and skips the rest.
 *
 * @since 1.20
 */
export function createUpdateQueue(ids, update) {
    let stopped = false;

    return {
        async run() {
            const failed = [];
            for (const id of ids) {
                if (stopped) {
                    break;
                }
                try {
                    await update(id);
                } catch (error) {
                    failed.push(id);
                }
            }
            return { stopped, failed };
        },
        stop() {
            stopped = true;
        },
    };
}
