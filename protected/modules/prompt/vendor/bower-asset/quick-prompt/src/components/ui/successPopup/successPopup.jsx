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

import {useEffect, useState} from 'react'
import PropTypes from 'prop-types'
import c from 'classnames'
import Emojisplosion from '../../generic/emojisplosion/emojisplosion'
import {useCurrentGuess, useDialogue} from '../../../store'

import styles from './successPopup.module.scss'

const SuccessPopup = ({className}) => {
  const guess = useCurrentGuess()
  const [position, setPosition] = useState([100, 100])
  const [doShow, setDoShow] = useState(false)
  const [doHide, setDoHide] = useState(false)

  const dialogue = useDialogue()

  useEffect(() => {
    const guesses = document.body.querySelectorAll('.guess')
    const lastGuess = guesses[guesses.length - 1]

    if (!lastGuess) return

    // long messages need more time to scroll
    const lastMessage = dialogue[dialogue.length - 1].text
    const timeout = lastMessage.length > 500 ? lastMessage.length : 500

    window.setTimeout(() => {
      const {x, y} = lastGuess.getBoundingClientRect()

      setPosition([x, y])

      setDoShow(true)
    }, timeout)

    window.setTimeout(() => {
      setDoHide(true)
    }, timeout + 1000)

    return () => {
      setDoShow(false)
      setDoHide(false)
    }
  }, [])

  return (
    <div
      className={c(
        styles.successPopup,
        className,
        doShow && styles.show,
        doHide && styles.hide
      )}
    >
      <mark
        style={{
          transform: `translate(${position[0]}px, ${position[1]}px)`
        }}
      >
        {guess}
        <div className={c(styles.plusOne)}>+1</div>
        <Emojisplosion />
      </mark>
    </div>
  )
}

SuccessPopup.propTypes = {
  className: PropTypes.string
}

export default SuccessPopup
