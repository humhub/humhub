/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

export class MaxHeightState {
    constructor(state, options) {
        this.state = state;
        this.context = options.context;
        this.oldStageHeight = 0;
        this.scrollActive = false;
        this.initialized = false;
    }

    update() {
        let stageHeight = this.context.editor.getStage()[0].offsetHeight;

        if(stageHeight === this.oldStageHeight) {
            return;
        }

        this.oldStageHeight = stageHeight;

        // The stage scrolls natively (`overflow: auto`, set by the plugin)
        if(!this.scrollActive && this.context.editor.getStage()[0].scrollHeight > stageHeight) {
            this.scrollActive = true;
            this.context.editor.trigger('scrollActive');
        } else if(!this.initialized || this.scrollActive) {
            this.scrollActive = false;
            this.context.editor.trigger('scrollInactive');
        }

        this.initialized = true;
    }
}