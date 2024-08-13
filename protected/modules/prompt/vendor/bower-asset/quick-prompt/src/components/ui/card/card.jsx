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
import {useDidWin, useBadInput, useDidSkip, useGamePrompt} from '../../../store'
import styles from './card.module.scss'

const Card = ({disallowed = []}) => {
  const [show, setShow] = useState(false)
  const gamePrompt = useGamePrompt()
  const didWin = useDidWin()
  const didSkip = useDidSkip()
  const badInput = useBadInput()

  useEffect(() => {
    setShow(false)

    if (didWin || badInput || didSkip) return

    if (gamePrompt.word && !didWin) {
      setShow(true)
    }
  }, [gamePrompt, didWin, badInput, didSkip])

  return (
    <div className={c(styles.card, show && styles.show)}>
      <div className={c(styles.container)}>
        <span>Describe</span>
        <div className={c(styles.word)}>
          <span className="emoji">{gamePrompt.emoji}&nbsp;</span>
          <span>{gamePrompt.word}</span>
        </div>
      </div>
      <div className={c(styles.container)}>
        <span>Without using</span>
        <div className={c(styles.disallowedWords)}>
          {disallowed &&
            disallowed.map((word, i) => (
              <span key={word}>
                {word}
                {i < disallowed.length - 1 && ','}&nbsp;
              </span>
            ))}
        </div>
      </div>
    </div>
  )
}

Card.propTypes = {
  disallowed: PropTypes.array
}

export default Card
