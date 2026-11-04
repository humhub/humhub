/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

const schema = {
    marks: {
        code:{
            isCode: true,
            sortOrder: 400,
            preventMarks: ['link'],
            parseDOM: [{tag: "code"}],
            toDOM: () => {
                return ["code"]
            },
            parseMarkdown:  {code_inline: {mark: "code"}},
            toMarkdown: {open: "`", close: "`"}
        }
    }
};

export {schema}