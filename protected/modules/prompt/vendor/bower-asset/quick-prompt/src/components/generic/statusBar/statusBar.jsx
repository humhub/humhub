/**
 * Copyright 2023 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import PropTypes from 'prop-types'
import c from 'classnames'
import Timer from '../../ui/timer/timer'
import Score from '../../ui/score/score'
import styles from './statusBar.module.scss'

const StatusBar = ({className}) => {
  return (
    <div className={c(styles.statusBar, className)}>
      <Timer className={styles.timer} />
      <Score className={styles.score} />
    </div>
  )
}

StatusBar.propTypes = {
  className: PropTypes.string
}

export default StatusBar
