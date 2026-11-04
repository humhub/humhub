/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const schema = {
    marks: {
        em: {
            sortOrder: 100,
            parseDOM: [{tag: "i"}, {tag: "em"},
                {
                    style: "font-style", getAttrs: function (value) {
                    return value === "italic" && null;
                }
                }],
            toDOM: () => {
                return ["em"]
            },
            parseMarkdown: {mark: "em"},
            toMarkdown: {open: "*", close: "*", mixable: true, expelEnclosingWhitespace: true}
        }
    }
};

export {schema};
