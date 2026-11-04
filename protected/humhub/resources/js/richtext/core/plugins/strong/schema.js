/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

const schema = {
    marks: {
        strong: {
            sortOrder: 200,
            parseDOM: [{tag: "b"}, {tag: "strong"},
                {
                    style: "font-weight", getAttrs: function (value) {
                    return /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null;
                }
                }],
            toDOM: () => {
                return ["strong"]
            },
            parseMarkdown: {mark: "strong"},
            toMarkdown: {open: "**", close: "**", mixable: true, expelEnclosingWhitespace: true}
        }
    }
};

export {schema}