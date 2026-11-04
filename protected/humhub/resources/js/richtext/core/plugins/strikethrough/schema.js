/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

const schema = {
    marks: {
        strikethrough: {
            parseDOM: [{tag: "s"}],
            toDOM: () => {
                return ["s"]
            },
            parseMarkdown: {s: {mark: "strikethrough"}},
            toMarkdown: {open: "~~", close: "~~", mixable: true, expelEnclosingWhitespace: true}
        }
    }
};

export {schema}