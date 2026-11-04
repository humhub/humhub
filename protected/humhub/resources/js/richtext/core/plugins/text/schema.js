/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const schema = {
    nodes: {
        text: {
            sortOrder: 900,
            group: "inline",
            toDOM: (node) => {
                return node.text
            },
            toMarkdown: (state, node) => {
                let isCodeMark = false;
                node.marks.forEach(function(mark) {
                    if(mark.type.spec.isCode) {
                        isCodeMark = true;
                    }
                });

                let text = node.text;

                if(isCodeMark) {
                    text = text.replace('`', '');
                }

                state.text(text, !isCodeMark);
            }
        }
    }
};

export {schema};
