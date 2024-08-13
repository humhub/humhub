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
import {useSecondsLeft} from '../../../store'
import {roundSeconds} from '../../../lib/utils'
import styles from './timer.module.scss'

const Timer = ({className}) => {
  const secondsLeft = useSecondsLeft()

  return (
    <div className={c(styles.timer, className)}>
      <label htmlFor="timer">Time </label>
      <div className={c(styles.progressBarWrapper)}>
        <progress id="timer" max={roundSeconds} value={secondsLeft} />
        <div className={c(styles.time)}>
          {Math.floor(secondsLeft / 60)}:
          {(secondsLeft % 60).toString().padStart(2, '0')}
        </div>
      </div>
    </div>
  )
}

Timer.propTypes = {
  className: PropTypes.string
}

export default Timer
