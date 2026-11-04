/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {menu} from "./menu"
import {schema} from "./schema"
import {initFileHandler} from "./service"

const file_handler = {
    id: 'file_handler',
    schema,
    menu: (context) => menu(context),
    init: (context) => initFileHandler(context)
}

export default file_handler
